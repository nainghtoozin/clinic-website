<!DOCTYPE html>
<html lang="{{ $userLanguage ?? config('app.locale', 'en') }}" data-bs-theme="{{ ($userTheme ?? 'light') === 'dark' ? 'dark' : 'light' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

    <style>
        body {
            background: #f5f7fb;
            font-size: 0.9rem;
        }

        [data-bs-theme="dark"] body {
            background: #1b1e23;
        }
        [data-bs-theme="dark"] .bg-white {
            background-color: var(--bs-tertiary-bg) !important;
        }
        [data-bs-theme="dark"] .topbar {
            background: var(--bs-body-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            height: 100dvh;
            background: linear-gradient(180deg, #0d6efd 0%, #0b5ed7 100%);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s ease;
        }

        .sidebar .sidebar-brand {
            position: sticky;
            top: 0;
            z-index: 1;
            background: linear-gradient(180deg, #0d6efd 0%, #0b5ed7 100%);
            padding: 1rem 1.25rem;
            font-weight: 700;
            color: #fff;
            font-size: 1.1rem;
            letter-spacing: -0.3px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .sidebar .nav-section {
            padding: 0.75rem 1.25rem 0.25rem;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
        }

        .sidebar a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 0.5rem 1.25rem;
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            border-radius: 0;
            transition: all 0.15s;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .sidebar a.active {
            background: rgba(255,255,255,0.18);
            color: #fff;
            font-weight: 500;
        }

        .sidebar a i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 0.6rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #333;
            cursor: pointer;
            padding: 0.25rem;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar-toggle {
                display: inline-block;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.4);
                z-index: 1035;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }

        .stat-card {
            border-radius: 10px;
            transition: transform 0.15s;
        }
        .stat-card:hover {
            transform: translateY(-1px);
        }

        .page-header {
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 1.25rem;
        }

        /* Collapsed sidebar preference */
        body.sidebar-collapsed .sidebar {
            width: 64px;
        }
        body.sidebar-collapsed .sidebar .sidebar-brand {
            padding: 1rem 0.5rem;
            justify-content: center;
            font-size: 0;
        }
        body.sidebar-collapsed .sidebar .sidebar-brand i {
            font-size: 1.25rem;
        }
        body.sidebar-collapsed .sidebar .nav-section {
            text-align: center;
            padding: 0.75rem 0 0.25rem;
            font-size: 0;
        }
        body.sidebar-collapsed .sidebar .nav-section::first-letter {
            font-size: 0.65rem;
        }
        body.sidebar-collapsed .sidebar a {
            padding: 0.6rem 0;
            justify-content: center;
        }
        body.sidebar-collapsed .sidebar a i {
            margin-right: 0;
        }
        body.sidebar-collapsed .sidebar a {
            font-size: 0;
        }
        body.sidebar-collapsed .sidebar a i {
            font-size: 1.05rem;
        }
        body.sidebar-collapsed .main-content {
            margin-left: 64px;
        }

        /* Table density preference */
        body.density-compact .table td,
        body.density-compact .table th {
            padding: 0.3rem 0.4rem;
            font-size: 0.85rem;
        }
    </style>
</head>

<body class="{{ ($userSidebar ?? 'expanded') === 'collapsed' ? 'sidebar-collapsed ' : '' }}{{ ($userTableDensity ?? 'comfortable') === 'compact' ? 'density-compact' : 'density-comfortable' }}">

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-hospital me-2"></i>{{ config('app.name', __('app.app_name')) }}
        </div>

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
        <a href="{{ route('user.settings') }}" class="{{ request()->routeIs('user.settings*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> {{ __('app.nav.settings') }}
        </a>
        @can('settings.view')
            <a href="{{ route('settings.clinic') }}" class="{{ request()->routeIs('settings.clinic*', 'settings.website*') ? 'active' : '' }}">
                <i class="bi bi-buildings"></i> {{ __('app.nav.clinic_settings') }}
            </a>
        @endcan

        <div class="nav-section">{{ __('app.nav.section_account') }}</div>
        <a href="{{ route('user.settings') }}" class="{{ request()->routeIs('user.settings*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> {{ __('app.nav.account_settings') }}
        </a>

        <div style="height: 2rem;"></div>
    </aside>

    <div class="main-content">
        <div class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <span class="text-muted small d-none d-md-inline">{{ \Illuminate\Support\Carbon::now($userTimezone ?? config('app.timezone', 'UTC'))->format(($userDateFormat ?? 'M d, Y')) }}</span>
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:30px;height:30px;font-size:0.8rem;">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="fw-medium d-none d-sm-inline">{{ Auth::user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <span class="dropdown-item-text small text-muted">
                            {{ Auth::user()->email }}
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person me-2"></i> {{ __('app.topbar.profile') }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> {{ __('app.topbar.log_out') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="p-3 p-md-4">
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
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.querySelector('.sidebar-overlay').classList.toggle('show');
        }
    </script>
</body>

</html>
