<x-auth-layout>
    <x-page-header title="Clinic Settings" icon="bi-hospital" :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Clinic']]">
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
                    <h6 class="mb-0"><i class="bi bi-hospital me-2"></i>Clinic Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.clinic.update') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Clinic Name <span class="text-danger">*</span></label>
                                <input type="text" name="clinic_name" class="form-control"
                                    value="{{ $settings['clinic_name'] ?? '' }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="clinic_email" class="form-control"
                                    value="{{ $settings['clinic_email'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="clinic_phone" class="form-control"
                                    value="{{ $settings['clinic_phone'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Currency</label>
                                <input type="text" name="clinic_currency" class="form-control"
                                    placeholder="USD" value="{{ $settings['clinic_currency'] ?? 'USD' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Opening Hours</label>
                                <input type="text" name="clinic_opening_hours" class="form-control"
                                    placeholder="Mon-Fri: 8AM-5PM" value="{{ $settings['clinic_opening_hours'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Default Consultation Fee</label>
                                <input type="number" name="clinic_default_fee" class="form-control" step="0.01" min="0"
                                    value="{{ $settings['clinic_default_fee'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" name="clinic_tax_rate" class="form-control" step="0.01" min="0" max="100"
                                    value="{{ $settings['clinic_tax_rate'] ?? '' }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="clinic_address" class="form-control" rows="2">{{ $settings['clinic_address'] ?? '' }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Receipt Footer</label>
                                <textarea name="clinic_receipt_footer" class="form-control" rows="2"
                                    placeholder="Thank you for your visit!">{{ $settings['clinic_receipt_footer'] ?? '' }}</textarea>
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
