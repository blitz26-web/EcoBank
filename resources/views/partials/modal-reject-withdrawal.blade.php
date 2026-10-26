<div class="modal fade" id="modal-tolak-penarikan" tabindex="-1" aria-labelledby="modal-tolak-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="form-tolak-penarikan" action="#" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modal-tolak-label">
                    <i class="bi bi-x-circle text-danger me-1"></i>Tolak Penarikan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">
                    Kode: <span class="fw-semibold" id="tolak-kode">-</span>
                </p>
                <p class="text-muted small">
                    Detail: <span id="tolak-info">-</span><br>
                    Poin yang ditahan <strong>akan dikembalikan otomatis</strong> ke saldo warga
                    beserta entri kredit di ledger (audit trail).
                </p>
                <label for="tolak-alasan" class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                <textarea class="form-control" id="tolak-alasan" name="reason" rows="3"
                          placeholder="Mis. Nomor rekening tidak sesuai data warga." required minlength="5" maxlength="255"></textarea>
                <div class="form-text">Alasan tersimpan di data penarikan & keterangan ledger pengembalian poin.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-x-circle me-1"></i>Tolak & Kembalikan Poin
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('modal-tolak-penarikan');

            modal.addEventListener('show.bs.modal', function (event) {
                var trigger = event.relatedTarget;
                if (!trigger) {
                    return;
                }
                document.getElementById('form-tolak-penarikan').action = trigger.getAttribute('data-action') || '#';
                document.getElementById('tolak-kode').textContent = trigger.getAttribute('data-code') || '-';
                document.getElementById('tolak-info').textContent = trigger.getAttribute('data-info') || '-';
            });
        });
    </script>
@endpush
