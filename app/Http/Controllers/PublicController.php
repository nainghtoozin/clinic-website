<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Department;
use App\Models\Location;
use App\Models\Service;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::orderBy('sort_order')->paginate(10);
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
        return view('index', [
            'doctors'     => $doctors,
            'departments' => Department::orderBy('name')->get(),
            'locations'   => Location::orderBy('name')->get(),
        ]);
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
        if ($request->filled('is_available')) {
            $query->where('is_available', $request->is_available);
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

    public function service_details(Service $service)
    {
        return view('service-details', compact('service'));
    }

    public function services()
    {
        $services = Service::get();
        return view('services', compact('services'));
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
