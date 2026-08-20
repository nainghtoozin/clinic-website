{{-- Stock Movement Detail Modal (Alpine + Bootstrap styling, opened via window event) --}}
<div
    x-data="{
        open: false,
        movement: null,
        badges: {
            opening: 'bg-info',
            stock_in: 'bg-success',
            stock_out: 'bg-warning text-dark',
            adjustment: 'bg-secondary',
            dispensed: 'bg-primary',
            expired: 'bg-danger',
            default: 'bg-secondary'
        },
        show(detail) {
            this.movement = detail;
            this.open = true;
        },
        close() {
            this.open = false;
        },
        badge(key) {
            return this.badges[key] || this.badges['default'];
        }
    }"
    x-init="
        $watch('open', (val) => {
            if (val) document.body.classList.add('modal-open');
            else document.body.classList.remove('modal-open');
        });
    "
    @open-movement-detail.window="show($event.detail)"
    @keydown.escape.window="close()"
>
    <div x-show="open" x-cloak class="modal" tabindex="-1" role="dialog" aria-modal="true"
        :aria-hidden="open ? 'false' : 'true'" @click.self="close()">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Stock Movement Detail</h6>
                    <button type="button" class="btn-close" @click="close()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <template x-if="movement">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <label class="form-label text-muted small mb-1">Movement</label>
                                    <div>
                                        <span class="badge" :class="badge(movement.type_key)">
                                            <span class="status-dot"></span><span x-text="movement.type"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <label class="form-label text-muted small mb-1">Quantity</label>
                                    <div class="fs-4 fw-bold" :class="movement.quantity < 0 ? 'text-danger' : 'text-success'"
                                        x-text="movement.quantity_display"></div>
                                </div>
                            </div>

                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-0">Medicine</label>
                                    <div class="fw-semibold" x-text="movement.medicine"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-0">Batch / Lot</label>
                                    <div x-text="movement.batch || '—'"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-0">Before Quantity</label>
                                    <div class="fw-medium" x-text="movement.before ?? '—'"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-0">After Quantity</label>
                                    <div class="fw-medium" x-text="movement.after ?? '—'"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-0">Date</label>
                                    <div x-text="movement.date"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-0">Performed By</label>
                                    <div x-text="movement.by"></div>
                                </div>
                                <div class="col-12" x-show="movement.reference">
                                    <label class="form-label text-muted small mb-0">Reference</label>
                                    <div><span class="badge bg-light border text-dark" x-text="movement.reference"></span></div>
                                </div>
                            </div>

                            <div class="border-top pt-3 mt-2">
                                <div class="mb-2">
                                    <label class="form-label text-muted small mb-1">Reason</label>
                                    <div class="fw-medium" x-text="movement.reason || '—'"></div>
                                </div>
                                <div class="mb-0" x-show="movement.note">
                                    <label class="form-label text-muted small mb-1">Note</label>
                                    <div class="text-muted" x-text="movement.note"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" @click="close()">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div x-show="open" x-cloak class="modal-backdrop fade show"></div>
</div>

<style>[x-cloak] { display: none !important; }</style>
