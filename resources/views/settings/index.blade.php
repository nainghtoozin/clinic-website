<x-auth-layout>
    <x-page-header title="Clinic Settings" icon="bi-buildings" :breadcrumbs="[['label' => 'Settings', 'url' => '#']]">
    </x-page-header>

    <div class="row g-4">
        <div class="col-md-3 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-2">
                    <nav class="nav nav-pills flex-column gap-1">
                        <a class="nav-link text-start d-flex align-items-center {{ request()->routeIs('settings.clinic') ? 'active' : '' }}"
                            href="{{ route('settings.clinic') }}">
                            <i class="bi bi-hospital me-2"></i> Clinic
                        </a>
                        <a class="nav-link text-start d-flex align-items-center {{ request()->routeIs('settings.website*') ? 'active' : '' }}"
                            href="{{ route('settings.website.edit') }}">
                            <i class="bi bi-globe me-2"></i> Website
                        </a>
                        <a class="nav-link text-start d-flex align-items-center {{ request()->routeIs('settings.appointment') ? 'active' : '' }}"
                            href="{{ route('settings.appointment') }}">
                            <i class="bi bi-calendar-check me-2"></i> Appointment
                        </a>
                        <a class="nav-link text-start d-flex align-items-center {{ request()->routeIs('settings.queue') ? 'active' : '' }}"
                            href="{{ route('settings.queue') }}">
                            <i class="bi bi-people me-2"></i> Queue
                        </a>
                        <a class="nav-link text-start d-flex align-items-center {{ request()->routeIs('settings.billing') ? 'active' : '' }}"
                            href="{{ route('settings.billing') }}">
                            <i class="bi bi-receipt me-2"></i> Billing
                        </a>
                        <a class="nav-link text-start d-flex align-items-center {{ request()->routeIs('settings.inventory') ? 'active' : '' }}"
                            href="{{ route('settings.inventory') }}">
                            <i class="bi bi-box-seam me-2"></i> Inventory
                        </a>
                        <a class="nav-link text-start d-flex align-items-center {{ request()->routeIs('settings.prescription') ? 'active' : '' }}"
                            href="{{ route('settings.prescription') }}">
                            <i class="bi bi-capsule me-2"></i> Prescription
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-buildings display-4 text-muted"></i>
                    <h5 class="mt-3">Clinic Settings</h5>
                    <p class="text-muted">Select a section from the sidebar to configure.</p>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
