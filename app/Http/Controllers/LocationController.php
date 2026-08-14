<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    public function index()
    {
        Gate::authorize('location.view');
        $locations = Location::latest()->paginate(10);
        return view('locations.index', compact('locations'));
    }

    public function show(Location $location)
    {
        Gate::authorize('location.view');

        return view('locations.show', compact('location'));
    }

    public function create()
    {
        Gate::authorize('location.create');
        return view('locations.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('location.create');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['name']);

        Location::create($data);

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function edit(Location $location)
    {
        Gate::authorize('location.edit');
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        Gate::authorize('location.edit');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $location->update($data);

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        Gate::authorize('location.delete');
        $location->delete();

        return redirect()
            ->route('locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
