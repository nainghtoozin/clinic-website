<x-auth-layout>
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5>{{ isset($service) ? 'Edit Service' : 'Create Service' }}</h5>
            </div>

            <div class="card-body">
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($service) ? route('services.update', $service) : route('services.store') }}">
                    @csrf
                    @isset($service)
                        @method('PUT')
                    @endisset

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Service Title</label>
                            <input name="title" class="form-control" required
                                value="{{ old('title', $service->title ?? '') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <input name="category" class="form-control"
                                value="{{ old('category', $service->category ?? '') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price</label>
                            <input name="price" type="number" step="0.01" class="form-control"
                                value="{{ old('price', $service->price ?? '') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Icon Class</label>
                            <input name="icon" class="form-control" placeholder="fas fa-vials"
                                value="{{ old('icon', $service->icon ?? '') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Features (comma separated)</label>
                        <input name="features" class="form-control" placeholder="Blood Test, X-Ray, Same Day Result"
                            value="{{ old('features', isset($service) ? implode(',', $service->features ?? []) : '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service Image</label>
                        <input type="file" name="service_image" class="form-control">

                        @isset($service->service_image)
                            <img src="{{ asset('storage/' . $service->service_image) }}" class="mt-2 rounded"
                                width="120">
                        @endisset
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-control">{{ old('description', $service->description ?? '') }}</textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="status"
                            {{ old('status', $service->status ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>

                    <button class="btn btn-success">
                        {{ isset($service) ? 'Update' : 'Create' }}
                    </button>
                    <a href="{{ route('services.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
</x-auth-layout>
