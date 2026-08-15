<x-auth-layout>
    <x-page-header title="Edit Patient" subtitle="Update the patient record ({{ $patient->patient_number }})"
        :breadcrumbs="[['label' => 'Patients', 'url' => route('patients.index')], ['label' => $patient->name]]">
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-eye me-1"></i> View
        </a>
        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </x-page-header>

    @include('patients._form', ['patient' => $patient])
</x-auth-layout>