<x-auth-layout>
    <x-page-header title="Follow-ups" subtitle="Pending and completed follow-ups"
        :breadcrumbs="[['label' => 'Communications', 'url' => route('communications.index')], ['label' => 'Follow-ups']]">
    </x-page-header>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('communications.follow-ups', ['filter' => 'overdue']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 {{ $filter === 'overdue' ? 'border-danger border-2' : '' }}">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-danger">{{ $counts['overdue'] }}</div>
                        <small class="text-muted">Overdue</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('communications.follow-ups', ['filter' => 'today']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 {{ $filter === 'today' ? 'border-warning border-2' : '' }}">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-warning">{{ $counts['today'] }}</div>
                        <small class="text-muted">Due Today</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('communications.follow-ups', ['filter' => 'upcoming']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 {{ $filter === 'upcoming' ? 'border-info border-2' : '' }}">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-info">{{ $counts['upcoming'] }}</div>
                        <small class="text-muted">Upcoming</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('communications.follow-ups', ['filter' => 'completed']) }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 {{ $filter === 'completed' ? 'border-success border-2' : '' }}">
                    <div class="card-body text-center py-3">
                        <div class="fs-3 fw-bold text-success">{{ $counts['completed'] }}</div>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('communications.follow-ups') }}">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small text-muted mb-1">Search Patient</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Patient name or number..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted mb-1">Patient</label>
                        <select name="patient_id" class="form-select form-select-sm">
                            <option value="">All Patients</option>
                            @foreach (\App\Models\Patient::orderBy('name')->get() as $patient)
                                <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                    {{ $patient->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i></button>
                        @if (request()->hasAny(['search', 'patient_id']))
                            <a href="{{ route('communications.follow-ups', ['filter' => $filter]) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if ($followUps->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                    <h6 class="text-muted">No Follow-ups</h6>
                    <p class="small text-muted mb-0">No {{ $filter }} follow-ups found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Follow-up Date</th>
                                <th>Purpose</th>
                                <th class="d-none d-md-table-cell">Note</th>
                                <th class="d-none d-md-table-cell">Staff</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($followUps as $fu)
                                <tr>
                                    <td>
                                        <div class="fw-semibold small">{{ $fu->patient->name ?? '-' }}</div>
                                        <div class="text-muted small">{{ $fu->patient->patient_number ?? '' }}</div>
                                    </td>
                                    <td class="small">
                                        {{ $fu->follow_up_date->format('d M Y') }}
                                        @if ($fu->isFollowUpOverdue())
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                        @elseif ($fu->isFollowUpDueToday())
                                            <span class="badge bg-warning text-dark ms-1">Today</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $fu->getPurposeBadgeClass() }}">{{ $fu->purpose_label }}</span></td>
                                    <td class="d-none d-md-table-cell small text-muted">{{ Str::limit($fu->follow_up_note, 60) }}</td>
                                    <td class="d-none d-md-table-cell small">{{ $fu->user->name ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('communications.show', $fu) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if (!$fu->follow_up_completed)
                                                <form method="POST" action="{{ route('communications.complete-follow-up', $fu) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Mark Complete">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($followUps->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $followUps->withQueryString()->links() }}
        </div>
    @endif
</x-auth-layout>
