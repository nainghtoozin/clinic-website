<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\Doctor;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $appointments = Appointment::with(['doctor', 'department'])
            ->when(
                $request->doctor_id,
                fn($q) =>
                $q->where('doctor_id', $request->doctor_id)
            )
            ->when(
                $request->department_id,
                fn($q) =>
                $q->where('department_id', $request->department_id)
            )
            ->when(
                $request->status,
                fn($q) =>
                $q->where('status', $request->status)
            )
            ->when(
                $request->date_from,
                fn($q) =>
                $q->whereDate('appointment_date', '>=', $request->date_from)
            )
            ->when(
                $request->date_to,
                fn($q) =>
                $q->whereDate('appointment_date', '<=', $request->date_to)
            )
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->order);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('appointments.index', [
            'appointments' => $appointments,
            'doctors' => Doctor::all(),
            'departments' => Department::all(),
        ]);
    }


    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:approved,cancelled'
        ]);

        $appointment->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Appointment updated');
    }


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
