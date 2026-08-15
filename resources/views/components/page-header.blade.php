@props([
    'title' => null,
    'subtitle' => null,
    'breadcrumbs' => [],
])

<div class="page-header d-flex flex-wrap justify-content-between align-items-end gap-3">
    <div>
        @if (!empty($breadcrumbs))
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>{{ __('app.nav.dashboard') }}</a>
                    </li>
                    @foreach ($breadcrumbs as $crumb)
                        @if (isset($crumb['url']) && !$loop->last)
                            <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                        @else
                            <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        <h4 class="page-title mb-0">{{ $title }}</h4>
        @if ($subtitle)
            <p class="text-muted small mb-0 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="d-flex align-items-center gap-2">
        {{ $slot }}
    </div>
</div>