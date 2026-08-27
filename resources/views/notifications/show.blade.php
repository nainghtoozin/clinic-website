<x-auth-layout>
    <div class="container py-4">
        <div class="mb-3">
            <a href="{{ route('notifications.index') }}" class="text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to Notifications
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="badge bg-{{ $notification->is_read ? 'secondary' : 'primary' }} rounded-circle p-3">
                        <i class="bi {{ $notification->icon }} fs-5"></i>
                    </span>
                    <div class="flex-grow-1">
                        <h4 class="mb-1">{{ $notification->title }}</h4>
                        <div class="d-flex align-items-center gap-3 text-muted small">
                            <span><i class="bi bi-tag me-1"></i> {{ $notification->type_label }}</span>
                            <span><i class="bi bi-clock me-1"></i> {{ $notification->created_at->format('M d, Y h:i A') }}</span>
                            @if($notification->read_at)
                                <span><i class="bi bi-check-circle me-1"></i> Read {{ $notification->read_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <hr>

                <div class="notification-message mb-4">
                    {!! nl2br(e($notification->message)) !!}
                </div>

                @if($notification->metadata)
                    <div class="card notification-metadata-card bg-light border-0 mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-muted small mb-2"><i class="bi bi-info-circle me-1"></i> Additional Details</h6>
                            <dl class="row mb-0 small">
                                @foreach($notification->metadata as $key => $value)
                                    <dt class="col-sm-3">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                    <dd class="col-sm-9">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                @endif

                @if($notification->url)
                    <a href="{{ $notification->url }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> View Related Record
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-auth-layout>
