<x-auth-layout>
    <x-page-header title="Notifications" icon="bi-bell">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill">{{ $unreadCount }} unread</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="markAllRead()">
                <i class="bi bi-check-all me-1"></i> Mark All Read
            </button>
        </div>
    </x-page-header>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="p-3 border-bottom">
                <form method="GET" action="{{ route('notifications.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search notifications..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="filter" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="unread" {{ request('filter') === 'unread' ? 'selected' : '' }}>Unread</option>
                            <option value="read" {{ request('filter') === 'read' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="module" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            @foreach($modules as $key => $label)
                                <option value="{{ $key }}" {{ request('module') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>

            @if($notifications->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash display-4 text-muted"></i>
                    <p class="text-muted mt-3">No notifications found.</p>
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($notifications as $notification)
                        <div class="list-group-item list-group-item-action d-flex align-items-start gap-3 {{ $notification->is_read ? '' : 'notification-unread' }}"
                             id="notification-{{ $notification->id }}">
                            <div class="flex-shrink-0 mt-1">
                                <span class="badge bg-{{ $notification->is_read ? 'secondary' : 'primary' }} rounded-circle p-2">
                                    <i class="bi {{ $notification->icon }}"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1 {{ $notification->is_read ? 'fw-normal' : 'fw-semibold' }}">
                                        {{ $notification->title }}
                                    </h6>
                                    <small class="text-muted text-nowrap ms-2">{{ $notification->time_ago }}</small>
                                </div>
                                <p class="mb-1 text-muted small">{{ Str::limit($notification->message, 120) }}</p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark">{{ $notification->type_label }}</span>
                                    @if($notification->url)
                                        <a href="{{ route('notifications.show', $notification) }}" class="small text-decoration-none">
                                            View Details <i class="bi bi-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if(!$notification->is_read)
                                    <button class="btn btn-sm btn-outline-secondary" onclick="markAsRead({{ $notification->id }})" title="Mark as read">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary" onclick="markAsUnread({{ $notification->id }})" title="Mark as unread">
                                        <i class="bi bi-envelope"></i>
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteNotification({{ $notification->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function getCsrfToken() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.content : '';
        }

        function updateUnreadCount(count) {
            document.querySelectorAll('.notification-unread-badge').forEach(function (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'inline' : 'none';
            });
            var navbarBadge = document.getElementById('navbar-unread-count');
            if (navbarBadge) {
                navbarBadge.textContent = count;
                navbarBadge.style.display = count > 0 ? 'inline' : 'none';
            }
        }

        function markAsRead(id) {
            fetch('/notifications/' + id + '/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            })
            .then(function (response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(function (data) {
                var el = document.getElementById('notification-' + id);
                if (el) {
                    el.classList.remove('notification-unread');
                    var h6 = el.querySelector('h6');
                    if (h6) {
                        h6.classList.add('fw-normal');
                        h6.classList.remove('fw-semibold');
                    }
                }
                updateUnreadCount(data.unread_count);
            })
            .catch(function () {});
        }

        function markAsUnread(id) {
            fetch('/notifications/' + id + '/unread', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            })
            .then(function (response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(function (data) {
                var el = document.getElementById('notification-' + id);
                if (el) {
                    el.classList.add('notification-unread');
                    var h6 = el.querySelector('h6');
                    if (h6) {
                        h6.classList.remove('fw-normal');
                        h6.classList.add('fw-semibold');
                    }
                }
                updateUnreadCount(data.unread_count);
            })
            .catch(function () {});
        }

        function markAllRead() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            })
            .then(function (response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(function () {
                location.reload();
            })
            .catch(function () {});
        }

        function deleteNotification(id) {
            fetch('/notifications/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            })
            .then(function (response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(function (data) {
                var el = document.getElementById('notification-' + id);
                if (el) el.remove();
                updateUnreadCount(data.unread_count);
            })
            .catch(function () {});
        }
    </script>
    @endpush
</x-auth-layout>
