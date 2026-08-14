<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        Gate::authorize('service.view');
        $services = Service::latest()->paginate(10);
        return view('services.index', compact('services'));
    }

    public function show(Service $service)
    {
        Gate::authorize('service.view');

        return view('services.show', compact('service'));
    }

    public function create()
    {
        Gate::authorize('service.create');
        return view('services.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('service.create');
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'service_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'features' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('service_image')) {
            $imagePath = $request->file('service_image')
                ->store('services', 'public');
        }

        Service::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'category' => $request->category,
            'description' => $request->description,
            'icon' => $request->icon,
            'features' => $request->features ? explode(',', $request->features) : null,
            'price' => $request->price,
            'service_image' => $imagePath,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('services.index')->with('success', 'Service created successfully');
    }

    public function edit(Service $service)
    {
        Gate::authorize('service.edit');
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        Gate::authorize('service.edit');
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric',
            'service_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'features' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $imagePath = $service->service_image;

        if ($request->hasFile('service_image')) {
            $imagePath = $request->file('service_image')
                ->store('services', 'public');
        }

        $service->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'category' => $request->category,
            'description' => $request->description,
            'icon' => $request->icon,
            'features' => $request->features ? explode(',', $request->features) : null,
            'price' => $request->price,
            'service_image' => $imagePath,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('services.index')->with('success', 'Service updated successfully');
    }

    public function destroy(Service $service)
    {
        Gate::authorize('service.delete');
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service deleted');
    }
}
