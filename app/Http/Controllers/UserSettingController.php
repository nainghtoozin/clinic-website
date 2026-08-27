<?php

namespace App\Http\Controllers;

use App\Services\UserSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserSettingController extends Controller
{
    public function __construct(
        protected UserSettingsService $settings
    ) {
    }

    public function index(Request $request): View
    {
        return view('account.settings', [
            'activeSection' => 'overview',
            'settings' => $this->settings->all($request->user()),
            'densityOptions' => $this->densityOptions(),
            'user' => $request->user(),
            'canDeleteAccount' => ! $request->user()->wouldRemoveLastAdministrator(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate($this->validationRules());

        $this->settings->save($user, 'appearance', [
            'theme' => $validated['appearance']['theme'],
            'table_density' => $validated['appearance']['table_density'],
            'sidebar' => $validated['appearance']['sidebar'],
        ]);

        $this->settings->save($user, 'localization', [
            'language' => $validated['localization']['language'],
            'timezone' => $validated['localization']['timezone'],
            'date_format' => $validated['localization']['date_format'],
            'time_format' => $validated['localization']['time_format'],
        ]);

        $this->settings->save($user, 'preferences', [
            'calendar_view' => $validated['preferences']['calendar_view'],
            'week_starts_on' => $validated['preferences']['week_starts_on'],
            'show_weekends' => $validated['preferences']['show_weekends'],
        ]);

        if (isset($validated['notifications'])) {
            $this->settings->save($user, 'notifications', [
                'appointment_notifications' => $validated['notifications']['appointment_notifications'] ?? true,
                'queue_notifications' => $validated['notifications']['queue_notifications'] ?? true,
                'consultation_notifications' => $validated['notifications']['consultation_notifications'] ?? true,
                'prescription_notifications' => $validated['notifications']['prescription_notifications'] ?? true,
                'investigation_notifications' => $validated['notifications']['investigation_notifications'] ?? true,
                'inventory_notifications' => $validated['notifications']['inventory_notifications'] ?? true,
                'expiry_notifications' => $validated['notifications']['expiry_notifications'] ?? true,
                'invoice_notifications' => $validated['notifications']['invoice_notifications'] ?? true,
                'payment_notifications' => $validated['notifications']['payment_notifications'] ?? true,
                'expense_notifications' => $validated['notifications']['expense_notifications'] ?? true,
                'communication_notifications' => $validated['notifications']['communication_notifications'] ?? true,
                'backup_notifications' => $validated['notifications']['backup_notifications'] ?? true,
                'system_notifications' => $validated['notifications']['system_notifications'] ?? true,
            ]);
        }

        return back()->with('success', __('app.settings.saved'));
    }

    protected function validationRules(): array
    {
        return [
            'appearance.theme' => ['required', 'in:light,dark,system'],
            'appearance.table_density' => ['required', 'in:compact,comfortable'],
            'appearance.sidebar' => ['required', 'in:expanded,collapsed'],

            'localization.language' => ['required', Rule::in(config('app.supported_locales', ['en']))],
            'localization.timezone' => ['required', 'timezone'],
            'localization.date_format' => ['required', Rule::in(['Y-m-d', 'd/m/Y', 'm/d/Y', 'M d, Y', 'd M Y'])],
            'localization.time_format' => ['required', Rule::in(['H:i', 'h:i A', 'g:i A'])],

            'preferences.calendar_view' => ['required', Rule::in(['month', 'week', 'list'])],
            'preferences.week_starts_on' => ['required', Rule::in(['sunday', 'monday', 'saturday'])],
            'preferences.show_weekends' => ['required', 'boolean'],

            'notifications.appointment_notifications' => ['sometimes', 'boolean'],
            'notifications.queue_notifications' => ['sometimes', 'boolean'],
            'notifications.consultation_notifications' => ['sometimes', 'boolean'],
            'notifications.prescription_notifications' => ['sometimes', 'boolean'],
            'notifications.investigation_notifications' => ['sometimes', 'boolean'],
            'notifications.inventory_notifications' => ['sometimes', 'boolean'],
            'notifications.expiry_notifications' => ['sometimes', 'boolean'],
            'notifications.invoice_notifications' => ['sometimes', 'boolean'],
            'notifications.payment_notifications' => ['sometimes', 'boolean'],
            'notifications.expense_notifications' => ['sometimes', 'boolean'],
            'notifications.communication_notifications' => ['sometimes', 'boolean'],
            'notifications.backup_notifications' => ['sometimes', 'boolean'],
            'notifications.system_notifications' => ['sometimes', 'boolean'],
        ];
    }

    protected function densityOptions(): array
    {
        return [
            'comfortable' => __('app.settings.density_comfortable'),
            'compact' => __('app.settings.density_compact'),
        ];
    }
}