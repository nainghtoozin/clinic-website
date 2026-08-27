<x-auth-layout>
    <x-page-header title="Inventory Settings" icon="bi-box-seam" :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Inventory']]">
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
                    <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Inventory Configuration</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.inventory.update') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Expiry Warning Days <span class="text-danger">*</span></label>
                                <input type="number" name="expiry_warning_days" class="form-control" min="1" max="365"
                                    value="{{ $settings['inventory.expiry_warning_days'] ?? 30 }}" required>
                                <div class="form-text">Number of days before expiry to show warning (default: 30).</div>
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
