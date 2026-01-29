<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::where('group', 'website')
            ->pluck('value', 'key');

        return view('settings.website', compact('settings'));
    }

    public function update(Request $request)
    {
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
}
