<?php

namespace App\Http\Controllers;

use App\Enums\DayOfWeek;
use App\Models\Doctor;
use App\Models\DoctorUnavailableDate;
use App\Models\Department;
use App\Models\Location;
use App\Services\AppointmentAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{

    public function __construct(
        private AppointmentAvailabilityService $availability
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('doctor.view');

        $query = Doctor::with(['department', 'location']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        $doctors = $query->latest()->paginate(10)->withQueryString();

        return view('doctors.index', [
            'doctors'     => $doctors,
            'departments' => Department::orderBy('name')->get(),
            'locations'   => Location::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        Gate::authorize('doctor.create');

        $departments = Department::all();
        $days = DayOfWeek::options();
        return view('doctors.create', compact('departments', 'days'));
    }

    public function store(Request $request)
    {
        Gate::authorize('doctor.create');

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'gender'            => 'nullable|in:male,female,other',
            'profile_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'department_id'     => 'nullable|exists:departments,id',
            'consultation_fee'  => 'nullable|numeric|min:0',
            'days'              => 'required|array|min:1',
            'days.*'            => 'integer|in:' . implode(',', array_keys(DayOfWeek::options())),
            'start_time'        => 'required|date_format:H:i',
            'end_time'          => 'required|date_format:H:i|after:start_time',
            'break_start'       => 'nullable|date_format:H:i',
            'break_end'         => 'nullable|date_format:H:i|after:break_start',
            'title'             => 'nullable|string|max:255',
            'role'              => 'nullable|string|max:255',
            'qualifications'    => 'nullable|string',
            'experience_years'  => 'nullable|integer|min:0',
            'board_certified'   => 'nullable|boolean',
            'short_description' => 'nullable|string|max:500',
            'biography'         => 'nullable|string',
            'is_available'      => 'nullable|boolean',
            'availability_note' => 'nullable|string|max:500',
            'is_featured'       => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')
                ->store('doctors', 'public');
        }

        Doctor::create([
            'name'                => $validated['name'],
            'slug'                => Str::slug($validated['name']),
            'gender'              => $validated['gender'] ?? null,
            'profile_image'       => $imagePath,
            'title'               => $validated['title'] ?? null,
            'role'                => $validated['role'] ?? null,
            'qualifications'      => $validated['qualifications'] ?? null,
            'experience_years'    => $validated['experience_years'] ?? 0,
            'board_certified'     => $validated['board_certified'] ?? false,
            'short_description'   => $validated['short_description'] ?? null,
            'biography'           => $validated['biography'] ?? null,
            'department_id'       => $validated['department_id'] ?? null,
            'is_available'        => $validated['is_available'] ?? true,
            'availability_note'   => $validated['availability_note'] ?? null,
            'is_featured'         => $validated['is_featured'] ?? false,
            'consultation_fee'    => $validated['consultation_fee'] ?? null,
            'available_days'      => $validated['days'],
            'start_time'          => $validated['start_time'],
            'end_time'            => $validated['end_time'],
            'break_start'         => $validated['break_start'] ?? null,
            'break_end'           => $validated['break_end'] ?? null,
            'user_id'             => auth()->id(),
        ]);

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor created successfully');
    }

    public function show(Doctor $doctor)
    {
        Gate::authorize('doctor.view');

        $doctor->load('department', 'location');

        $unavailableDates = $doctor->unavailableDates()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();

        $upcomingAppointments = $doctor->appointments()
            ->where('date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled'])
            ->with('patient')
            ->orderBy('date')
            ->orderBy('time')
            ->limit(10)
            ->get();

        return view('doctors.show', compact('doctor', 'unavailableDates', 'upcomingAppointments'));
    }

    public function edit(Doctor $doctor)
    {
        Gate::authorize('doctor.edit');

        $departments = Department::all();
        $days = DayOfWeek::options();
        return view('doctors.edit', compact('doctor', 'departments', 'days'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        Gate::authorize('doctor.edit');

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'gender'            => 'nullable|in:male,female,other',
            'profile_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'consultation_fee'  => 'nullable|numeric|min:0',
            'days'              => 'required|array|min:1',
            'days.*'            => 'integer|in:' . implode(',', array_keys(DayOfWeek::options())),
            'start_time'        => 'required|date_format:H:i',
            'end_time'          => 'required|date_format:H:i|after:start_time',
            'break_start'       => 'nullable|date_format:H:i',
            'break_end'         => 'nullable|date_format:H:i|after:break_start',
            'title'             => 'nullable|string|max:255',
            'role'              => 'nullable|string|max:255',
            'qualifications'    => 'nullable|string',
            'experience_years'  => 'nullable|integer|min:0',
            'board_certified'   => 'nullable|boolean',
            'short_description' => 'nullable|string|max:500',
            'biography'         => 'nullable|string',
            'department_id'     => 'nullable|exists:departments,id',
            'is_available'      => 'nullable|boolean',
            'availability_note' => 'nullable|string|max:500',
            'is_featured'       => 'nullable|boolean',
        ]);

        if ($request->hasFile('profile_image')) {
            if ($doctor->profile_image && Storage::disk('public')->exists($doctor->profile_image)) {
                Storage::disk('public')->delete($doctor->profile_image);
            }

            $doctor->profile_image = $request->file('profile_image')
                ->store('doctors', 'public');
        }

        $doctor->update([
            'name'                => $validated['name'],
            'slug'                => Str::slug($validated['name']),
            'gender'              => $validated['gender'] ?? null,
            'title'               => $validated['title'] ?? null,
            'role'                => $validated['role'] ?? null,
            'qualifications'      => $validated['qualifications'] ?? null,
            'experience_years'    => $validated['experience_years'] ?? 0,
            'board_certified'     => $validated['board_certified'] ?? false,
            'short_description'   => $validated['short_description'] ?? null,
            'biography'           => $validated['biography'] ?? null,
            'department_id'       => $validated['department_id'] ?? null,
            'is_available'        => $validated['is_available'] ?? false,
            'availability_note'   => $validated['availability_note'] ?? null,
            'is_featured'         => $validated['is_featured'] ?? false,
            'consultation_fee'    => $validated['consultation_fee'] ?? null,
            'available_days'      => $validated['days'],
            'start_time'          => $validated['start_time'],
            'end_time'            => $validated['end_time'],
            'break_start'         => $validated['break_start'] ?? null,
            'break_end'           => $validated['break_end'] ?? null,
        ]);

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor updated successfully');
    }

    public function destroy(Doctor $doctor)
    {
        Gate::authorize('doctor.delete');

        if ($doctor->profile_image && Storage::disk('public')->exists($doctor->profile_image)) {
            Storage::disk('public')->delete($doctor->profile_image);
        }

        $doctor->delete();

        return back()->with('success', 'Doctor deleted successfully');
    }

    public function availableDoctors(Request $request)
    {
        $request->validate([
            'date'  => 'required|date',
            'time'  => 'required',
        ]);

        $date = \Carbon\Carbon::parse($request->date);
        $time = $request->time;

        $doctors = Doctor::where('is_available', true)
            ->with('department')
            ->get()
            ->filter(function ($doctor) use ($date, $time) {
                if (! $this->availability->isWorkingDay($doctor, $date)) {
                    return false;
                }

                $hours = $this->availability->workingHours($doctor);
                if ($hours === null) {
                    return false;
                }

                if (! $this->availability->isWithinWorkingHours($doctor, $time)) {
                    return false;
                }

                if ($this->availability->isUnavailableDate($doctor, $date)) {
                    return false;
                }

                return true;
            })
            ->sortBy('name')
            ->values();

        return response()->json($doctors);
    }

    public function storeUnavailableDate(Request $request, Doctor $doctor)
    {
        Gate::authorize('doctor.edit');

        $validated = $request->validate([
            'date'   => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:100',
            'type'   => 'required|in:leave,holiday,training,emergency,other',
            'notes'  => 'nullable|string|max:1000',
        ]);

        if ($doctor->unavailableDates()->where('date', $validated['date'])->exists()) {
            return back()->with('error', 'This date is already marked as unavailable.');
        }

        $doctor->unavailableDates()->create([
            'date'       => $validated['date'],
            'reason'     => $validated['reason'] ?? null,
            'type'       => $validated['type'],
            'notes'      => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Unavailable date added successfully.');
    }

    public function destroyUnavailableDate(Doctor $doctor, DoctorUnavailableDate $unavailableDate)
    {
        Gate::authorize('doctor.edit');

        if ($unavailableDate->doctor_id !== $doctor->id) {
            abort(403);
        }

        $unavailableDate->delete();

        return back()->with('success', 'Unavailable date removed.');
    }
}
