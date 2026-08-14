<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\QueueTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('queue.view');

        $query = QueueTicket::with(['patient', 'doctor', 'appointment'])
            ->whereDate('queue_date', now()->toDateString());

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $tickets = $query->get()
            ->groupBy('status');

        $doctors = Doctor::where('is_available', true)->orderBy('name')->get();

        return view('queue.index', compact('tickets', 'doctors'));
    }

    public function checkinForm(Request $request)
    {
        Gate::authorize('queue.checkin');

        $today = now()->toDateString();

        $appointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('date', $today)
            ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])
            ->whereNotIn('id', function ($query) use ($today) {
                $query->select('appointment_id')
                    ->from('queue_tickets')
                    ->whereDate('queue_date', $today)
                    ->whereNotNull('appointment_id');
            })
            ->orderBy('time')
            ->get();

        $doctors = Doctor::where('is_available', true)->orderBy('name')->get();

        return view('queue.checkin', compact('appointments', 'doctors'));
    }

    public function checkin(Request $request)
    {
        Gate::authorize('queue.checkin');

        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        $appointment = Appointment::findOrFail($validated['appointment_id']);

        if (!in_array($appointment->status->value, ['scheduled', 'confirmed'])) {
            return back()->with('error', 'This appointment cannot be checked in.');
        }

        $existingTicket = QueueTicket::where('appointment_id', $appointment->id)
            ->whereDate('queue_date', now()->toDateString())
            ->first();

        if ($existingTicket) {
            return back()->with('error', 'This appointment has already been checked in.');
        }

        $ticket = DB::transaction(function () use ($appointment) {
            $ticketNumber = QueueTicket::generateTicketNumber(now()->toDateString());

            $ticket = QueueTicket::create([
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'queue_date' => now()->toDateString(),
                'ticket_number' => $ticketNumber,
                'status' => 'waiting',
                'checked_in_at' => now(),
            ]);

            $fromStatus = $appointment->status;
            $appointment->update(['status' => AppointmentStatus::CheckedIn]);

            $appointment->statusHistories()->create([
                'from_status' => $fromStatus?->value,
                'to_status' => AppointmentStatus::CheckedIn->value,
                'note' => 'Patient checked in (queue ticket ' . $ticketNumber . ').',
                'changed_by' => auth()->id(),
            ]);

            return $ticket;
        });

        return redirect()->route('queue.index')
            ->with('success', "Patient checked in. Ticket number: {$ticket->ticket_number}");
    }

    public function walkinForm()
    {
        Gate::authorize('queue.checkin');

        $patients = Patient::where('status', 'active')->orderBy('name')->get();
        $doctors = Doctor::where('is_available', true)->with('department')->orderBy('name')->get();

        return view('queue.walkin', compact('patients', 'doctors'));
    }

    public function walkin(Request $request)
    {
        Gate::authorize('queue.checkin');

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id'  => 'required|exists:doctors,id',
            'notes'      => 'nullable|string|max:500',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        $doctor = Doctor::findOrFail($validated['doctor_id']);

        if (!$patient || $patient->status !== 'active') {
            return back()->withErrors(['patient_id' => 'Patient is not active.'])->withInput();
        }

        if (!$doctor->is_available) {
            return back()->withErrors(['doctor_id' => 'Doctor is currently unavailable.'])->withInput();
        }

        $ticket = QueueTicket::create([
            'patient_id'   => $patient->id,
            'doctor_id'    => $doctor->id,
            'appointment_id' => null,
            'queue_date'   => now()->toDateString(),
            'ticket_number' => QueueTicket::generateTicketNumber(now()->toDateString()),
            'status'       => 'waiting',
            'checked_in_at' => now(),
            'notes'        => $validated['notes'] ?? null,
        ]);

        return redirect()->route('queue.index')
            ->with('success', "Walk-in patient added. Ticket number: {$ticket->ticket_number}");
    }

    public function callNext(Request $request)
    {
        Gate::authorize('queue.call');

        $doctorId = $request->input('doctor_id');

        $nextTicket = QueueTicket::whereDate('queue_date', now()->toDateString())
            ->where('status', 'waiting')
            ->when($doctorId, fn($q) => $q->where('doctor_id', $doctorId))
            ->orderBy('checked_in_at')
            ->orderBy('ticket_number')
            ->lockForUpdate()
            ->first();

        if (!$nextTicket) {
            return back()->with('error', 'No patients waiting in the queue.');
        }

        $nextTicket->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        return back()->with('success', "Called ticket: {$nextTicket->ticket_number}");
    }

    public function callTicket(QueueTicket $ticket)
    {
        Gate::authorize('queue.call');

        if (!$ticket->canBeCalled()) {
            return back()->with('error', 'This ticket cannot be called.');
        }

        $ticket->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        return back()->with('success', "Called ticket: {$ticket->ticket_number}");
    }

    public function startConsultation(QueueTicket $ticket)
    {
        Gate::authorize('queue.consult');

        if (!$ticket->canStartConsultation()) {
            return back()->with('error', 'This ticket cannot start consultation.');
        }

        $ticket->update([
            'status' => 'in_consultation',
            'consultation_started_at' => now(),
        ]);

        return back()->with('success', "Consultation started for ticket: {$ticket->ticket_number}");
    }

    public function cancelTicket(QueueTicket $ticket)
    {
        Gate::authorize('queue.cancel');

        if (!$ticket->canBeCancelled()) {
            return back()->with('error', 'This ticket cannot be cancelled.');
        }

        $ticket->update(['status' => 'cancelled']);

        if ($ticket->appointment_id) {
            $appointment = Appointment::find($ticket->appointment_id);
            if ($appointment && $appointment->isCheckedIn()) {
                $fromStatus = $appointment->status;
                $appointment->update(['status' => AppointmentStatus::Cancelled]);
                $appointment->statusHistories()->create([
                    'from_status' => $fromStatus?->value,
                    'to_status' => AppointmentStatus::Cancelled->value,
                    'note' => 'Queue ticket ' . $ticket->ticket_number . ' cancelled.',
                    'changed_by' => auth()->id(),
                ]);
            }
        }

        return back()->with('success', "Ticket {$ticket->ticket_number} cancelled.");
    }

    public function appointments(Request $request)
    {
        Gate::authorize('queue.view');

        $today = now()->toDateString();

        $query = Appointment::with(['patient', 'doctor', 'department'])
            ->whereDate('date', $today)
            ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed]);

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->orderBy('time')->get();

        return view('queue.appointments', compact('appointments'));
    }
}
