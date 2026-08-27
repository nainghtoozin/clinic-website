<x-auth-layout>
    <x-page-header title="Billing Settings" icon="bi-receipt" :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Billing']]">
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
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Invoice Configuration</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.billing.update') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Prefix <span class="text-danger">*</span></label>
                                <input type="text" name="prefix" class="form-control" maxlength="10"
                                    value="{{ $settings['invoice.prefix'] ?? 'INV' }}" required>
                                <div class="form-text">Prefix for invoice numbers (e.g., "INV" produces INV-20260826-0001).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Sequence Length <span class="text-danger">*</span></label>
                                <input type="number" name="sequence_length" class="form-control" min="2" max="6"
                                    value="{{ $settings['invoice.sequence_length'] ?? 4 }}" required>
                                <div class="form-text">Number of digits in invoice sequence (e.g., 4 produces 0001-9999).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Default Tax Rate (%)</label>
                                <input type="number" name="default_tax_rate" class="form-control" step="0.01" min="0" max="100"
                                    value="{{ $settings['invoice.default_tax_rate'] ?? '' }}">
                                <div class="form-text">Default tax rate applied to new invoices. Leave empty for no default.</div>
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
