<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function create(Request $request)
    {
        return view('appointment', [
            'departments' => Department::with('doctors')->get(),
            'selectedDoctor' => $request->doctor
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'email'         => 'required|email',
            'phone'         => 'required',
            'department_id' => 'required|exists:departments,id',
            'doctor_id'     => 'required|exists:doctors,id',
            'date'          => 'required|date',
        ]);

        Appointment::create($request->all());

        return back()->with('success', 'Appointment booked successfully!');
    }
}
