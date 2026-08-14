<x-auth-layout>
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Dispense Prescription</h5>
            <small class="text-muted">{{ $prescription->prescription_number }}</small>
        </div>
        <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-capsule me-2"></i>Prescription Items</h6>
                    <span class="badge bg-{{ $prescription->isDispensed() ? 'success' : 'warning' }}">
                        {{ $prescription->isDispensed() ? 'Dispensed' : 'Pending' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Patient</label>
                            <div class="fw-semibold">{{ $prescription->patient->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Doctor</label>
                            <div class="fw-semibold">{{ $prescription->doctor->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date</label>
                            <div>{{ fmt_date($prescription->prescribed_date) }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('inventory.dispense', $prescription) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Medicine</th>
                                        <th class="text-end">Prescribed</th>
                                        <th class="text-end">Available</th>
                                        <th class="text-end">Dispense Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($prescription->items as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->medicine->name ?? 'Deleted' }}</strong>
                                                @if ($item->medicine && $item->medicine->stock_quantity < $item->quantity)
                                                    <br><small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Insufficient stock</small>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ $item->medicine->stock_quantity ?? 0 }}</td>
                                            <td class="text-end">
                                                <input type="number"
                                                    name="dispensed_quantities[{{ $item->id }}]"
                                                    class="form-control form-control-sm d-inline-block text-end"
                                                    style="width: 80px;"
                                                    min="0"
                                                    max="{{ $item->quantity }}"
                                                    value="{{ old('dispensed_quantities.' . $item->id, $item->quantity) }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button class="btn btn-success" {{ $prescription->isDispensed() ? 'disabled' : '' }}>
                                <i class="bi bi-check-lg me-1"></i> Dispense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
