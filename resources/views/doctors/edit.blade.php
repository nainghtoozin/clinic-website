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


    <form method="POST" action="{{ route('doctors.update', $doctor) }}" enctype="multipart/form-data">
        @include('doctors._form', [
            'formTitle' => 'Edit Doctor Information',
        ])
    </form>
</x-auth-layout>
