<x-auth-layout>
    <div class="container">
        <h4 class="mb-4">Website Settings</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('settings.website.update') }}" enctype="multipart/form-data">
            @csrf

            {{-- Website Name --}}
            <div class="mb-3">
                <label>Website Name</label>
                <input type="text" name="site_name" class="form-control"
                    value="{{ $settings['site.site_name'] ?? '' }}">
            </div>

            {{-- Contact Phone --}}
            <div class="mb-3">
                <label>Contact Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ $settings['site.phone'] ?? '' }}">
            </div>

            {{-- Address --}}
            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control">{{ $settings['site.address'] ?? '' }}</textarea>
            </div>

            {{-- Website Logo --}}
            <div class="mb-3">
                <label>Website Logo</label>
                <input type="file" name="logo" class="form-control">

                @if (!empty($settings['site.logo']))
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $settings['site.logo']) }}" height="60">
                    </div>
                @endif
            </div>

            {{-- Social Links --}}
            <hr>
            <h6 class="mb-3">Social Media Links</h6>

            <div class="mb-3">
                <label>Facebook URL</label>
                <input type="url" name="social[facebook]" class="form-control"
                    placeholder="https://facebook.com/yourpage" value="{{ $settings['site.social.facebook'] ?? '' }}">
            </div>

            <div class="mb-3">
                <label>Twitter / X URL</label>
                <input type="url" name="social[twitter]" class="form-control"
                    placeholder="https://x.com/yourprofile" value="{{ $settings['site.social.twitter'] ?? '' }}">
            </div>

            <div class="mb-3">
                <label>Instagram URL</label>
                <input type="url" name="social[instagram]" class="form-control"
                    placeholder="https://instagram.com/yourprofile"
                    value="{{ $settings['site.social.instagram'] ?? '' }}">
            </div>

            <div class="mb-3">
                <label>LinkedIn URL</label>
                <input type="url" name="social[linkedin]" class="form-control"
                    placeholder="https://linkedin.com/company/yourcompany"
                    value="{{ $settings['site.social.linkedin'] ?? '' }}">
            </div>

            <button class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</x-auth-layout>
