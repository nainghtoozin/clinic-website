<x-auth-layout>


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
</x-auth-layout>
