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
                <h4 class="mb-0">Dashboard</h4>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">Welcome, Admin</span>
                    <img src="https://i.pravatar.cc/40" class="rounded-circle">
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Doctors</h6>
                            <h3 class="counter" data-count="24">0</h3>
                            <i class="bi bi-person-badge fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Appointments</h6>
                            <h3 class="counter" data-count="128">0</h3>
                            <i class="bi bi-calendar-check fs-3 text-success"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Patients</h6>
                            <h3 class="counter" data-count="312">0</h3>
                            <i class="bi bi-people fs-3 text-warning"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Reviews</h6>
                            <h3 class="counter" data-count="89">0</h3>
                            <i class="bi bi-star-fill fs-3 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments & Doctors -->
            <div class="row g-4">

                <!-- Appointments -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <strong>Upcoming Appointments</strong>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Smith</td>
                                        <td>Dr. Amelia Brooks</td>
                                        <td>Jan 20, 10:00 AM</td>
                                        <td><span class="badge bg-success">Confirmed</span></td>
                                    </tr>
                                    <tr>
                                        <td>Emma Watson</td>
                                        <td>Dr. Noah Turner</td>
                                        <td>Jan 21, 01:30 PM</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                    </tr>
                                    <tr>
                                        <td>Michael Lee</td>
                                        <td>Dr. Sofia Bennett</td>
                                        <td>Jan 22, 09:00 AM</td>
                                        <td><span class="badge bg-danger">Cancelled</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Doctors Overview -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <strong>Doctors On Duty</strong>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://i.pravatar.cc/40?img=1" class="rounded-circle me-2">
                                <div>
                                    <strong>Dr. Amelia Brooks</strong><br>
                                    <small class="text-muted">Cardiology</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <img src="https://i.pravatar.cc/40?img=2" class="rounded-circle me-2">
                                <div>
                                    <strong>Dr. Noah Turner</strong><br>
                                    <small class="text-muted">Pediatrics</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <img src="https://i.pravatar.cc/40?img=3" class="rounded-circle me-2">
                                <div>
                                    <strong>Dr. Sofia Bennett</strong><br>
                                    <small class="text-muted">Dermatology</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

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
