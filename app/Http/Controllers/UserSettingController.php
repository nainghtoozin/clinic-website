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