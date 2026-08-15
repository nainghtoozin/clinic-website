<x-auth-layout>
    <x-page-header title="Edit Doctor Information" subtitle="Update a doctor profile and availability schedule"
        :breadcrumbs="[['label' => 'Doctors', 'url' => route('doctors.index')], ['label' => $doctor->name]]">
        <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to Doctors
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

    <form method="POST" action="{{ route('doctors.update', $doctor) }}" enctype="multipart/form-data">
        @include('doctors._form', [
            'formTitle' => 'Edit Doctor Information',
        ])
    </form>
</x-auth-layout>