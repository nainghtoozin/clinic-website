<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::latest()->paginate(10);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'service_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('service_image')) {
            $imagePath = $request->file('service_image')
                ->store('services', 'public');
        }

        Service::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title), // 🆕
            'category' => $request->category,
            'description' => $request->description,
            'icon' => $request->icon,
            'features' => $request->features ? explode(',', $request->features) : null,
            'price' => $request->price,
            'service_image' => $imagePath,
            'status' => $request->status ?? 0,
        ]);

        return redirect()->route('services.index')->with('success', 'Service created successfully');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'service_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $service->service_image;

        if ($request->hasFile('service_image')) {
            $imagePath = $request->file('service_image')
                ->store('services', 'public');
        }

        $service->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title), // 🆕 auto update
            'category' => $request->category,
            'description' => $request->description,
            'icon' => $request->icon,
            'features' => $request->features ? explode(',', $request->features) : null,
            'price' => $request->price,
            'service_image' => $imagePath,
            'status' => $request->status ?? 0,
        ]);

        return redirect()->route('services.index')->with('success', 'Service updated successfully');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service deleted');
    }
}
