<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentAvailabilityService $availability
    ) {
    }
    public function index(Request $request)
    {
        Gate::authorize('appointment.view');

        $query = Appointment::with(['doctor', 'department', 'patient']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('appointment_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($pq) => $pq->where('name', 'like', "%{$search}%")
                      ->orWhere('patient_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $appointments = $query->latest('date')->latest('time')->paginate(15)->withQueryString();

        $doctors = \App\Models\Doctor::orderBy('name')->get();

        return view('appointments.index', compact('appointments', 'doctors'));
    }

    public function create(Request $request)
    {
        Gate::authorize('appointment.create');

        $patients = Patient::where('status', 'active')->orderBy('name')->get();
        $doctors = Doctor::where('is_available', true)->with('department')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        $selectedPatient = $request->patient_id;
        $selectedDoctor = $request->doctor_id;

        return view('appointments.create', compact('patients', 'doctors', 'departments', 'selectedPatient', 'selectedDoctor'));
    }

    public function store(Request $request)
    {
        Gate::authorize('appointment.create');

        $validated = $request->validate([
            'patient_id'    => 'required|exists:patients,id',
            'doctor_id'     => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'date'          => 'required|date|after_or_equal:today',
            'time'          => 'required|date_format:H:i',
            'duration'      => 'required|integer|min:15|max:180',
            'message'       => 'nullable|string|max:1000',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        $doctor = Doctor::findOrFail($validated['doctor_id']);

        if (!$doctor->is_available) {
            return back()->withErrors(['doctor_id' => 'This doctor is currently unavailable.'])->withInput();
        }

        $appointmentDate = \Carbon\Carbon::parse($validated['date']);

        // 3. Doctor must work on the requested date.
        if (! $this->availability->isWorkingDay($doctor, $appointmentDate)) {
            return back()->withErrors(['date' => 'Doctor is not available on ' . $appointmentDate->isoFormat('dddd') . '. Available days: ' . implode(', ', array_map(fn($d) => \App\Enums\DayOfWeek::from($d)->label(), $doctor->available_days ?? []))])->withInput();
        }

        // 4. The doctor must have a valid schedule (start < end) and the booking
        // must start and finish inside the working hours. The shared availability
        // service normalizes the schedule so invalid data never leaks a
        // nonsense range such as "17:39 - 11:14" into the UI.
        if (! $this->availability->workingHours($doctor)) {
            return back()->withErrors([
                'time' => 'This doctor does not have a working schedule set up. Please choose another doctor.',
            ])->withInput();
        }

        if (! $this->availability->isWithinWorkingHours($doctor, $validated['time'], $validated['duration'])) {
            return back()->withErrors(['time' => 'Requested time is outside doctor\'s working hours.'])->withInput();
        }

        if ($this->availability->hasConflict($doctor->id, $validated['date'], $validated['time'], $validated['duration'])) {
            return back()->withErrors(['time' => 'This doctor already has an appointment at the requested time.'])->withInput();
        }

        $validated['patient_id'] = $patient->id;
        $validated['name'] = $patient->name;
        $validated['email'] = $patient->email;
        $validated['phone'] = $patient->phone;
        $validated['appointment_number'] = Appointment::generateAppointmentNumber();
        $validated['status'] = AppointmentStatus::Scheduled;
        $validated['source'] = 'admin';

        Appointment::create($validated);

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment scheduled successfully.');
    }

    public function show(Appointment $appointment)
    {
        Gate::authorize('appointment.view');

        $appointment->load(['doctor', 'department', 'patient', 'statusHistories.changedBy']);

        $allowedTransitions = collect($this->allowedTransitions($appointment->status))
            ->map(fn (AppointmentStatus $status) => ['value' => $status->value, 'label' => $status->label()])
            ->values()
            ->all();

        return view('appointments.show', compact('appointment', 'allowedTransitions'));
    }

    public function edit(Appointment $appointment)
    {
        Gate::authorize('appointment.edit');

        $appointment->load(['doctor', 'department', 'patient']);

        $patients = Patient::where('status', 'active')->orderBy('name')->get();
        $doctors = Doctor::where('is_available', true)->with('department')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('appointments.edit', compact('appointment', 'patients', 'doctors', 'departments'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        Gate::authorize('appointment.edit');

        $validated = $request->validate([
            'patient_id'    => 'required|exists:patients,id',
            'doctor_id'     => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'date'          => 'required|date|after_or_equal:today',
            'time'          => 'required|date_format:H:i',
            'duration'      => 'required|integer|min:15|max:180',
            'message'       => 'nullable|string|max:1000',
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);

        if (!$doctor->is_available) {
            return back()->withErrors(['doctor_id' => 'This doctor is currently unavailable.'])->withInput();
        }

        $appointmentDate = \Carbon\Carbon::parse($validated['date']);

        if (! $this->availability->isWorkingDay($doctor, $appointmentDate)) {
            return back()->withErrors(['date' => 'Doctor is not available on ' . $appointmentDate->isoFormat('dddd') . '.'])->withInput();
        }

        if (! $this->availability->workingHours($doctor)) {
            return back()->withErrors([
                'time' => 'This doctor does not have a working schedule set up. Please choose another doctor.',
            ])->withInput();
        }

        if (! $this->availability->isWithinWorkingHours($doctor, $validated['time'], $validated['duration'])) {
            return back()->withErrors(['time' => 'Requested time is outside doctor\'s working hours.'])->withInput();
        }

        if ($this->availability->hasConflict($doctor->id, $validated['date'], $validated['time'], $validated['duration'], $appointment->id)) {
            return back()->withErrors(['time' => 'This doctor already has an appointment at the requested time.'])->withInput();
        }

        $patient = Patient::findOrFail($validated['patient_id']);
        $validated['patient_id'] = $patient->id;
        $validated['name'] = $patient->name;
        $validated['email'] = $patient->email;
        $validated['phone'] = $patient->phone;

        $appointment->update($validated);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment rescheduled successfully.');
    }

    public function confirm(Appointment $appointment)
    {
        Gate::authorize('appointment.edit');

        if (!in_array($appointment->status, [AppointmentStatus::Scheduled, AppointmentStatus::Pending])) {
            return back()->with('error', 'Only scheduled or pending appointments can be confirmed.');
        }

        if (!$appointment->patient_id && $appointment->email) {
            $patient = Patient::where('email', $appointment->email)->first();
            if (!$patient && $appointment->phone) {
                $patient = Patient::where('phone', $appointment->phone)->first();
            }
            if ($patient) {
                $appointment->update(['patient_id' => $patient->id]);
            }
        }

        $this->applyStatusChange($appointment, AppointmentStatus::Confirmed, 'Confirmed by staff.');

        return back()->with('success', 'Appointment confirmed.');
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        Gate::authorize('appointment.cancel');

        if ($appointment->isCancelled()) {
            return back()->with('error', 'Appointment is already cancelled.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:1000',
        ]);

        $note = $validated['note'] ?? $validated['cancel_reason'] ?? null;

        if (blank($note)) {
            return back()->withErrors(['cancel_reason' => 'A cancellation/rejection reason is required.']);
        }

        $this->applyStatusChange($appointment, AppointmentStatus::Cancelled, $note);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function complete(Appointment $appointment)
    {
        Gate::authorize('appointment.edit');

        if ($appointment->isCancelled()) {
            return back()->with('error', 'Cannot complete a cancelled appointment.');
        }

        $this->applyStatusChange($appointment, AppointmentStatus::Completed, 'Marked as completed.');

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        Gate::authorize('appointment.edit');

        $validated = $request->validate([
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'note' => 'nullable|string|max:1000',
        ]);

        $toStatus = AppointmentStatus::from($validated['status']);

        if (!in_array($toStatus, $this->allowedTransitions($appointment->status), true)) {
            $message = "Cannot change appointment from {$appointment->status->label()} to {$toStatus->label()}.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'errors' => ['status' => [$message]]], 422);
            }

            return back()->withErrors(['status' => $message])->withInput();
        }

        if ($toStatus === AppointmentStatus::Cancelled && blank($validated['note'] ?? null)) {
            $message = 'A cancellation/rejection reason is required.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'errors' => ['note' => [$message]]], 422);
            }

            return back()->withErrors(['note' => $message])->withInput();
        }

        $this->applyStatusChange($appointment, $toStatus, $validated['note'] ?? null);

        $message = "Appointment marked as {$toStatus->label()}.";

        if ($request->expectsJson()) {
            $request->session()->flash('success', $message);

            return response()->json(['message' => $message, 'status' => $toStatus->value, 'label' => $toStatus->label()]);
        }

        return back()->with('success', $message);
    }

    protected function allowedTransitions(?AppointmentStatus $current): array
    {
        return match ($current) {
            AppointmentStatus::Pending,
            AppointmentStatus::Scheduled => [AppointmentStatus::Confirmed, AppointmentStatus::Cancelled],
            AppointmentStatus::Confirmed => [AppointmentStatus::Cancelled, AppointmentStatus::Completed],
            AppointmentStatus::CheckedIn => [AppointmentStatus::Cancelled, AppointmentStatus::Completed],
            default => [],
        };
    }

    protected function applyStatusChange(Appointment $appointment, AppointmentStatus $toStatus, ?string $note = null): void
    {
        $fromStatus = $appointment->status;

        $updateData = ['status' => $toStatus];

        if ($toStatus === AppointmentStatus::Confirmed && !$appointment->appointment_number) {
            $updateData['appointment_number'] = Appointment::generateAppointmentNumber();
        }

        if ($toStatus === AppointmentStatus::Cancelled) {
            $updateData['cancel_reason'] = $note;
        }

        DB::transaction(function () use ($appointment, $updateData, $fromStatus, $toStatus, $note) {
            $appointment->update($updateData);

            $appointment->statusHistories()->create([
                'from_status' => $fromStatus?->value,
                'to_status' => $toStatus->value,
                'note' => $note,
                'changed_by' => auth()->id(),
            ]);
        });
    }

    public function destroy(Appointment $appointment)
    {
        Gate::authorize('appointment.delete');

        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}
