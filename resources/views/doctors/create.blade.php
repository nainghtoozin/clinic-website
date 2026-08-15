<x-auth-layout>
    <x-page-header title="Add New Doctor" subtitle="Create a doctor profile and availability schedule"
        :breadcrumbs="[['label' => 'Doctors', 'url' => route('doctors.index')], ['label' => 'Add Doctor']]">
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

    <form method="POST" action="{{ route('doctors.store') }}" enctype="multipart/form-data">
        @include('doctors._form', [
            'formTitle' => 'Add New Doctor',
        ])
    </form>
</x-auth-layout>