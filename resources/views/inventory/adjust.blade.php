<x-auth-layout>
    <div class="container">
        <h4 class="mb-4"><i class="bi bi-gear me-2"></i>Stock Adjustment — {{ $medicine->name }}</h4>

        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="{{ route('inventory.adjust', $medicine) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Current Stock</label>
                                <input type="text" class="form-control" value="{{ $medicine->stock_quantity }}" disabled>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Direction <span class="text-danger">*</span></label>
                                    <select name="direction" class="form-select" required>
                                        <option value="increase" {{ old('direction') === 'increase' ? 'selected' : '' }}>Increase (+)</option>
                                        <option value="decrease" {{ old('direction') === 'decrease' ? 'selected' : '' }}>Decrease (−)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control" min="1" value="{{ old('quantity', 1) }}" required>
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">Reason <span class="text-danger">*</span></label>
                                <input type="text" name="reason" class="form-control" placeholder="e.g. Stock count correction, Damaged" value="{{ old('reason') }}" required>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary me-2">Cancel</a>
                                <button class="btn btn-secondary">Save Adjustment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
