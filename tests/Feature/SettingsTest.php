<?php

use App\Models\Setting;
use App\Models\User;
use App\Services\ClinicSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\PermissionSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

describe('ClinicSettingsService', function () {
    it('returns default value for unknown key', function () {
        expect(ClinicSettingsService::get('unknown.key', 'fallback'))->toBe('fallback');
    });

    it('sets and gets a setting', function () {
        ClinicSettingsService::set('test.key', 'test_value');
        expect(ClinicSettingsService::get('test.key'))->toBe('test_value');
    });

    it('sets setting with correct group', function () {
        ClinicSettingsService::set('test.key', 'test_value', 'billing');
        $setting = Setting::where('key', 'test.key')->first();
        expect($setting->group)->toBe('billing');
    });

    it('returns cached value', function () {
        Setting::create(['key' => 'test.cached', 'value' => 'cached_val']);
        expect(ClinicSettingsService::get('test.cached'))->toBe('cached_val');
    });

    it('returns typed int value', function () {
        ClinicSettingsService::set('test.int', '42');
        expect(ClinicSettingsService::getInt('test.int'))->toBe(42);
    });

    it('returns typed float value', function () {
        ClinicSettingsService::set('test.float', '3.14');
        expect(ClinicSettingsService::getFloat('test.float'))->toBe(3.14);
    });

    it('returns typed bool value', function () {
        ClinicSettingsService::set('test.bool', '1');
        expect(ClinicSettingsService::getBool('test.bool'))->toBeTrue();

        ClinicSettingsService::set('test.bool2', '0');
        expect(ClinicSettingsService::getBool('test.bool2'))->toBeFalse();
    });

    it('sets many settings at once', function () {
        ClinicSettingsService::setMany([
            'test.a' => 'val_a',
            'test.b' => 'val_b',
        ], 'group1');

        expect(ClinicSettingsService::get('test.a'))->toBe('val_a');
        expect(ClinicSettingsService::get('test.b'))->toBe('val_b');
    });

    it('gets all settings for a group', function () {
        Setting::create(['key' => 'group1.x', 'value' => 'gx', 'group' => 'group1']);
        Setting::create(['key' => 'group1.y', 'value' => 'gy', 'group' => 'group1']);

        $all = ClinicSettingsService::all('group1');
        expect($all)->toHaveKeys(['group1.x', 'group1.y']);
    });

    it('forgets a setting', function () {
        ClinicSettingsService::set('test.forget', 'val');
        expect(ClinicSettingsService::get('test.forget'))->toBe('val');

        ClinicSettingsService::forget('test.forget');
        expect(Setting::where('key', 'test.forget')->exists())->toBeFalse();
    });

    it('returns default for null value', function () {
        expect(ClinicSettingsService::get('null.key', 'default_val'))->toBe('default_val');
    });

    it('returns default config values for known keys', function () {
        expect(ClinicSettingsService::get('appointment.default_duration'))->toBe(30);
        expect(ClinicSettingsService::get('queue.ticket_prefix'))->toBe('A');
        expect(ClinicSettingsService::get('invoice.prefix'))->toBe('INV');
        expect(ClinicSettingsService::get('prescription.prefix'))->toBe('RX');
        expect(ClinicSettingsService::get('inventory.expiry_warning_days'))->toBe(30);
    });

    it('parses JSON array values', function () {
        ClinicSettingsService::set('test.arr', json_encode([1, 2, 3]));
        $result = ClinicSettingsService::getArray('test.arr');
        expect($result)->toBe([1, 2, 3]);
    });
});

describe('SettingController Clinic', function () {
    it('renders clinic settings page', function () {
        $this->get(route('settings.clinic'))
            ->assertOk()
            ->assertSee('Clinic Settings')
            ->assertSee('clinic_name');
    });

    it('updates clinic settings', function () {
        $this->post(route('settings.clinic.update'), [
            'clinic_name' => 'My Clinic',
            'clinic_email' => 'info@myclinic.com',
            'clinic_phone' => '1234567890',
            'clinic_currency' => 'EUR',
            'clinic_opening_hours' => 'Mon-Fri 9-5',
            'clinic_default_fee' => '100.00',
            'clinic_tax_rate' => '10',
            'clinic_address' => '123 Main St',
            'clinic_receipt_footer' => 'Thank you!',
        ])->assertRedirect();

        expect(ClinicSettingsService::get('clinic_name'))->toBe('My Clinic');
        expect(ClinicSettingsService::get('clinic_email'))->toBe('info@myclinic.com');
        expect(ClinicSettingsService::get('clinic_phone'))->toBe('1234567890');
        expect(ClinicSettingsService::get('clinic_currency'))->toBe('EUR');
        expect(ClinicSettingsService::get('clinic_opening_hours'))->toBe('Mon-Fri 9-5');
        expect(ClinicSettingsService::get('clinic_default_fee'))->toBe('100.00');
        expect(ClinicSettingsService::get('clinic_tax_rate'))->toBe('10');
        expect(ClinicSettingsService::get('clinic_address'))->toBe('123 Main St');
        expect(ClinicSettingsService::get('clinic_receipt_footer'))->toBe('Thank you!');
    });

    it('validates clinic name is required', function () {
        $this->post(route('settings.clinic.update'), [
            'clinic_name' => '',
        ])->assertSessionHasErrors('clinic_name');
    });

    it('validates clinic email format', function () {
        $this->post(route('settings.clinic.update'), [
            'clinic_name' => 'Test',
            'clinic_email' => 'not-an-email',
        ])->assertSessionHasErrors('clinic_email');
    });
});

describe('SettingController Appointment', function () {
    it('renders appointment settings page', function () {
        $this->get(route('settings.appointment'))
            ->assertOk()
            ->assertSee('Appointment Settings')
            ->assertSee('default_duration');
    });

    it('updates appointment settings', function () {
        $this->post(route('settings.appointment.update'), [
            'default_duration' => '45',
            'min_duration' => '20',
            'max_duration' => '120',
            'advance_booking_days' => '60',
            'cancellation_hours' => '12',
        ])->assertRedirect();

        expect(ClinicSettingsService::get('appointment.default_duration'))->toBe('45');
        expect(ClinicSettingsService::get('appointment.min_duration'))->toBe('20');
        expect(ClinicSettingsService::get('appointment.max_duration'))->toBe('120');
        expect(ClinicSettingsService::get('appointment.advance_booking_days'))->toBe('60');
        expect(ClinicSettingsService::get('appointment.cancellation_hours'))->toBe('12');
    });

    it('validates required fields', function () {
        $this->post(route('settings.appointment.update'), [
            'default_duration' => '',
            'min_duration' => '',
            'max_duration' => '',
        ])->assertSessionHasErrors(['default_duration', 'min_duration', 'max_duration']);
    });

    it('validates min/max ranges', function () {
        $this->post(route('settings.appointment.update'), [
            'default_duration' => '3',
            'min_duration' => '3',
            'max_duration' => '500',
        ])->assertSessionHasErrors(['default_duration', 'min_duration', 'max_duration']);
    });
});

describe('SettingController Queue', function () {
    it('renders queue settings page', function () {
        $this->get(route('settings.queue'))
            ->assertOk()
            ->assertSee('Queue Settings')
            ->assertSee('ticket_prefix');
    });

    it('updates queue settings', function () {
        $this->post(route('settings.queue.update'), [
            'ticket_prefix' => 'Q',
            'ticket_sequence_length' => '4',
        ])->assertRedirect();

        expect(ClinicSettingsService::get('queue.ticket_prefix'))->toBe('Q');
        expect(ClinicSettingsService::get('queue.ticket_sequence_length'))->toBe('4');
    });
});

describe('SettingController Billing', function () {
    it('renders billing settings page', function () {
        $this->get(route('settings.billing'))
            ->assertOk()
            ->assertSee('Billing Settings')
            ->assertSee('prefix');
    });

    it('updates billing settings', function () {
        $this->post(route('settings.billing.update'), [
            'prefix' => 'BILL',
            'sequence_length' => '5',
            'default_tax_rate' => '15',
        ])->assertRedirect();

        expect(ClinicSettingsService::get('invoice.prefix'))->toBe('BILL');
        expect(ClinicSettingsService::get('invoice.sequence_length'))->toBe('5');
        expect(ClinicSettingsService::get('invoice.default_tax_rate'))->toBe('15');
    });
});

describe('SettingController Inventory', function () {
    it('renders inventory settings page', function () {
        $this->get(route('settings.inventory'))
            ->assertOk()
            ->assertSee('Inventory Settings')
            ->assertSee('expiry_warning_days');
    });

    it('updates inventory settings', function () {
        $this->post(route('settings.inventory.update'), [
            'expiry_warning_days' => '60',
        ])->assertRedirect();

        expect(ClinicSettingsService::get('inventory.expiry_warning_days'))->toBe('60');
    });
});

describe('SettingController Prescription', function () {
    it('renders prescription settings page', function () {
        $this->get(route('settings.prescription'))
            ->assertOk()
            ->assertSee('Prescription Settings')
            ->assertSee('prefix');
    });

    it('updates prescription settings', function () {
        $this->post(route('settings.prescription.update'), [
            'prefix' => 'SCRIPT',
            'sequence_length' => '3',
        ])->assertRedirect();

        expect(ClinicSettingsService::get('prescription.prefix'))->toBe('SCRIPT');
        expect(ClinicSettingsService::get('prescription.sequence_length'))->toBe('3');
    });
});

describe('SettingController Website', function () {
    it('renders website settings page', function () {
        $this->get(route('settings.website.edit'))
            ->assertOk()
            ->assertSee('Website Settings')
            ->assertSee('site_name');
    });

    it('updates website settings', function () {
        $this->post(route('settings.website.update'), [
            'site_name' => 'Clinic Pro',
            'email' => 'contact@clinicpro.com',
            'phone' => '555-0100',
            'address' => '456 Oak Ave',
        ])->assertRedirect();

        expect(ClinicSettingsService::get('site.site_name'))->toBe('Clinic Pro');
        expect(ClinicSettingsService::get('site.email'))->toBe('contact@clinicpro.com');
        expect(ClinicSettingsService::get('site.phone'))->toBe('555-0100');
        expect(ClinicSettingsService::get('site.address'))->toBe('456 Oak Ave');
    });

    it('validates site name is required', function () {
        $this->post(route('settings.website.update'), [
            'site_name' => '',
        ])->assertSessionHasErrors('site_name');
    });

    it('validates social URL format', function () {
        $this->post(route('settings.website.update'), [
            'site_name' => 'Test',
            'social' => ['facebook' => 'not-a-url'],
        ])->assertSessionHasErrors('social.facebook');
    });
});

describe('Settings Permissions', function () {
    it('denies access for users without permission', function () {
        $this->user->syncRoles([]);

        $this->get(route('settings.clinic'))->assertForbidden();
        $this->post(route('settings.clinic.update'), ['clinic_name' => 'X'])->assertForbidden();
        $this->get(route('settings.appointment'))->assertForbidden();
        $this->get(route('settings.queue'))->assertForbidden();
        $this->get(route('settings.billing'))->assertForbidden();
        $this->get(route('settings.inventory'))->assertForbidden();
        $this->get(route('settings.prescription'))->assertForbidden();
        $this->get(route('settings.website.edit'))->assertForbidden();
    });
});

describe('Settings Index Page', function () {
    it('renders settings index with all navigation links', function () {
        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Clinic')
            ->assertSee('Website')
            ->assertSee('Appointment')
            ->assertSee('Queue')
            ->assertSee('Billing')
            ->assertSee('Inventory')
            ->assertSee('Prescription');
    });
});
