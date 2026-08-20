<x-auth-layout>
    <x-page-header title="Dispense Prescription" subtitle="{{ $prescription->patient?->name ?? '' }} &middot; {{ $prescription->prescription_number }}"
        :breadcrumbs="[['label' => 'Prescriptions', 'url' => route('prescriptions.index')], ['label' => 'Dispense']]">
        <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </x-page-header>

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
                                        <th>Batch (optional)</th>
                                        <th class="text-end">Dispense Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($prescription->items as $item)
                                        @php
                                            $validBatches = $item->medicine?->inventoryBatches
                                                ->where('quantity', '>', 0)
                                                ->filter(fn ($b) => ! $b->isExpired());
                                            $usable = $validBatches->sum('quantity');
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $item->medicine->name ?? 'Deleted' }}</strong>
                                                @if ($item->medicine && $usable < $item->quantity)
                                                    <br><small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Insufficient usable stock</small>
                                                @endif
                                            </td>
                                            <td class="text-end">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ $usable }}</td>
                                            <td>
                                                @if ($item->medicine && $validBatches->isNotEmpty())
                                                    <select name="batch_selections[{{ $item->id }}]" class="form-select form-select-sm">
                                                        <option value="">Auto (FEFO)</option>
                                                        @foreach ($validBatches as $batch)
                                                            <option value="{{ $batch->id }}" {{ old('batch_selections.' . $item->id) == $batch->id ? 'selected' : '' }}>
                                                                {{ $batch->batch_number }} ({{ $batch->quantity }} &middot; Exp {{ $batch->expiry_date ? fmt_date($batch->expiry_date) : '-' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <span class="text-muted small">No valid batch</span>
                                                @endif
                                            </td>
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
                        <div class="form-text mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Leave batch as <strong>Auto (FEFO)</strong> to dispense from the earliest-expiring valid batch, or pick a specific valid batch.
                            Expired batches are never dispensed.
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
