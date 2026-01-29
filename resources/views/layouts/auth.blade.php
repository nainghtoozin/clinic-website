<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Clinic Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #0d6efd;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.15);
        }

        .stat-card {
            border-radius: 12px;
        }
    </style>
</head>

<body>

    <div class="d-flex">

        <!-- Sidebar -->
        <aside class="sidebar">
            <h5 class="text-white text-center py-4 mb-0">Clinic Admin</h5>
            <a href="{{ route('dashboard') }}" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            @can('doctors.view')
                <a href="{{ route('doctors.index') }}"><i class="bi bi-person-badge me-2"></i> Doctors</a>
            @endcan
            @can('user.view')
                <a href="{{ route('users.index') }}"><i class="bi bi-person-badge me-2"></i> Users</a>
            @endcan
            @can('role.view')
                <a href="{{ route('roles.index') }}"><i class="bi bi-person-badge me-2"></i> Roles</a>
            @endcan
            @can('department.view')
                <a href="{{ route('departments.index') }}"><i class="bi bi-person-badge me-2"></i> Departments</a>
            @endcan
            @can('location.view')
                <a href="{{ route('locations.index') }}"><i class="bi bi-person-badge me-2"></i> Locations</a>
            @endcan
            @can('service.view')
                <a href="{{ route('services.index') }}"><i class="bi bi-person-badge me-2"></i> Services</a>
            @endcan
            @can('appointment.view')
                <a href="{{ route('appointments.index') }}"><i class="bi bi-calendar-check me-2"></i> Appointments</a>
            @endcan
            <a href="#"><i class="bi bi-people me-2"></i> Patients</a>
            <a href="#"><i class="bi bi-chat-left-text me-2"></i> Reviews</a>
            <a href="{{ route('settings.website.edit') }}"><i class="bi bi-gear me-2"></i> Settings</a>
        </aside>

        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
                class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
                <div class="toast show align-items-center text-bg-success border-0 shadow">
                    <div class="d-flex">
                        <div class="toast-body">
                            ✅ {{ session('success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="show = false">
                        </button>
                    </div>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition
                class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
                <div class="toast show align-items-center text-bg-danger border-0 shadow">
                    <div class="d-flex">
                        <div class="toast-body">
                            ❌ {{ session('error') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="show = false">
                        </button>
                    </div>
                </div>
            </div>
        @endif


        <!-- Main Content -->
        <main class="flex-grow-1 p-4">
            <!-- Top Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"></h4>
                <div class="dropdown ms-3">
                    <button class="btn btn-white dropdown-toggle d-flex align-items-center gap-2" type="button"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="fw-medium text-muted">
                            {{ Auth::user()->name }}
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">

                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                👤 Profile
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    🚪 Log Out
                                </button>
                            </form>
                        </li>

                    </ul>
                </div>

            </div>


            {{ $slot }}

        </main>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        document.querySelectorAll('.counter').forEach(counter => {
            let target = +counter.dataset.count;
            let count = 0;
            let step = target / 40;

            let interval = setInterval(() => {
                count += step;
                if (count >= target) {
                    counter.innerText = target;
                    clearInterval(interval);
                } else {
                    counter.innerText = Math.floor(count);
                }
            }, 30);
        });
    </script>

</body>

</html>
