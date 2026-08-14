<x-auth-layout>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><i class="bi bi-hospital me-2"></i>Clinic Settings</h4>
            <div class="btn-group">
                <a href="{{ route('settings.clinic') }}"
                    class="btn btn-{{ request()->routeIs('settings.clinic') ? 'primary' : 'outline-primary' }}">
                    Clinic
                </a>
                @can('settings.view')
                    <a href="{{ route('settings.website.edit') }}"
                        class="btn btn-{{ request()->routeIs('settings.website.edit') ? 'primary' : 'outline-primary' }}">
                        Website
                    </a>
                @endcan
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0">
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
                        <button class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-auth-layout>
