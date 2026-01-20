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
        <h4 class="mb-4">➕ Create Department</h4>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('departments.store') }}" enctype="multipart/form-data">
                    @csrf

                    @include('departments.partials.form')

                    <div class="text-end">
                        <button class="btn btn-primary">Save Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-auth-layout>
