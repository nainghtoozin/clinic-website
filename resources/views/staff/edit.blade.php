<x-auth-layout>
    <div class="container">
        <h4 class="mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Staff Member</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('staff.update', $staff) }}">
                    @csrf
                    @method('PUT')
                    @include('staff.partials.form', ['staff' => $staff])

                    <div class="text-end">
                        <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button class="btn btn-primary">Update Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-auth-layout>
