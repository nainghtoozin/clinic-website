<x-auth-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Create New Role</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('roles.store') }}">
                            @csrf

                            <!-- Role Name -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Role Name</label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Doctor, Nurse, Receptionist" required>
                            </div>

                            <!-- Permissions -->
                            @foreach ($permissions as $group => $items)
                                <div class="card mb-3 border">

                                    <!-- Group Header + Check All -->
                                    <div
                                        class="card-header bg-light fw-semibold text-uppercase d-flex justify-content-between align-items-center">
                                        <span>{{ $group }} Permissions</span>

                                        <div class="form-check">
                                            <input class="form-check-input check-all" type="checkbox"
                                                data-group="{{ $group }}" id="checkAll-{{ $group }}">
                                            <label class="form-check-label text-capitalize"
                                                for="checkAll-{{ $group }}">
                                                Check All
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Permission Items -->
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach ($items as $permission)
                                                <div class="col-md-4 col-sm-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox"
                                                            type="checkbox" name="permissions[]"
                                                            value="{{ $permission->name }}"
                                                            data-group="{{ $group }}"
                                                            id="{{ $permission->name }}">

                                                        <label class="form-check-label" for="{{ $permission->name }}">
                                                            {{ str_replace($group . '.', '', $permission->name) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            @endforeach

                            <!-- Submit -->
                            <div class="text-end mt-4">
                                <button class="btn btn-primary px-4">
                                    Create Role
                                </button>
                                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ✅ CHECK ALL + AUTO CHECK SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // 1️⃣ Check All → toggle all permissions in group
            document.querySelectorAll('.check-all').forEach(checkAll => {
                checkAll.addEventListener('change', function() {
                    const group = this.dataset.group;
                    const checked = this.checked;

                    document.querySelectorAll(
                        '.permission-checkbox[data-group="' + group + '"]'
                    ).forEach(cb => {
                        cb.checked = checked;
                    });
                });
            });

            // 2️⃣ Individual permission → auto toggle Check All
            document.querySelectorAll('.permission-checkbox').forEach(permission => {
                permission.addEventListener('change', function() {
                    const group = this.dataset.group;

                    const permissions = document.querySelectorAll(
                        '.permission-checkbox[data-group="' + group + '"]'
                    );

                    const allChecked = Array.from(permissions)
                        .every(cb => cb.checked);

                    const checkAll = document.querySelector(
                        '.check-all[data-group="' + group + '"]'
                    );

                    if (checkAll) {
                        checkAll.checked = allChecked;
                    }
                });
            });

        });
    </script>
</x-auth-layout>
