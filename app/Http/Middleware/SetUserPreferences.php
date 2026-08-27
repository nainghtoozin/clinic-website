<?php

namespace App\Http\Middleware;

use App\Services\UserSettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetUserPreferences
{
    public function __construct(
        protected UserSettingsService $settings
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $language = config('app.locale', 'en');
        $theme = 'light';
        $timezone = (string) config('app.timezone', 'UTC');
        $dateFormat = 'M d, Y';
        $timeFormat = 'h:i A';
        $sidebar = 'expanded';
        $tableDensity = 'comfortable';

        if ($user !== null) {
            $language = (string) $this->settings->get($user, 'localization', 'language', $language);
            $theme = (string) $this->settings->get($user, 'appearance', 'theme', $theme);
            $timezone = (string) $this->settings->get($user, 'localization', 'timezone', $timezone);
            $dateFormat = (string) $this->settings->get($user, 'localization', 'date_format', $dateFormat);
            $timeFormat = (string) $this->settings->get($user, 'localization', 'time_format', $timeFormat);
            $sidebar = (string) $this->settings->get($user, 'appearance', 'sidebar', $sidebar);
            $tableDensity = (string) $this->settings->get($user, 'appearance', 'table_density', $tableDensity);
        }

        if (in_array($language, (array) config('app.supported_locales', ['en']), true)) {
            App::setLocale($language);
        }

        Carbon::setLocale(App::getLocale());

        View::share([
            'userTheme' => $theme,
            'userLanguage' => App::getLocale(),
            'userTimezone' => $timezone,
            'userDateFormat' => $dateFormat,
            'userTimeFormat' => $timeFormat,
            'userSidebar' => $sidebar,
            'userTableDensity' => $tableDensity,
            'recentNotifications' => $user
                ? \App\Models\Notification::where('user_id', $user->id)->latest()->limit(8)->get()
                : collect(),
        ]);

        return $next($request);
    }
}