<?php

namespace App\Http\Controllers;

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
        return view('department');
    }

    public function department_details()
    {
        return view('department-details');
    }

    public function doctors()
    {
        return view('doctors');
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
