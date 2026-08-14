<x-auth-layout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Consultation Report</h4>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Total Consultations</h6>
                    <h3>{{ $totalConsultations }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Completed</h6>
                    <h3 class="text-success">{{ $completedCount }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Draft</h6>
                    <h3 class="text-warning">{{ $draftCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Filters</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.consultation') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Doctor</label>
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach($doctors as $id => $name)
                                <option value="{{ $id }}" {{ request('doctor_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('reports.consultation') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Diagnosis</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultations as $consultation)
                            <tr>
                                <td>{{ $consultation->patient->name ?? '-' }}</td>
                                <td>{{ $consultation->doctor->name ?? '-' }}</td>
                                <td>{{ Str::limit($consultation->diagnosis, 40) ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $consultation->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($consultation->status) }}
                                    </span>
                                </td>
                                <td>{{ fmt_datetime($consultation->created_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No consultations found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($consultations->hasPages())
        <div class="card-footer bg-white">
            {{ $consultations->links() }}
        </div>
        @endif
    </div>
</x-auth-layout>
