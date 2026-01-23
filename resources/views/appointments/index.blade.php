<x-auth-layout>

    {{-- FILTER CARD --}}
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">

            <form method="GET" class="row g-2 align-items-end" x-data="{ loading: false }" @submit="loading = true">

                {{-- Doctor --}}
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Doctor</label>
                    <select name="doctor_id" class="form-select" @change="$el.form.submit()">
                        <option value="">All Doctors</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected(request('doctor_id') == $doctor->id)>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Department --}}
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted">Department</label>
                    <select name="department_id" class="form-select" @change="$el.form.submit()">
                        <option value="">All Departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="col-12 col-md-2">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select" @change="$el.form.submit()">
                        <option value="">All</option>
                        <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') == 'approved')>Approved</option>
                        <option value="cancelled" @selected(request('status') == 'cancelled')>Cancelled</option>
                    </select>
                </div>

                {{-- Search --}}
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Patient name or email" @keyup.debounce.500ms="$el.form.submit()">
                </div>

                {{-- Loading --}}
                <div class="col-12" x-show="loading">
                    <span class="text-muted small">Filtering...</span>
                </div>

            </form>

        </div>
    </div>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>Email</th>
                    <th>Doctor</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $appointment->name }}</td>
                        <td>{{ $appointment->email }}</td>
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
                        <td class="text-center">
                            @if ($appointment->status === 'pending')
                                <form method="POST" action="{{ route('appointments.status', $appointment) }}"
                                    class="d-inline me-1" @submit="return confirm('Approve this appointment?')">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-success">✓</button>
                                </form>

                                <form method="POST" action="{{ route('appointments.status', $appointment) }}"
                                    class="d-inline" @submit="return confirm('Cancel this appointment?')">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="btn btn-sm btn-danger">✕</button>
                                </form>
                            @else
                                <span class="badge bg-secondary">Done</span>
                            @endif
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

    {{ $appointments->links() }}

</x-auth-layout>
