<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\Location;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        return view('index');
    }
    public function error()
    {
        return view('404');
    }

    public function about()
    {
        return view('about');
    }

    public function appointment()
    {
        return view('appointment');
    }

    public function contact()
    {
        return view('contact');
    }

    public function department()
    {
        $departments = Department::orderBy('sort_order')->paginate(10);
        return view('department', compact('departments'));
    }

    public function department_details()
    {
        return view('department-details');
    }

    public function doctors(Request $request)
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
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $doctors = $query->latest()->paginate(10)->withQueryString();
        return view('doctors', [
            'doctors'     => $doctors,
            'departments' => Department::orderBy('name')->get(),
            'locations'   => Location::orderBy('name')->get(),
        ]);
    }

    public function faq()
    {
        return view('faq');
    }

    public function gallery()
    {
        return view('gallery');
    }

    public function gallery_details()
    {
        return view('gallery');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function service_details()
    {
        return view('service-details');
    }

    public function services()
    {
        return view('services');
    }

    public function starter_page()
    {
        return view('starter-page');
    }

    public function terms()
    {
        return view('terms');
    }

    public function testimonial()
    {
        return view('testimonials');
    }
}
