<x-auth-layout>
    <x-page-header title="Register New Patient" subtitle="Create a new patient record"
        :breadcrumbs="[['label' => 'Patients', 'url' => route('patients.index')], ['label' => 'New Patient']]">
        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-page-header>

    @include('patients._form')
</x-auth-layout>