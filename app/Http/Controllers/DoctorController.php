<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = Doctor::with(['departments', 'locations']);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('title', 'like', '%' . $request->search . '%');
        }

        if ($request->department) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('id', $request->department);
            });
        }

        $doctors = $query->latest()->paginate(12);

        $departments = Department::all();

        return view('doctors.index', compact('doctors', 'departments'));
    }

    public function create()
    {
        return view('doctors.create', [
            'departments' => Department::all(),
            'locations' => Location::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'profile_image' => 'nullable|image',
            'title' => 'nullable',
            'role' => 'nullable',
            'qualifications' => 'nullable',
            'experience_years' => 'nullable|integer',
            'short_description' => 'nullable',
            'biography' => 'nullable',
            'departments' => 'array',
            'locations' => 'array'
        ]);

        $data['slug'] = Str::slug($request->name);
        $data['board_certified'] = $request->has('board_certified');
        $data['is_available'] = $request->has('is_available');

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store('doctors', 'public');
        }

        $doctor = Doctor::create($data);

        $doctor->departments()->sync($request->departments);
        $doctor->locations()->sync($request->locations);

        return redirect()->route('doctors.index')->with('success', 'Doctor created successfully');
    }
}
