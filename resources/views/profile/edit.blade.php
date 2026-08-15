<x-auth-layout>
    <x-page-header title="Profile" subtitle="Update your account profile, password and account settings"
        :breadcrumbs="[['label' => __('app.topbar.profile')]]">
    </x-page-header>

    <div class="row g-4">
        {{-- Left: Account Settings navigation --}}
        <div class="col-lg-4 col-xl-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Account Settings</h6>
                </div>
                <div class="card-body p-2">
                    <nav class="nav nav-pills flex-column gap-1">
                        <a class="nav-link active" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person me-2"></i> Profile
                        </a>
                        <a class="nav-link" href="{{ route('user.settings') }}#appearance">
                            <i class="bi bi-palette me-2"></i> Appearance
                        </a>
                        <a class="nav-link" href="{{ route('user.settings') }}#localization">
                            <i class="bi bi-globe2 me-2"></i> Language &amp; Region
                        </a>
                        <a class="nav-link" href="{{ route('user.settings') }}#preferences">
                            <i class="bi bi-sliders me-2"></i> Preferences
                        </a>
                        <a class="nav-link" href="{{ route('user.settings') }}#security">
                            <i class="bi bi-shield-lock me-2"></i> Security
                        </a>
                        <a class="nav-link" href="{{ route('user.settings') }}#account">
                            <i class="bi bi-person-gear me-2"></i> Account
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        {{-- Right: Profile Information --}}
        <div class="col-lg-8 col-xl-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Profile Information</h6>
                    <small class="text-muted">Update your account's profile information.</small>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
