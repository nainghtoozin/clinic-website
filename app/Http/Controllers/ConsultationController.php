<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('consultation.view');

        $query = Consultation::with(['patient', 'doctor', 'appointment']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                  ->orWhere('symptoms', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'like', "%{$search}%")
                      ->orWhere('patient_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $consultations = $query->latest()->paginate(15)->withQueryString();

        $doctors = \App\Models\Doctor::orderBy('name')->get();

        return view('consultations.index', compact('consultations', 'doctors'));
    }

    public function create(Request $request)
    {
        Gate::authorize('consultation.create');

        $ticket = null;
        $patient = null;
        $appointment = null;

        if ($request->filled('queue_ticket_id')) {
            $ticket = QueueTicket::with(['patient', 'doctor', 'appointment'])
                ->findOrFail($request->queue_ticket_id);

            if (!$ticket->isInConsultation()) {
                return redirect()->route('queue.index')
                    ->with('error', 'This queue ticket is not in consultation.');
            }

            $existingConsultation = Consultation::where('queue_ticket_id', $ticket->id)
                ->where('status', 'draft')
                ->first();

            if ($existingConsultation) {
                return redirect()->route('consultations.edit', $existingConsultation);
            }

            $patient = $ticket->patient;
            $appointment = $ticket->appointment;
        }

        $patients = Patient::where('status', 'active')->orderBy('name')->get();
        $doctors = \App\Models\Doctor::where('is_available', true)->with('department')->orderBy('name')->get();

        return view('consultations.create', compact('ticket', 'patient', 'appointment', 'patients', 'doctors'));
    }

    public function store(Request $request)
    {
        Gate::authorize('consultation.create');

        $validated = $request->validate([
            'patient_id'       => 'required|exists:patients,id',
            'doctor_id'        => 'required|exists:doctors,id',
            'queue_ticket_id'  => 'nullable|exists:queue_tickets,id',
            'appointment_id'   => 'nullable|exists:appointments,id',
            'symptoms'         => 'nullable|string|max:2000',
            'diagnosis'        => 'nullable|string|max:2000',
            'clinical_notes'   => 'nullable|string|max:5000',
            'treatment_plan'   => 'nullable|string|max:2000',
            'follow_up_date'   => 'nullable|date|after:today',
            'follow_up_notes'  => 'nullable|string|max:1000',

            'blood_pressure'      => 'nullable|string|max:20',
            'temperature'         => 'nullable|numeric|min:30|max:45',
            'pulse'               => 'nullable|integer|min:30|max:250',
            'respiratory_rate'    => 'nullable|integer|min:5|max:60',
            'weight'              => 'nullable|numeric|min:0|max:500',
            'height'              => 'nullable|numeric|min:0|max:300',
            'oxygen_saturation'   => 'nullable|numeric|min:0|max:100',
        ]);

        if (isset($validated['queue_ticket_id'])) {
            $ticket = QueueTicket::find($validated['queue_ticket_id']);
            if ($ticket && !$ticket->isInConsultation()) {
                return back()->withErrors(['queue_ticket_id' => 'This queue ticket is not in consultation.'])->withInput();
            }
        }

        if (isset($validated['appointment_id'])) {
            $existingConsultation = Consultation::where('appointment_id', $validated['appointment_id'])
                ->first();

            if ($existingConsultation) {
                return back()->withErrors(['appointment_id' => 'A consultation already exists for this appointment.'])->withInput();
            }
        }

        $consultation = DB::transaction(function () use ($validated) {
            $vitalSignData = [
                'blood_pressure'    => $validated['blood_pressure'] ?? null,
                'temperature'       => $validated['temperature'] ?? null,
                'pulse'             => $validated['pulse'] ?? null,
                'respiratory_rate'  => $validated['respiratory_rate'] ?? null,
                'weight'            => $validated['weight'] ?? null,
                'height'            => $validated['height'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'recorded_at'       => now(),
            ];

            unset(
                $validated['blood_pressure'], $validated['temperature'],
                $validated['pulse'], $validated['respiratory_rate'],
                $validated['weight'], $validated['height'],
                $validated['oxygen_saturation']
            );

            $consultation = Consultation::create($validated);

            $hasVitalSigns = collect($vitalSignData)->except(['consultation_id', 'recorded_at'])->filter()->isNotEmpty();

            if ($hasVitalSigns) {
                $vitalSignData['consultation_id'] = $consultation->id;
                VitalSign::create($vitalSignData);
            }

            return $consultation;
        });

        AuditService::logCreated($consultation, 'Consultation');

        NotificationService::notify(
            $consultation->doctor->user_id ?? auth()->id(),
            'consultation',
            'Consultation Started',
            "Consultation for patient {$consultation->patient->name} has been started.",
            $consultation,
            'consultation',
            'created',
            route('consultations.show', $consultation)
        );

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation saved successfully.');
    }

    public function show(Consultation $consultation)
    {
        Gate::authorize('consultation.view');

        $consultation->load(['patient', 'doctor', 'appointment', 'queueTicket', 'vitalSign', 'invoice', 'prescriptions.items.medicine', 'investigations.labTest']);

        return view('consultations.show', compact('consultation'));
    }

    public function edit(Consultation $consultation)
    {
        Gate::authorize('consultation.edit');

        $consultation->load(['patient', 'doctor', 'appointment', 'queueTicket', 'vitalSign']);

        return view('consultations.edit', compact('consultation'));
    }

    public function update(Request $request, Consultation $consultation)
    {
        Gate::authorize('consultation.edit');

        $validated = $request->validate([
            'symptoms'         => 'nullable|string|max:2000',
            'diagnosis'        => 'nullable|string|max:2000',
            'clinical_notes'   => 'nullable|string|max:5000',
            'treatment_plan'   => 'nullable|string|max:2000',
            'follow_up_date'   => 'nullable|date|after:today',
            'follow_up_notes'  => 'nullable|string|max:1000',

            'blood_pressure'      => 'nullable|string|max:20',
            'temperature'         => 'nullable|numeric|min:30|max:45',
            'pulse'               => 'nullable|integer|min:30|max:250',
            'respiratory_rate'    => 'nullable|integer|min:5|max:60',
            'weight'              => 'nullable|numeric|min:0|max:500',
            'height'              => 'nullable|numeric|min:0|max:300',
            'oxygen_saturation'   => 'nullable|numeric|min:0|max:100',
        ]);

        $vitalSignData = [
            'blood_pressure'    => $validated['blood_pressure'] ?? null,
            'temperature'       => $validated['temperature'] ?? null,
            'pulse'             => $validated['pulse'] ?? null,
            'respiratory_rate'  => $validated['respiratory_rate'] ?? null,
            'weight'            => $validated['weight'] ?? null,
            'height'            => $validated['height'] ?? null,
            'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
            'recorded_at'       => now(),
        ];

        unset(
            $validated['blood_pressure'], $validated['temperature'],
            $validated['pulse'], $validated['respiratory_rate'],
            $validated['weight'], $validated['height'],
            $validated['oxygen_saturation']
        );

        $old = $consultation->toArray();

        DB::transaction(function () use ($consultation, $validated, $vitalSignData) {
            $consultation->update($validated);

            $hasVitalSigns = collect($vitalSignData)->filter()->isNotEmpty();

            if ($hasVitalSigns) {
                if ($consultation->vitalSign) {
                    $consultation->vitalSign->update($vitalSignData);
                } else {
                    $vitalSignData['consultation_id'] = $consultation->id;
                    VitalSign::create($vitalSignData);
                }
            }
        });

        AuditService::logUpdated($consultation, 'Consultation', $old, $validated);

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation updated successfully.');
    }

    public function complete(Consultation $consultation)
    {
        Gate::authorize('consultation.complete');

        if ($consultation->isCompleted()) {
            return back()->with('error', 'Consultation is already completed.');
        }

        $oldStatus = $consultation->status;

        DB::transaction(function () use ($consultation) {
            $consultation->update(['status' => 'completed']);

            if ($consultation->queue_ticket_id) {
                $ticket = QueueTicket::find($consultation->queue_ticket_id);
                if ($ticket && $ticket->isInConsultation()) {
                    $ticket->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                }
            }

            if ($consultation->appointment_id) {
                $appointment = \App\Models\Appointment::find($consultation->appointment_id);
                if ($appointment && $appointment->isCheckedIn()) {
                    $appointment->update(['status' => AppointmentStatus::Completed]);
                }
            }
        });

        AuditService::logStatusChange($consultation, 'Consultation', $oldStatus, 'completed');

        NotificationService::notify(
            $consultation->doctor->user_id ?? auth()->id(),
            'consultation',
            'Consultation Completed',
            "Consultation for patient {$consultation->patient->name} has been completed.",
            $consultation,
            'consultation',
            'completed',
            route('consultations.show', $consultation)
        );

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation completed successfully.');
    }
}
