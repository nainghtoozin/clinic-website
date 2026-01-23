<x-auth-layout>


    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Doctors</h6>
                    <h3>{{ $doctorCount }}</h3>
                    <i class="bi bi-person-badge fs-3 text-primary"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Appointments</h6>
                    <h3>{{ $appointmentCount }}</h3>
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

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Pending</h6>
                    <h3>{{ $pending }}</h3>
                    <i class="bi bi-clock fs-3 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Cancelled</h6>
                    <h3>{{ $cancelled }}</h3>
                    <i class="bi bi-x-circle fs-3 text-danger"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Completed</h6>
                    <h3>{{ $completed }}</h3>
                    <i class="bi bi-check-circle fs-3 text-success"></i>
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
                                <th>Department</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tbody>
                            @forelse($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->name }}</td>
                                    <td>{{ $appointment->doctor->name }}</td>
                                    <td>{{ $appointment->department->name }}</td>
                                    <td>{{ $appointment->date }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $appointment->status === 'approved'
                                                ? 'success'
                                                : ($appointment->status === 'cancelled'
                                                    ? 'danger'
                                                    : 'warning') }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        No appointments found
                                    </td>
                                </tr>
                            @endforelse
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
