<x-auth-layout>
    <div class="container py-4">

        <div class="row justify-content-center">
            <div class="col-lg-8">

                {{-- Update Profile Info --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Profile Information</h5>
                        <small class="text-muted">
                            Update your account's profile information.
                        </small>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- Update Password --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Update Password</h5>
                        <small class="text-muted">
                            Ensure your account is using a long, random password.
                        </small>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                {{-- Delete Account --}}
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Delete Account</h5>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-auth-layout>
