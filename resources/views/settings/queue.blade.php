<x-auth-layout>
    <x-page-header title="Queue Settings" icon="bi-people" :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Queue']]">
    </x-page-header>

    <div class="row g-4">
        <div class="col-md-3 col-lg-2">
            @include('settings._sidebar')
        </div>

        <div class="col-md-9 col-lg-10">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-people me-2"></i>Queue Configuration</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.queue.update') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ticket Prefix <span class="text-danger">*</span></label>
                                <input type="text" name="ticket_prefix" class="form-control" maxlength="5"
                                    value="{{ $settings['queue.ticket_prefix'] ?? 'A' }}" required>
                                <div class="form-text">Prefix for queue ticket numbers (e.g., "A" produces A001, A002...)</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Sequence Length <span class="text-danger">*</span></label>
                                <input type="number" name="ticket_sequence_length" class="form-control" min="2" max="6"
                                    value="{{ $settings['queue.ticket_sequence_length'] ?? 3 }}" required>
                                <div class="form-text">Number of digits in ticket sequence (e.g., 3 produces 001-999).</div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i> Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
