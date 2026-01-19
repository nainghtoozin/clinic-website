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
            <a href="#" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="#"><i class="bi bi-person-badge me-2"></i> Doctors</a>
            <a href="#"><i class="bi bi-calendar-check me-2"></i> Appointments</a>
            <a href="#"><i class="bi bi-people me-2"></i> Patients</a>
            <a href="#"><i class="bi bi-chat-left-text me-2"></i> Reviews</a>
            <a href="#"><i class="bi bi-gear me-2"></i> Settings</a>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow-1 p-4">
            <!-- Top Bar -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"></h4>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">Welcome, Admin</span>
                    <img src="https://i.pravatar.cc/40" class="rounded-circle">
                </div>
            </div>
            {{ $slot }}

        </main>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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
