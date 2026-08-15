@php
    $selected = $selected ?? [];
    $totalPermissions = $permissions->flatten()->count();
@endphp

<div class="mb-3">
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" id="permission-search" class="form-control" placeholder="Search permissions (e.g. patient, invoice, view)...">
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <small class="text-muted" id="permission-count">
            <i class="bi bi-shield-check me-1"></i><span id="selected-permission-count">0</span> of {{ $totalPermissions }} permissions selected
        </small>
        <button type="button" class="btn btn-link btn-sm p-0" id="select-all-btn">
            <i class="bi bi-check2-square me-1"></i>Select all
        </button>
        <button type="button" class="btn btn-link btn-sm p-0" id="clear-all-btn">
            <i class="bi bi-x-square me-1"></i>Clear all
        </button>
    </div>
</div>

@forelse ($permissions as $group => $items)
    <div class="card shadow-sm border-0 mb-3 permission-group" data-group="{{ $group }}">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
            <div>
                <span class="badge bg-primary-subtle text-primary text-uppercase me-2">{{ $group }}</span>
                <span class="text-muted small">{{ $items->count() }} permission(s)</span>
            </div>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input check-all" type="checkbox" role="switch"
                    data-group="{{ $group }}" id="checkAll-{{ $group }}">
                <label class="form-check-label small" for="checkAll-{{ $group }}">Check all</label>
            </div>
        </div>
        <div class="card-body py-2">
            <div class="row g-1">
                @foreach ($items as $permission)
                    <div class="col-12 col-sm-6 col-lg-4 permission-item" data-name="{{ strtolower($permission->name) }}">
                        <div class="form-check form-switch">
                            <input class="form-check-input permission-checkbox" type="checkbox" role="switch"
                                name="permissions[]" value="{{ $permission->name }}" data-group="{{ $group }}"
                                id="{{ $permission->name }}"
                                {{ in_array($permission->name, $selected) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="{{ $permission->name }}">
                                {{ str_replace($group . '.', '', $permission->name) }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-shield-x fs-1 d-block mb-2"></i>
        <small>No permissions found.</small>
    </div>
@endforelse

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAlls = document.querySelectorAll('.check-all');
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        const selectedCount = document.getElementById('selected-permission-count');
        const searchInput = document.getElementById('permission-search');
        const selectAllBtn = document.getElementById('select-all-btn');
        const clearAllBtn = document.getElementById('clear-all-btn');

        function updateGroupCheckAll(group) {
            const boxes = document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]');
            const allChecked = Array.from(boxes).every(cb => cb.checked);
            const checkAll = document.querySelector('.check-all[data-group="' + group + '"]');
            if (checkAll) checkAll.checked = allChecked;
        }

        function updateSelectedCount() {
            selectedCount.textContent = document.querySelectorAll('.permission-checkbox:checked').length;
        }

        checkAlls.forEach(function (checkAll) {
            checkAll.addEventListener('change', function () {
                const group = this.dataset.group;
                document.querySelectorAll('.permission-checkbox[data-group="' + group + '"]').forEach(function (cb) {
                    cb.checked = checkAll.checked;
                });
                updateSelectedCount();
            });
        });

        permissionCheckboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                updateGroupCheckAll(this.dataset.group);
                updateSelectedCount();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();
                document.querySelectorAll('.permission-group').forEach(function (group) {
                    let visible = 0;
                    group.querySelectorAll('.permission-item').forEach(function (item) {
                        const match = term === '' || item.dataset.name.includes(term);
                        item.style.display = match ? '' : 'none';
                        if (match) visible++;
                    });
                    const header = group.querySelector('.card-header');
                    const label = header.querySelector('.text-muted');
                    if (label) label.textContent = visible + ' permission(s)';
                    group.style.display = visible > 0 ? '' : 'none';
                });
            });
        }

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.permission-checkbox').forEach(function (cb) {
                    if (cb.closest('.permission-item').style.display !== 'none') cb.checked = true;
                });
                checkAlls.forEach(function (checkAll) { checkAll.checked = true; });
                updateSelectedCount();
            });
        }

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.permission-checkbox').forEach(function (cb) { cb.checked = false; });
                checkAlls.forEach(function (checkAll) { checkAll.checked = false; });
                updateSelectedCount();
            });
        }

        // Initial state
        checkAlls.forEach(function (checkAll) { updateGroupCheckAll(checkAll.dataset.group); });
        updateSelectedCount();
    });
</script>
@endpush
