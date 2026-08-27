<x-auth-layout>
    <x-page-header title="Website Settings" icon="bi-globe" :breadcrumbs="[['label' => 'Settings', 'url' => route('settings.index')], ['label' => 'Website']]">
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
                    <h6 class="mb-0"><i class="bi bi-globe me-2"></i>Website & Branding</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.website.update') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Website Name <span class="text-danger">*</span></label>
                                <input type="text" name="site_name" class="form-control"
                                    value="{{ $settings['site.site_name'] ?? '' }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Contact Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ $settings['site.email'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ $settings['site.phone'] ?? '' }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ $settings['site.address'] ?? '' }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                @if (!empty($settings['site.logo']))
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $settings['site.logo']) }}" height="60" class="border rounded p-1">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Social Media Links</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Facebook URL</label>
                                <input type="url" name="social[facebook]" class="form-control"
                                    placeholder="https://facebook.com/yourpage" value="{{ $settings['site.social.facebook'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Twitter / X URL</label>
                                <input type="url" name="social[twitter]" class="form-control"
                                    placeholder="https://x.com/yourprofile" value="{{ $settings['site.social.twitter'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Instagram URL</label>
                                <input type="url" name="social[instagram]" class="form-control"
                                    placeholder="https://instagram.com/yourprofile"
                                    value="{{ $settings['site.social.instagram'] ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="url" name="social[linkedin]" class="form-control"
                                    placeholder="https://linkedin.com/company/yourcompany"
                                    value="{{ $settings['site.social.linkedin'] ?? '' }}">
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
