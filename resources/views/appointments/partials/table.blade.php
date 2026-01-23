<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Patient</th>
                <th>Email</th>
                <th>Doctor</th>
                <th>Department</th>
                <th>Status</th>
                <th width="120">Action</th>
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
                    <td>
                        <span
                            class="badge bg-{{ $appointment->status == 'approved'
                                ? 'success'
                                : ($appointment->status == 'cancelled'
                                    ? 'danger'
                                    : 'warning') }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-success action-btn" data-id="{{ $appointment->id }}"
                            data-status="approved">
                            ✓
                        </button>
                        <button class="btn btn-sm btn-danger action-btn" data-id="{{ $appointment->id }}"
                            data-status="cancelled">
                            ✕
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        No appointments found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $appointments->links() }}
</div>

@push('scripts')
    <script>
        $(document).on('click', '.action-btn', function() {
            let id = $(this).data('id');
            let status = $(this).data('status');

            if (!confirm('Are you sure?')) return;

            $.ajax({
                url: `/appointments/${id}/status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status
                },
                success: function() {
                    loadAppointments();
                }
            });
        });
    </script>
@endpush
