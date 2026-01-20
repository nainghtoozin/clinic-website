{{-- resources/views/departments/partials/form.blade.php --}}

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Department Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $department->name ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control"
            value="{{ old('category', $department->category ?? '') }}">
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control">{{ old('description', $department->description ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Icon Class</label>
        <input type="text" name="icon" class="form-control" placeholder="fas fa-baby"
            value="{{ old('icon', $department->icon ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control">
        @isset($department->image)
            <img src="{{ asset('storage/' . $department->image) }}" class="img-thumbnail mt-2" width="120">
        @endisset
    </div>

    <div class="col-md-6">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control"
            value="{{ old('sort_order', $department->sort_order ?? 0) }}">
    </div>

    <div class="col-md-6 d-flex align-items-center mt-4">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                {{ old('is_active', $department->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
    </div>
</div>
