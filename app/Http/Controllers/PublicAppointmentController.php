<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Services\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class PublicAppointmentController extends Controller
{
    public function __construct(
        protected AppointmentAvailabilityService $availability
    ) {
    }

    public function create()
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        $preselected = [
            'department_id' => old('department_id'),
            'doctor_id' => old('doctor_id'),
            'date' => old('date'),
            'time' => old('time'),
        ];

        return view('public-appointment.create', compact('departments', 'preselected'));
    }

    /**
     * Public endpoint — doctors available in the selected department.
     */
    public function doctors(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
        ]);

        $doctors = Doctor::with('department')
            ->where('department_id', $validated['department_id'])
            ->where('is_available', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Doctor $doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'title' => $doctor->title,
                'department' => $doctor->department?->name,
                'working_days' => $this->availability->workingDays($doctor),
                'working_hours' => $this->availability->workingHours($doctor),
                'photo_url' => $doctor->profile_image ? asset('storage/' . $doctor->profile_image) : null,
            ]);

        return response()->json(['doctors' => $doctors]);
    }

    /**
     * Public endpoint — working day + available time slots for a doctor/date.
     */
    public function availability(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        $doctor = Doctor::with('department')->findOrFail($validated['doctor_id']);
        $date = Carbon::parse($validated['date']);

        $workingDay = $this->availability->isWorkingDay($doctor, $date);
        $hours = $this->availability->workingHours($doctor);
        $slots = $workingDay && $hours
            ? $this->availability->availableSlots($doctor, $date)
            : [];

        $available = $workingDay && $hours !== null && count($slots) > 0;

        return response()->json([
            'date' => $date->toDateString(),
            'available' => $available,
            'working_day' => $workingDay,
            'working_hours' => $hours,
            'slots' => $slots,
            'message' => $this->availabilityMessage($available, $workingDay, $hours),
        ]);
    }

    public function store(Request $request)
    {
        $key = 'public-appointment:' . ($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withErrors(['general' => 'Too many appointment requests. Please try again in ' . $seconds . ' seconds.'])
                ->withInput();
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20|min:6',
            'doctor_id' => 'required|integer|exists:doctors,id',
            'department_id' => 'required|integer|exists:departments,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'message' => 'nullable|string|max:2000',
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $department = Department::findOrFail($validated['department_id']);

        // 1. Doctor must be active.
        if (! $doctor->is_available) {
            return back()->withErrors(['doctor_id' => 'This doctor is currently not accepting new appointments.'])->withInput();
        }

        // 2. Doctor must actually belong to the selected department.
        if ($doctor->department_id != $department->id) {
            return back()->withErrors([
                'doctor_id' => 'The selected doctor does not belong to the selected department.',
            ])->withInput();
        }

        // 3. Doctor must work on the requested date.
        if (! $this->availability->isWorkingDay($doctor, $validated['date'])) {
            return back()->withErrors([
                'date' => 'The selected date is not a working day for this doctor. Please choose another date.',
            ])->withInput();
        }

        // 4. Time must sit inside working hours and land exactly on a generated slot.
        if (! $this->availability->isWithinWorkingHours($doctor, $validated['time'])) {
            return back()->withErrors([
                'time' => 'This time is no longer available. Please select another time.',
            ])->withInput();
        }

        if (! $this->availability->isAvailableSlot($doctor, $validated['date'], $validated['time'])) {
            return back()->withErrors([
                'time' => 'This time is no longer available. Please select another time.',
            ])->withInput();
        }

        // 5. Real-time conflict detection (covers concurrent bookings).
        if ($this->availability->hasConflict($doctor->id, $validated['date'], $validated['time'], AppointmentAvailabilityService::DEFAULT_DURATION_MINUTES)) {
            return back()->withErrors([
                'time' => 'Sorry, this time slot was just booked. Please choose another available time.',
            ])->withInput();
        }

        $appointment = Appointment::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'],
            'doctor_id' => $validated['doctor_id'],
            'department_id' => $validated['department_id'],
            'date' => $validated['date'],
            'time' => $validated['time'],
            'duration' => AppointmentAvailabilityService::DEFAULT_DURATION_MINUTES,
            'message' => $validated['message'] ?? null,
            'status' => AppointmentStatus::Pending,
            'source' => 'public',
        ]);

        return redirect()->route('public.appointment.success')
            ->with([
                'appointment_name' => $appointment->name,
                'doctor_name' => $doctor->name,
                'appointment_date' => Carbon::parse($appointment->date)->isoFormat('dddd, DD MMMM YYYY'),
                'appointment_time' => Carbon::parse($appointment->time)->format('h:i A'),
            ]);
    }

    public function success()
    {
        if (! session('appointment_name')) {
            return redirect()->route('public.appointment.create');
        }

        return view('public-appointment.success');
    }

    protected function availabilityMessage(bool $available, bool $workingDay, ?array $hours): string
    {
        if (! $workingDay) {
            return 'This doctor does not work on this date. Please choose another day.';
        }

        return 'No appointment times are available for this doctor on this date.';
    }
}