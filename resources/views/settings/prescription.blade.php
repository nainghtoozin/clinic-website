<x-auth-layout>
    <x-page-header title="Prescription Settings" icon="bi-capsule" :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Prescription']]">
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
                    <h6 class="mb-0"><i class="bi bi-capsule me-2"></i>Prescription Configuration</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.prescription.update') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Prescription Prefix <span class="text-danger">*</span></label>
                                <input type="text" name="prefix" class="form-control" maxlength="10"
                                    value="{{ $settings['prescription.prefix'] ?? 'RX' }}" required>
                                <div class="form-text">Prefix for prescription numbers (e.g., "RX" produces RX-20260826-0001).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Sequence Length <span class="text-danger">*</span></label>
                                <input type="number" name="sequence_length" class="form-control" min="2" max="6"
                                    value="{{ $settings['prescription.sequence_length'] ?? 4 }}" required>
                                <div class="form-text">Number of digits in prescription sequence (e.g., 4 produces 0001-9999).</div>
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
