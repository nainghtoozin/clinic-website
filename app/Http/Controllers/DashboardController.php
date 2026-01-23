<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use PhpParser\Comment\Doc;

class DashboardController extends Controller
{
    public function index()
    {
        $appointments = Appointment::latest()->take(5)->get();
        $doctorCount = Doctor::count();
        $appointmentCount = Appointment::count();
        $pending = Appointment::where('status', 'pending')->count();
        $cancelled = Appointment::where('status', 'cancelled')->count();
        $completed = Appointment::where('status', 'approved')->count();


        return view('dashboard', compact('appointments', 'doctorCount', 'appointmentCount', 'pending', 'cancelled', 'completed'));
    }
}
