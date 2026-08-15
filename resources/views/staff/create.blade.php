<x-auth-layout>
    <x-page-header title="Add Staff Member" subtitle="Create a staff account and assign a role"
        :breadcrumbs="[['label' => 'Staff', 'url' => route('staff.index')], ['label' => 'Add Staff']]">
        <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Staff
        </a>
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('staff.store') }}">
                @csrf
                @include('staff.partials.form')

                <div class="text-end mt-3">
                    <a href="{{ route('staff.index') }}" class="btn btn-light me-2">Cancel</a>
                    <button class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Create Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-auth-layout>