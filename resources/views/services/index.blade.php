<x-auth-layout>

    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h4>Services</h4>
            <a href="{{ route('services.create') }}" class="btn btn-primary">+ Add Service</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th width="160">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($services as $service)
                    <tr>
                        <td>{{ $service->title }}</td>
                        <td>{{ $service->category }}</td>
                        <td>${{ number_format($service->price, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $service->status ? 'success' : 'secondary' }}">
                                {{ $service->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @can('service.edit')
                                <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endcan
                            @can('service.delete')
                                <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this service?')">
                                        Delete
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $services->links() }}
    </div>


</x-auth-layout>
