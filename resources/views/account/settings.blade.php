<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">{{ __('app.settings.title') }}</h4>
            <small class="text-muted">{{ __('app.settings.subtitle') }}</small>
        </div>
    </div>

    @php
        $supportedLocales = (array) config('app.supported_locales', ['en']);
        $timezones = \DateTimeZone::listIdentifiers();
        $theme      = $settings['appearance']['theme'] ?? 'light';
        $density    = $settings['appearance']['table_density'] ?? 'comfortable';
        $sidebar    = $settings['appearance']['sidebar'] ?? 'expanded';
        $language   = $settings['localization']['language'] ?? 'en';
        $timezone   = $settings['localization']['timezone'] ?? 'UTC';
        $dateFmt    = $settings['localization']['date_format'] ?? 'Y-m-d';
        $timeFmt    = $settings['localization']['time_format'] ?? 'H:i';
        $calView    = $settings['preferences']['calendar_view'] ?? 'month';
        $weekStart  = $settings['preferences']['week_starts_on'] ?? 'sunday';
        $showWknd   = (bool) ($settings['preferences']['show_weekends'] ?? true);
    @endphp

    <div class="row g-4" x-data="{ activeSection: 'appearance' }">
        {{-- Section navigation --}}
        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-body p-2">
                    <nav class="nav nav-pills flex-column flex-md-column flex-row overflow-auto gap-1">
                        <a class="nav-link text-start d-flex align-items-center" :class="activeSection === 'profile' && 'active'"
                            href="#" @click.prevent="activeSection = 'profile'">
                            <i class="bi bi-person me-2"></i>{{ __('app.settings.profile') }}
                        </a>
                        <a class="nav-link text-start d-flex align-items-center" :class="activeSection === 'appearance' && 'active'"
                            href="#" @click.prevent="activeSection = 'appearance'">
                            <i class="bi bi-palette me-2"></i>{{ __('app.settings.appearance') }}
                        </a>
                        <a class="nav-link text-start d-flex align-items-center" :class="activeSection === 'localization' && 'active'"
                            href="#" @click.prevent="activeSection = 'localization'">
                            <i class="bi bi-globe2 me-2"></i>{{ __('app.settings.language_region') }}
                        </a>
                        <a class="nav-link text-start d-flex align-items-center" :class="activeSection === 'preferences' && 'active'"
                            href="#" @click.prevent="activeSection = 'preferences'">
                            <i class="bi bi-sliders me-2"></i>{{ __('app.settings.preferences') }}
                        </a>
                        <a class="nav-link text-start d-flex align-items-center" :class="activeSection === 'security' && 'active'"
                            href="#" @click.prevent="activeSection = 'security'">
                            <i class="bi bi-shield-lock me-2"></i>{{ __('app.settings.security') }}
                        </a>
                        <a class="nav-link text-start d-flex align-items-center" :class="activeSection === 'account' && 'active'"
                            href="#" @click.prevent="activeSection = 'account'">
                            <i class="bi bi-person-gear me-2"></i>{{ __('app.settings.account') }}
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        {{-- Section content --}}
        <div class="col-md-8 col-lg-9">
            {{-- Profile form (own form: multipart for avatar) --}}
            <div x-show="activeSection === 'profile'" x-cloak>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('patch')

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-person me-2"></i>{{ __('app.settings.profile') }}</h6>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('status') === 'profile-updated')
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-1"></i> {{ __('app.settings.profile_updated') }}
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">{{ __('app.settings.profile_photo') }}</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
                                        alt="{{ $user->name }}" class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                        style="width:80px;height:80px;object-fit:cover">
                                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp"
                                        class="form-control @error('avatar') is-invalid @enderror" style="max-width:320px">
                                </div>
                                @error('avatar')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="profile_name" class="form-label">{{ __('app.settings.name') }}</label>
                                <input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}"
                                    class="form-control @error('name') is-invalid @enderror" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="profile_email" class="form-label">{{ __('app.settings.email') }}</label>
                                <input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}"
                                    class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="profile_phone" class="form-label">{{ __('app.settings.phone') }}</label>
                                <input id="profile_phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                    class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2 me-1"></i> {{ __('app.settings.save_changes') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <form method="POST" action="{{ route('user.settings.store') }}" autocomplete="off">
                @csrf

                {{-- Appearance --}}
                <div class="card shadow-sm border-0" x-show="activeSection === 'appearance'" x-cloak>
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-palette me-2"></i>{{ __('app.settings.appearance') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="theme" class="form-label">{{ __('app.settings.theme') }}</label>
                            <select id="theme" name="appearance[theme]" class="form-select">
                                <option value="light" @selected($theme === 'light')>{{ __('app.settings.theme_light') }}</option>
                                <option value="dark" @selected($theme === 'dark')>{{ __('app.settings.theme_dark') }}</option>
                                <option value="system" @selected($theme === 'system')>{{ __('app.settings.theme_system') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="table_density" class="form-label">{{ __('app.settings.table_density') }}</label>
                            <select id="table_density" name="appearance[table_density]" class="form-select">
                                @foreach ($densityOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($density === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sidebar" class="form-label">{{ __('app.settings.sidebar') }}</label>
                            <select id="sidebar" name="appearance[sidebar]" class="form-select">
                                <option value="expanded" @selected($sidebar === 'expanded')>{{ __('app.settings.sidebar_expanded') }}</option>
                                <option value="collapsed" @selected($sidebar === 'collapsed')>{{ __('app.settings.sidebar_collapsed') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Language & Region --}}
                <div class="card shadow-sm border-0" x-show="activeSection === 'localization'" x-cloak>
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-globe2 me-2"></i>{{ __('app.settings.language_region') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="language" class="form-label">{{ __('app.settings.language') }}</label>
                            <select id="language" name="localization[language]" class="form-select">
                                @if (in_array('en', $supportedLocales, true))
                                    <option value="en" @selected($language === 'en')>{{ __('app.settings.language_english') }}</option>
                                @endif
                                @if (in_array('my', $supportedLocales, true))
                                    <option value="my" @selected($language === 'my')>{{ __('app.settings.language_myanmar') }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="timezone" class="form-label">{{ __('app.settings.timezone') }}</label>
                            <select id="timezone" name="localization[timezone]" class="form-select">
                                @foreach ($timezones as $tz)
                                    <option value="{{ $tz }}" @selected($timezone === $tz)>{{ str_replace('_', ' ', $tz) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="date_format" class="form-label">{{ __('app.settings.date_format') }}</label>
                                <select id="date_format" name="localization[date_format]" class="form-select">
                                    @foreach (['Y-m-d' => __('app.settings.date_ymd'), 'd/m/Y' => __('app.settings.date_dmy'), 'm/d/Y' => __('app.settings.date_mdy'), 'M d, Y' => __('app.settings.date_mdy_long'), 'd M Y' => __('app.settings.date_dmy_long')] as $value => $label)
                                        <option value="{{ $value }}" @selected($dateFmt === $value)>{{ $label }} ({{ $value }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="time_format" class="form-label">{{ __('app.settings.time_format') }}</label>
                                <select id="time_format" name="localization[time_format]" class="form-select">
                                    @foreach (['H:i' => __('app.settings.time_24h'), 'h:i A' => __('app.settings.time_12h'), 'g:i A' => __('app.settings.time_12h_2')] as $value => $label)
                                        <option value="{{ $value }}" @selected($timeFmt === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Preferences --}}
                <div class="card shadow-sm border-0" x-show="activeSection === 'preferences'" x-cloak>
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-sliders me-2"></i>{{ __('app.settings.preferences') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="calendar_view" class="form-label">{{ __('app.settings.calendar_view') }}</label>
                            <select id="calendar_view" name="preferences[calendar_view]" class="form-select">
                                <option value="month" @selected($calView === 'month')>{{ __('app.settings.calendar_month') }}</option>
                                <option value="week" @selected($calView === 'week')>{{ __('app.settings.calendar_week') }}</option>
                                <option value="list" @selected($calView === 'list')>{{ __('app.settings.calendar_list') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="week_starts_on" class="form-label">{{ __('app.settings.week_starts_on') }}</label>
                            <select id="week_starts_on" name="preferences[week_starts_on]" class="form-select">
                                <option value="sunday" @selected($weekStart === 'sunday')>{{ __('app.settings.week_sunday') }}</option>
                                <option value="monday" @selected($weekStart === 'monday')>{{ __('app.settings.week_monday') }}</option>
                                <option value="saturday" @selected($weekStart === 'saturday')>{{ __('app.settings.week_saturday') }}</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="preferences[show_weekends]" value="0">
                            <input id="show_weekends" type="checkbox" class="form-check-input"
                                name="preferences[show_weekends]" value="1" @checked($showWknd)>
                            <label for="show_weekends" class="form-check-label">{{ __('app.settings.show_weekends') }}</label>
                        </div>
                    </div>
                </div>

                <div class="mt-3"
                    x-show="['appearance', 'localization', 'preferences'].includes(activeSection)"
                    x-cloak>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2 me-1"></i> {{ __('app.settings.save_settings') }}
                    </button>
                </div>
            </form>

            {{-- Security (own form: change password) --}}
            <div x-show="activeSection === 'security'" x-cloak>
                <form method="POST" action="{{ route('password.update') }}" autocomplete="off">
                    @csrf
                    @method('put')

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>{{ __('app.settings.security') }}</h6>
                        </div>
                        <div class="card-body">
                            @if ($errors->updatePassword->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->updatePassword->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('status') === 'password-updated')
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-1"></i> {{ __('app.security.password_updated') }}
                                </div>
                            @endif

                            <h6 class="fw-semibold mb-1">{{ __('app.security.change_password') }}</h6>
                            <p class="text-muted mb-3">
                                {{ __('app.security.password_help') }}
                            </p>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">{{ __('app.security.current_password') }}</label>
                                <input id="current_password" type="password" name="current_password"
                                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                    autocomplete="current-password">
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label">{{ __('app.security.new_password') }}</label>
                                    <input id="new_password" type="password" name="password"
                                        class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                        autocomplete="new-password">
                                    @error('password', 'updatePassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">{{ __('app.security.confirm_password') }}</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation"
                                        class="form-control" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key me-1"></i> {{ __('app.security.update_password') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- Future security features (prepared architecture, not yet implemented) --}}
                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>{{ __('app.security.advanced_security') }}</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">{{ __('app.security.advanced_security_help') }}</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <div class="fw-semibold">{{ __('app.security.two_factor') }}</div>
                                    <small class="text-muted">{{ __('app.security.two_factor_help') }}</small>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary">{{ __('app.security.coming_soon') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <div class="fw-semibold">{{ __('app.security.active_sessions') }}</div>
                                    <small class="text-muted">{{ __('app.security.active_sessions_help') }}</small>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary">{{ __('app.security.coming_soon') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <div class="fw-semibold">{{ __('app.security.login_history') }}</div>
                                    <small class="text-muted">{{ __('app.security.login_history_help') }}</small>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary">{{ __('app.security.coming_soon') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Account --}}
            <div x-show="activeSection === 'account'" x-cloak>
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person-gear me-2"></i>{{ __('app.settings.account') }}</h6>
                    </div>
                    <div class="card-body">
                        @if ($errors->userDeletion->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->userDeletion->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($canDeleteAccount)
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i> {{ __('app.account.warning') }}
                            </div>

                            <h6 class="fw-semibold mb-2">{{ __('app.account.consequences_title') }}</h6>
                            <ul class="text-muted mb-3">
                                <li>{{ __('app.account.consequence_access') }}</li>
                                <li>{{ __('app.account.consequence_records') }}</li>
                                @if ($user->doctor)
                                    <li>{{ __('app.account.consequence_doctor') }}</li>
                                @endif
                            </ul>

                            <form method="POST" action="{{ route('profile.destroy') }}" autocomplete="off">
                                @csrf
                                @method('delete')

                                <div class="mb-3">
                                    <label for="delete_password" class="form-label">{{ __('app.account.current_password') }}</label>
                                    <input id="delete_password" type="password" name="password"
                                        class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                                        autocomplete="current-password">
                                    @error('password', 'userDeletion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_email" class="form-label">{{ __('app.account.confirm_email') }}</label>
                                    <input id="confirm_email" type="email" name="confirm_email"
                                        value="{{ old('confirm_email') }}"
                                        class="form-control @error('confirm_email', 'userDeletion') is-invalid @enderror"
                                        autocomplete="off">
                                    <div class="form-text">{{ __('app.account.confirm_email_help') }}</div>
                                    @error('confirm_email', 'userDeletion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-person-x me-1"></i> {{ __('app.account.submit') }}
                                </button>
                            </form>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-shield-lock me-1"></i> {{ __('app.account.delete_prevented_last_admin') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</x-auth-layout>