<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditService;
use App\Services\ClinicSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    public function index()
    {
        Gate::authorize('settings.view');

        return view('settings.index', [
            'activeTab' => 'clinic',
        ]);
    }

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
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'social' => 'nullable|array',
            'social.*' => 'nullable|url',
        ]);

        $oldSettings = Setting::whereIn('key', array_map(fn($key) => "site.$key", array_keys($data)))
            ->pluck('value', 'key')
            ->toArray();

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

        $newSettings = Setting::whereIn('key', array_map(fn($key) => "site.$key", array_keys($data)))
            ->pluck('value', 'key')
            ->toArray();

        AuditService::log('settings_updated', 'Settings', null, 'Website settings updated', $oldSettings, $newSettings);

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

        $oldSettings = Setting::whereIn('key', array_keys($data))
            ->pluck('value', 'key')
            ->toArray();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'clinic']
            );
            Cache::forget("setting:{$key}");
        }

        $newSettings = Setting::whereIn('key', array_keys($data))
            ->pluck('value', 'key')
            ->toArray();

        AuditService::log('settings_updated', 'Settings', null, 'Clinic settings updated', $oldSettings, $newSettings);

        return back()->with('success', 'Clinic settings updated successfully.');
    }

    public function appointment()
    {
        Gate::authorize('settings.view');
        $settings = ClinicSettingsService::all('appointment');

        return view('settings.appointment', compact('settings'));
    }

    public function updateAppointment(Request $request)
    {
        Gate::authorize('settings.edit');

        $data = $request->validate([
            'default_duration' => 'required|integer|min:5|max:480',
            'min_duration' => 'required|integer|min:5|max:120',
            'max_duration' => 'required|integer|min:15|max:480',
            'advance_booking_days' => 'nullable|integer|min:1|max:365',
            'cancellation_hours' => 'nullable|integer|min:0|max:168',
        ]);

        $oldSettings = [];
        $newSettings = [];

        foreach ($data as $key => $value) {
            $settingKey = "appointment.{$key}";
            $old = Setting::where('key', $settingKey)->value('value');
            $oldSettings[$settingKey] = $old;

            ClinicSettingsService::set($settingKey, $value, 'appointment');
            $newSettings[$settingKey] = $value;
        }

        AuditService::log('settings_updated', 'Settings', null, 'Appointment settings updated', $oldSettings, $newSettings);

        return back()->with('success', 'Appointment settings updated successfully.');
    }

    public function queue()
    {
        Gate::authorize('settings.view');
        $settings = ClinicSettingsService::all('queue');

        return view('settings.queue', compact('settings'));
    }

    public function updateQueue(Request $request)
    {
        Gate::authorize('settings.edit');

        $data = $request->validate([
            'ticket_prefix' => 'required|string|max:5',
            'ticket_sequence_length' => 'required|integer|min:2|max:6',
        ]);

        $oldSettings = [];
        $newSettings = [];

        foreach ($data as $key => $value) {
            $settingKey = "queue.{$key}";
            $old = Setting::where('key', $settingKey)->value('value');
            $oldSettings[$settingKey] = $old;

            ClinicSettingsService::set($settingKey, $value, 'queue');
            $newSettings[$settingKey] = $value;
        }

        AuditService::log('settings_updated', 'Settings', null, 'Queue settings updated', $oldSettings, $newSettings);

        return back()->with('success', 'Queue settings updated successfully.');
    }

    public function billing()
    {
        Gate::authorize('settings.view');
        $settings = ClinicSettingsService::all('billing');

        return view('settings.billing', compact('settings'));
    }

    public function updateBilling(Request $request)
    {
        Gate::authorize('settings.edit');

        $data = $request->validate([
            'prefix' => 'required|string|max:10',
            'sequence_length' => 'required|integer|min:2|max:6',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $oldSettings = [];
        $newSettings = [];

        foreach ($data as $key => $value) {
            $settingKey = "invoice.{$key}";
            $old = Setting::where('key', $settingKey)->value('value');
            $oldSettings[$settingKey] = $old;

            ClinicSettingsService::set($settingKey, $value, 'billing');
            $newSettings[$settingKey] = $value;
        }

        AuditService::log('settings_updated', 'Settings', null, 'Billing settings updated', $oldSettings, $newSettings);

        return back()->with('success', 'Billing settings updated successfully.');
    }

    public function inventory()
    {
        Gate::authorize('settings.view');
        $settings = ClinicSettingsService::all('inventory');

        return view('settings.inventory', compact('settings'));
    }

    public function updateInventory(Request $request)
    {
        Gate::authorize('settings.edit');

        $data = $request->validate([
            'expiry_warning_days' => 'required|integer|min:1|max:365',
        ]);

        $oldSettings = [];
        $newSettings = [];

        foreach ($data as $key => $value) {
            $settingKey = "inventory.{$key}";
            $old = Setting::where('key', $settingKey)->value('value');
            $oldSettings[$settingKey] = $old;

            ClinicSettingsService::set($settingKey, $value, 'inventory');
            $newSettings[$settingKey] = $value;
        }

        AuditService::log('settings_updated', 'Settings', null, 'Inventory settings updated', $oldSettings, $newSettings);

        return back()->with('success', 'Inventory settings updated successfully.');
    }

    public function prescription()
    {
        Gate::authorize('settings.view');
        $settings = ClinicSettingsService::all('prescription');

        return view('settings.prescription', compact('settings'));
    }

    public function updatePrescription(Request $request)
    {
        Gate::authorize('settings.edit');

        $data = $request->validate([
            'prefix' => 'required|string|max:10',
            'sequence_length' => 'required|integer|min:2|max:6',
        ]);

        $oldSettings = [];
        $newSettings = [];

        foreach ($data as $key => $value) {
            $settingKey = "prescription.{$key}";
            $old = Setting::where('key', $settingKey)->value('value');
            $oldSettings[$settingKey] = $old;

            ClinicSettingsService::set($settingKey, $value, 'prescription');
            $newSettings[$settingKey] = $value;
        }

        AuditService::log('settings_updated', 'Settings', null, 'Prescription settings updated', $oldSettings, $newSettings);

        return back()->with('success', 'Prescription settings updated successfully.');
    }
}
