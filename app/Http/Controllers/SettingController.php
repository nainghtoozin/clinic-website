<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function edit()
    {
        Gate::authorize('settings.view');
        $settings = Setting::where('group', 'website')
            ->pluck('value', 'key');

        return view('settings.website', compact('settings'));
    }

    public function update(Request $request)
    {
        Gate::authorize('settings.edit');

        $data = $request->validate([
            'site_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'social' => 'nullable|array',
            'social.*' => 'nullable|url',
        ]);

        foreach ($data as $key => $value) {
            if ($key === 'logo' && $request->hasFile('logo')) {
                $path = $request->file('logo')->store('settings', 'public');
                $value = $path;
            }

            Setting::updateOrCreate(
                ['key' => "site.$key"],
                ['value' => $value, 'group' => 'website']
            );

            Cache::forget("setting:site.$key");
        }

        return back()->with('success', 'Website settings updated successfully.');
    }

    public function clinic()
    {
        Gate::authorize('settings.view');
        $settings = Setting::where('group', 'clinic')
            ->pluck('value', 'key');

        return view('settings.clinic', compact('settings'));
    }

    public function updateClinic(Request $request)
    {
        Gate::authorize('settings.edit');

        $data = $request->validate([
            'clinic_name' => 'required|string|max:255',
            'clinic_email' => 'nullable|email|max:255',
            'clinic_phone' => 'nullable|string|max:50',
            'clinic_address' => 'nullable|string',
            'clinic_currency' => 'nullable|string|max:10',
            'clinic_opening_hours' => 'nullable|string|max:100',
            'clinic_default_fee' => 'nullable|numeric|min:0',
            'clinic_tax_rate' => 'nullable|numeric|min:0|max:100',
            'clinic_receipt_footer' => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'clinic']
            );
            Cache::forget("setting:{$key}");
        }

        return back()->with('success', 'Clinic settings updated successfully.');
    }
}
