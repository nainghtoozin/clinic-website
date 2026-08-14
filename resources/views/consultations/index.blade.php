<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Consultations</h5>
        @can('consultation.create')
            <a href="{{ route('consultations.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> New Consultation
            </a>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('consultations.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <select name="doctor_id" class="form-select form-select-sm">
                            <option value="">All Doctors</option>
                            @foreach ($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th class="d-none d-md-table-cell">Doctor</th>
                            <th class="d-none d-lg-table-cell">Diagnosis</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultations as $consultation)
                            <tr>
                                <td>{{ fmt_date($consultation->created_at) }}</td>
                                <td>{{ $consultation->patient->name ?? '-' }}</td>
                                <td class="d-none d-md-table-cell">{{ $consultation->doctor->name ?? '-' }}</td>
                                <td class="d-none d-lg-table-cell">{{ Str::limit($consultation->diagnosis ?? '-', 30) }}</td>
                                <td>
                                    <span class="badge {{ $consultation->isCompleted() ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($consultation->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if ($consultation->isDraft())
                                        @can('consultation.edit')
                                            <a href="{{ route('consultations.edit', $consultation) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-clipboard-check fs-1 d-block mb-2"></i>
                                    <div>No consultations found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $consultations->links() }}
    </div>
</x-auth-layout>
