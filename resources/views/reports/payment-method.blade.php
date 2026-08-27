<x-auth-layout>
    <x-page-header title="Payment Method Report" subtitle="Payments breakdown by method"
        :breadcrumbs="[['label' => 'Reports', 'url' => route('reports.index')], ['label' => 'Payment Methods']]">
    </x-page-header>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('reports.payment-method') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">Start Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small text-muted mb-1">End Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Total Payments</div>
                    <div class="fs-3 fw-bold text-success mt-1">{{ number_format($totalPayments, 2) }}</div>
                    <div class="small text-muted">{{ number_format($totalCount) }} transaction(s)</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">Average Payment</div>
                    <div class="fs-3 fw-bold text-primary mt-1">{{ $totalCount > 0 ? number_format($totalPayments / $totalCount, 2) : '0.00' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payments by Method</h6>
        </div>
        <div class="card-body p-0">
            @if ($paymentsByMethod->isEmpty())
                <div class="text-center text-muted py-4">No payments found</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Payment Method</th><th class="text-end">Amount</th><th class="text-end">Count</th><th class="text-end">% of Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($paymentsByMethod as $method)
                                <tr>
                                    <td class="fw-semibold">
                                        @php
                                            $badgeClass = match($method->payment_method) {
                                                'cash' => 'bg-success',
                                                'card' => 'bg-primary',
                                                'bank_transfer' => 'bg-info',
                                                'mobile_payment' => 'bg-warning text-dark',
                                                default => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} me-2">{{ ucfirst(str_replace('_', ' ', $method->payment_method)) }}</span>
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($method->total, 2) }}</td>
                                    <td class="text-end text-muted">{{ $method->count }}</td>
                                    <td class="text-end">{{ $totalPayments > 0 ? number_format(($method->total / $totalPayments) * 100, 1) : '0' }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="text-end fw-bold">{{ number_format($totalPayments, 2) }}</td>
                                <td class="text-end">{{ $totalCount }}</td>
                                <td class="text-end">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-auth-layout>
