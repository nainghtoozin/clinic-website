@php
    $active = $active ?? 'dashboard';
@endphp

<div class="btn-group btn-group-sm">
    <a href="{{ route('inventory.dashboard') }}" class="btn {{ $active === 'dashboard' ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-speedometer2 me-1"></i> Dashboard
    </a>
    <a href="{{ route('inventory.index') }}" class="btn {{ $active === 'index' ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-capsule me-1"></i> Stock List
    </a>
    <a href="{{ route('inventory.expiry') }}" class="btn {{ $active === 'expiry' ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-calendar-x me-1"></i> Expiry
    </a>
    <a href="{{ route('inventory.movements') }}" class="btn {{ $active === 'movements' ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="bi bi-clock-history me-1"></i> Movements
    </a>
</div>
