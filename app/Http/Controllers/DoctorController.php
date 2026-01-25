<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{

    public function index(Request $request)
    {
        $query = Doctor::with(['department', 'location']);

        // 🔍 Name search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🏥 Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 📍 Location filter
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        // ✅ Status filter
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
        $departments = Department::all();
        return view('doctors.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'gender'            => 'nullable|in:male,female,other',
            'profile_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'department_id'     => 'nullable|exists:departments,id',
        ]);

        // 👉 Image Upload
        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')
                ->store('doctors', 'public');
        }

        Doctor::create([
            'name'                => $request->name,
            'slug'                => Str::slug($request->name),
            'gender'              => $request->gender,
            'profile_image'       => $imagePath,

            'title'               => $request->title,
            'role'                => $request->role,
            'qualifications'      => $request->qualifications,
            'experience_years'    => $request->experience_years ?? 0,
            'board_certified'     => $request->board_certified ?? false,

            'primary_department'  => $request->primary_department,
            'short_description'   => $request->short_description,
            'biography'           => $request->biography,
            'location'            => $request->location,
            'department_id'       => $request->department_id,

            'is_available'        => $request->is_available ?? true,
            'availability_note'  => $request->availability_note,
            'is_featured'         => $request->is_featured ?? false,

            'user_id'             => auth()->id(),
        ]);

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor created successfully');
    }

    public function edit(Doctor $doctor)
    {
        $departments = Department::all();
        return view('doctors.edit', compact('doctor', 'departments'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'gender'        => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // 👉 Image Replace
        if ($request->hasFile('profile_image')) {

            // delete old image
            if ($doctor->profile_image && Storage::disk('public')->exists($doctor->profile_image)) {
                Storage::disk('public')->delete($doctor->profile_image);
            }

            $doctor->profile_image = $request->file('profile_image')
                ->store('doctors', 'public');
        }

        $doctor->update([
            'name'                => $request->name,
            'slug'                => Str::slug($request->name),
            'gender'              => $request->gender,

            'title'               => $request->title,
            'role'                => $request->role,
            'qualifications'      => $request->qualifications,
            'experience_years'    => $request->experience_years,
            'board_certified'     => $request->board_certified ?? false,

            'primary_department'  => $request->primary_department,
            'short_description'   => $request->short_description,
            'biography'           => $request->biography,
            'location'            => $request->location,
            'department_id'       => $request->department_id,

            'is_available'        => $request->is_available ?? false,
            'availability_note'  => $request->availability_note,
            'is_featured'         => $request->is_featured ?? false,
        ]);

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor updated successfully');
    }

    public function destroy(Doctor $doctor)
    {
        // 👉 delete image
        if ($doctor->profile_image && Storage::disk('public')->exists($doctor->profile_image)) {
            Storage::disk('public')->delete($doctor->profile_image);
        }

        $doctor->delete();

        return back()->with('success', 'Doctor deleted successfully');
    }
}
