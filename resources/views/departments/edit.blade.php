<x-auth-layout>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container">
        <h4 class="mb-4">✏️ Edit Department</h4>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('departments.update', $department) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include('departments.partials.form', ['department' => $department])

                    <div class="text-end">
                        <a href="{{ route('departments.index') }}" class="btn btn-primary me-4">Back</a>
                        <button class="btn btn-primary">
                            Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-auth-layout>
