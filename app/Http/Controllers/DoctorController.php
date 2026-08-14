<?php

namespace App\Http\Controllers;

use App\Enums\DayOfWeek;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{

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
            'user_id'             => auth()->id(),
        ]);

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor created successfully');
    }

    public function show(Doctor $doctor)
    {
        Gate::authorize('doctor.view');

        $doctor->load('department', 'location');

        return view('doctors.show', compact('doctor'));
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
        $dayValue = (int) $date->dayOfWeekIso;
        $time = $request->time;

        $doctors = Doctor::where('is_available', true)
            ->whereJsonContains('available_days', $dayValue)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->with('department')
            ->orderBy('name')
            ->get();

        return response()->json($doctors);
    }
}
