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
        <h4 class="mb-4">✏️ Edit User</h4>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    @include('users.partials.form', ['user' => $user])

                    <div class="text-end">
                        <button class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-auth-layout>
