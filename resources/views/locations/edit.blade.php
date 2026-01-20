<x-auth-layout>
    <div class="container">
        <h4 class="mb-4">✏️ Edit Location</h4>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('locations.update', $location) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $location->name) }}">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $location->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('locations.index') }}" class="btn btn-light">
                            ← Back
                        </a>
                        <button class="btn btn-primary">
                            Update Location
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-auth-layout>
