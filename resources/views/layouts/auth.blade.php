<!DOCTYPE html>
<html lang="{{ $userLanguage ?? config('app.locale', 'en') }}" data-bs-theme="{{ ($userTheme ?? 'light') === 'dark' ? 'dark' : 'light' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.admin_panel') }} {{ config('app.name', __('app.app_name')) }}</title>

    <script>
        (function () {
            var theme = @js($userTheme ?? 'light');
            var storedTheme = theme;
            if (theme === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            var el = document.documentElement;
            el.setAttribute('data-bs-theme', theme);
            if (storedTheme === 'system') {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
                    el.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
                });
            }
        })();
    </script>


    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="{{ ($userSidebar ?? 'expanded') === 'collapsed' ? 'sidebar-collapsed ' : '' }}{{ ($userTableDensity ?? 'comfortable') === 'compact' ? 'density-compact' : 'density-comfortable' }}">

    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <span class="brand-mark"><i class="bi bi-hospital"></i></span>
            <span class="brand-text">
                <span class="brand-name">{{ setting('site.site_name') ?: config('app.name') }}</span>
                <span class="brand-sub">{{ __('app.admin_panel') }}</span>
            </span>
        </a>

        @can('dashboard.view')
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> {{ __('app.nav.dashboard') }}
            </a>
        @endcan

        <div class="nav-section">{{ __('app.nav.section_patients') }}</div>
        @can('patient.view')
            <a href="{{ route('patients.index') }}" class="{{ request()->routeIs('patients.*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i> {{ __('app.nav.patients') }}
            </a>
        @endcan
        @can('appointment.view')
            <a href="{{ route('appointments.index') }}" class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> {{ __('app.nav.appointments') }}
            </a>
        @endcan
        @can('queue.view')
            <a href="{{ route('queue.index') }}" class="{{ request()->routeIs('queue.*') ? 'active' : '' }}">
                <i class="bi bi-list-ol"></i> {{ __('app.nav.queue') }}
            </a>
        @endcan

        <div class="nav-section">{{ __('app.nav.section_clinical') }}</div>
        @can('consultation.view')
            <a href="{{ route('consultations.index') }}" class="{{ request()->routeIs('consultations.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard2-pulse"></i> {{ __('app.nav.consultations') }}
            </a>
        @endcan
        @can('prescription.view')
            <a href="{{ route('prescriptions.index') }}" class="{{ request()->routeIs('prescriptions.*') ? 'active' : '' }}">
                <i class="bi bi-file-medical"></i> {{ __('app.nav.prescriptions') }}
            </a>
        @endcan
        @can('lab_test.view')
            <a href="{{ route('lab-tests.index') }}" class="{{ request()->routeIs('lab-tests.*') ? 'active' : '' }}">
                <i class="bi bi-eyedropper"></i> Lab Tests
            </a>
        @endcan
        @can('investigation.view')
            <a href="{{ route('investigations.index') }}" class="{{ request()->routeIs('investigations.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard2-data"></i> Investigations
            </a>
        @endcan
        @can('communication.view')
            <a href="{{ route('communications.index') }}" class="{{ request()->routeIs('communications.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i> Communications
            </a>
        @endcan

        <div class="nav-section">{{ __('app.nav.section_inventory') }}</div>
        @can('medicine.view')
            <a href="{{ route('medicines.index') }}" class="{{ request()->routeIs('medicines.*') ? 'active' : '' }}">
                <i class="bi bi-capsule"></i> {{ __('app.nav.medicines') }}
            </a>
        @endcan
        @can('inventory.view')
            <a href="{{ route('inventory.dashboard') }}" class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> {{ __('app.nav.stock_management') }}
            </a>
        @endcan

        <div class="nav-section">{{ __('app.nav.section_billing') }}</div>
        @can('invoice.view')
            <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> {{ __('app.nav.invoices') }}
            </a>
        @endcan
        @can('payment.view')
            <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> {{ __('app.nav.payments') }}
            </a>
        @endcan
        @can('expense.view')
            <a href="{{ route('expenses.index') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Expenses
            </a>
        @endcan

        <div class="nav-section">{{ __('app.nav.section_management') }}</div>
        @can('doctor.view')
            <a href="{{ route('doctors.index') }}" class="{{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> {{ __('app.nav.doctors') }}
            </a>
        @endcan
        @can('staff.view')
            <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> {{ __('app.nav.staff') }}
            </a>
        @endcan
        @can('role.view')
            <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i> {{ __('app.nav.roles_permissions') }}
            </a>
        @endcan
        @can('dashboard.view')
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> {{ __('app.nav.reports') }}
            </a>
        @endcan
        @can('dashboard.view')
            <a href="{{ route('analytics.index') }}" class="{{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Analytics
            </a>
        @endcan
        <a href="{{ route('user.settings') }}" class="{{ request()->routeIs('user.settings*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> {{ __('app.nav.settings') }}
        </a>
        @can('settings.view')
            <a href="{{ route('settings.clinic') }}" class="{{ request()->routeIs('settings.clinic*', 'settings.website*', 'settings.appointment*', 'settings.queue*', 'settings.billing*', 'settings.inventory*', 'settings.prescription*') ? 'active' : '' }}">
                <i class="bi bi-buildings"></i> {{ __('app.nav.clinic_settings') }}
            </a>
        @endcan
        @can('backup.view')
            <a href="{{ route('backups.index') }}" class="{{ request()->routeIs('backups.*') ? 'active' : '' }}">
                <i class="bi bi-database"></i> Backup & Restore
            </a>
        @endcan
        @can('audit.view')
            <a href="{{ route('audit-logs.index') }}" class="{{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Audit Logs
            </a>
        @endcan

        <div class="nav-section">{{ __('app.nav.section_account') }}</div>
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i> {{ __('app.topbar.profile') }}
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link">
                <i class="bi bi-box-arrow-right"></i> <span>{{ __('app.topbar.log_out') }}</span>
            </button>
        </form>

        <div style="height: 2rem; flex-shrink: 0;"></div>
    </aside>

    <div class="main-content">
        <div class="topbar d-flex justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
                <span class="text-muted small d-none d-md-inline">
                    <i class="bi bi-calendar3 me-1"></i>
                    {{ \Illuminate\Support\Carbon::now($userTimezone ?? config('app.timezone', 'UTC'))->format(($userDateFormat ?? 'M d, Y')) }}
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                @auth
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-1 border-0 shadow-none py-1 px-2 position-relative notification-trigger"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationDropdown">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-unread-badge"
                              style="display: {{ \App\Services\NotificationService::unreadCount(auth()->user()) > 0 ? 'inline' : 'none' }}">
                            {{ \App\Services\NotificationService::unreadCount(auth()->user()) }}
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow border-0 notification-dropdown" style="width: 360px; max-height: 420px; overflow-y: auto;">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <h6 class="mb-0 fw-semibold">Notifications</h6>
                            <button class="btn btn-sm btn-link text-decoration-none p-0 fw-medium" onclick="markAllReadDropdown()">Mark all read</button>
                        </div>
                        <div id="notification-dropdown-list">
                            @if($recentNotifications->isEmpty())
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-bell-slash d-block mb-2" style="font-size: 1.5rem;"></i>
                                    <small>No notifications yet</small>
                                </div>
                            @else
                                @foreach($recentNotifications as $notif)
                                    <a href="{{ route('notifications.show', $notif) }}"
                                       class="dropdown-item px-3 py-2 {{ $notif->is_read ? '' : 'notification-unread' }}"
                                       data-notif-id="{{ $notif->id }}">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="badge bg-{{ $notif->is_read ? 'secondary' : 'primary' }} rounded-circle p-1 mt-1 flex-shrink-0">
                                                <i class="bi {{ $notif->icon }}" style="font-size: 0.7rem;"></i>
                                            </span>
                                            <div class="flex-grow-1 min-width-0">
                                                <div class="d-flex justify-content-between align-items-baseline">
                                                    <span class="fw-medium small text-truncate" style="max-width: 210px;">{{ $notif->title }}</span>
                                                    <small class="text-muted text-nowrap ms-2 flex-shrink-0">{{ $notif->time_ago }}</small>
                                                </div>
                                                <small class="text-muted d-block text-truncate mt-0" style="max-width: 260px;">{{ $notif->message }}</small>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                        <div class="border-top px-3 py-2 text-center">
                            <a href="{{ route('notifications.index') }}" class="small text-decoration-none fw-medium">
                                View All Notifications <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endauth

                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2 border-0 shadow-none py-1 px-2 user-trigger" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        @if (Auth::user()->avatar)
                            <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="" class="avatar avatar-sm">
                        @else
                            <span class="avatar avatar-sm bg-primary">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        @endif
                        <span class="fw-medium d-none d-sm-inline">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1">
                        <li>
                            <div class="px-3 py-2">
                                <div class="fw-semibold text-truncate" style="max-width:220px;">{{ Auth::user()->name }}</div>
                                <div class="small text-muted text-truncate" style="max-width:220px;">{{ Auth::user()->email }}</div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person"></i> {{ __('app.topbar.profile') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('user.settings') }}">
                                <i class="bi bi-gear"></i> {{ __('app.nav.settings') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i> {{ __('app.topbar.log_out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <main class="page-container">
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
                    class="position-fixed top-0 end-0 p-3" style="z-index: 1060">
                    <div class="toast show align-items-center text-bg-success border-0 shadow">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="show = false"></button>
                        </div>
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
                    class="position-fixed top-0 end-0 p-3" style="z-index: 1060">
                    <div class="toast show align-items-center text-bg-danger border-0 shadow">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="show = false"></button>
                        </div>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="text-center text-muted small py-3">
            {{ setting('site.site_name') ?: config('app.name') }} &middot; {{ __('app.admin_panel') }}
        </footer>
    </div>

    @stack('scripts')

    <script>
        function getCsrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        }

        function markAllReadDropdown() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            })
            .then(function (response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(function (data) {
                document.querySelectorAll('.notification-unread-badge').forEach(function (badge) {
                    badge.textContent = '0';
                    badge.style.display = 'none';
                });
                document.querySelectorAll('#notification-dropdown-list .notification-unread').forEach(function (item) {
                    item.classList.remove('notification-unread');
                });
            })
            .catch(function () {});
        }
    </script>
</body>

</html>