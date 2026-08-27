<x-auth-layout>
    <x-page-header title="Appointment Settings" icon="bi-calendar-check" :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Appointment']]">
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
                    <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Appointment Configuration</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.appointment.update') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Default Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" name="default_duration" class="form-control" min="5" max="480"
                                    value="{{ $settings['appointment.default_duration'] ?? 30 }}" required>
                                <div class="form-text">Default slot length for new appointments.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Min Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" name="min_duration" class="form-control" min="5" max="120"
                                    value="{{ $settings['appointment.min_duration'] ?? 15 }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Max Duration (minutes) <span class="text-danger">*</span></label>
                                <input type="number" name="max_duration" class="form-control" min="15" max="480"
                                    value="{{ $settings['appointment.max_duration'] ?? 180 }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Advance Booking (days)</label>
                                <input type="number" name="advance_booking_days" class="form-control" min="1" max="365"
                                    value="{{ $settings['appointment.advance_booking_days'] ?? 90 }}">
                                <div class="form-text">How many days ahead patients can book. Leave empty for unlimited.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Cancellation Window (hours)</label>
                                <input type="number" name="cancellation_hours" class="form-control" min="0" max="168"
                                    value="{{ $settings['appointment.cancellation_hours'] ?? 24 }}">
                                <div class="form-text">Minimum hours before appointment for cancellation.</div>
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
