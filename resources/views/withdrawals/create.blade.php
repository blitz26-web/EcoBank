@extends('layouts.app')

@section('title', 'Ajukan Penarikan Poin')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Ajukan Penarikan Poin</h1>
            <p class="text-muted mb-0">Tukar poin Anda menjadi uang tunai atau transfer.</p>
        </div>
        <a href="{{ route('withdrawals.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Riwayat Penarikan
        </a>
    </div>

    @if ($hasActiveWithdrawal)
        <div class="alert alert-warning d-flex gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
            <div>
                Anda masih memiliki pengajuan penarikan dengan status <strong>Diajukan</strong>.
                Anda tetap boleh mengajukan lagi selama saldo mencukupi, tetapi mohon menunggu
                pengajuan sebelumnya diproses agar mudah ditelusuri.
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card app-card text-white bg-success mb-4 mb-lg-0">
                <div class="card-body p-4">
                    <div class="text-white-50 small mb-1">Saldo Poin Anda</div>
                    <div class="display-5 fw-bold">{{ number_format($balance, 0, ',', '.') }} <span class="h5 fw-semibold">poin</span></div>
                    <div class="mt-1 opacity-75">
                        <i class="bi bi-cash-coin me-1"></i>
                        Setara Rp {{ number_format($balance * $pointValue, 0, ',', '.') }}
                    </div>
                    <hr class="border-light opacity-25">
                    <ul class="list-unstyled small mb-0 opacity-75">
                        <li class="mb-1"><i class="bi bi-check2 me-1"></i>Minimal penarikan: {{ number_format($minPoints, 0, ',', '.') }} poin</li>
                        <li>
                            <i class="bi bi-check2 me-1"></i>Maksimal penarikan:
                            {{ $maxPoints > 0 ? number_format($maxPoints, 0, ',', '.') . ' poin' : 'tanpa batas' }}
                        </li>
                        <li><i class="bi bi-check2 me-1"></i>Poin ditahan (hold) sejak pengajuan</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <form method="POST" action="{{ route('withdrawals.store') }}" id="form-penarikan">
                @csrf

                <div class="card app-card mb-4">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="points-amount" class="form-label fw-semibold">
                                Jumlah Poin yang Ditarik <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-lg @error('points_amount') is-invalid @enderror"
                                   id="points-amount" name="points_amount"
                                   min="{{ $minPoints }}" @if ($maxPoints > 0) max="{{ $maxPoints }}" @endif
                                   step="1" value="{{ old('points_amount') }}" required>
                            @error('points_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-2 d-flex flex-wrap gap-2" id="chips-cepat">
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        data-fill="{{ $minPoints }}">Minimal ({{ number_format($minPoints, 0, ',', '.') }})</button>
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        data-fill="{{ intdiv($balance, 2) }}">Setengah Saldo</button>
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        data-fill="{{ $balance }}">Semua Saldo</button>
                            </div>
                        </div>

                        <div class="bg-light rounded-3 p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="text-muted small">Konversi (rate tetap dari server)</div>
                            <div class="fs-4 fw-bold text-success">
                                &asymp; Rp <span id="preview-rupiah">0</span>
                            </div>
                        </div>

                        <label class="form-label fw-semibold d-block">Metode Pembayaran <span class="text-danger">*</span></label>
                        <div class="row g-2 mb-3">
                            @foreach ($paymentLabels as $methodValue => $methodLabel)
                                <div class="col-12 col-md-4">
                                    <input type="radio" class="btn-check js-payment" name="payment_method"
                                           id="pm-{{ $methodValue }}" value="{{ $methodValue }}"
                                           @checked(old('payment_method') === $methodValue) required>
                                    <label class="btn btn-outline-success w-100 p-3" for="pm-{{ $methodValue }}">
                                        <i class="bi {{ match ($methodValue) {
                                            \App\Models\Withdrawal::PAYMENT_BANK_TRANSFER => 'bi-bank',
                                            \App\Models\Withdrawal::PAYMENT_E_WALLET => 'bi-phone',
                                            default => 'bi-cash-coin',
                                        } }} fs-4 d-block mb-1"></i>
                                        {{ $methodLabel }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('payment_method')
                            <div class="text-danger small mb-3">{{ $message }}</div>
                        @enderror

                        <div class="mb-1" id="wrap-account-number">
                            <label for="account-number" class="form-label fw-semibold">
                                <span id="label-account-number">Nomor Rekening / E-Wallet</span> <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                   id="account-number" name="account_number" maxlength="50"
                                   placeholder="Mis. 1234567890 a.n. Nama Anda"
                                   value="{{ old('account_number') }}">
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="hint-account-number">
                                Pastikan nama pemilik rekening/e-wallet sesuai data diri Anda.
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-body border-0 rounded-bottom-4 px-4 pb-4">
                        <button type="submit" class="btn btn-success btn-lg w-100" id="btn-submit">
                            <i class="bi bi-send me-1"></i>Kirim Pengajuan Penarikan
                        </button>
                        <div class="form-text mt-2 text-center">
                            Saldo dipotong saat pengajuan (pola hold). Bila pengajuan ditolak petugas,
                            poin otomatis dikembalikan beserta catatan ledger.
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var pointValue = {{ (int) $pointValue }};
            var minPoints = {{ (int) $minPoints }};
            var balance = {{ (int) $balance }};
            var form = document.getElementById('form-penarikan');
            var pointsInput = document.getElementById('points-amount');
            var preview = document.getElementById('preview-rupiah');
            var accountWrap = document.getElementById('wrap-account-number');
            var accountInput = document.getElementById('account-number');
            var accountLabel = document.getElementById('label-account-number');
            var cashValue = '{{ \App\Models\Withdrawal::PAYMENT_CASH }}';

            function formatNumber(value) {
                return Number(value).toLocaleString('id-ID');
            }

            function selectedPayment() {
                var checked = document.querySelector('.js-payment:checked');
                return checked ? checked.value : null;
            }

            function recalc() {
                var points = parseInt(pointsInput.value, 10) || 0;
                preview.textContent = formatNumber(points * pointValue);

                var isCash = selectedPayment() === cashValue;
                accountWrap.classList.toggle('d-none', isCash);
                accountInput.required = !isCash;

                if (!isCash) {
                    accountLabel.textContent = selectedPayment() === 'e_wallet'
                        ? 'Nomor E-Wallet'
                        : 'Nomor Rekening Bank';
                }
            }

            function belowMinimum() {
                return parseInt(pointsInput.value, 10) > 0 && parseInt(pointsInput.value, 10) < minPoints;
            }

            pointsInput.addEventListener('input', recalc);

            document.querySelectorAll('.js-payment').forEach(function (radio) {
                radio.addEventListener('change', recalc);
            });

            document.querySelectorAll('#chips-cepat [data-fill]').forEach(function (chip) {
                chip.addEventListener('click', function () {
                    pointsInput.value = chip.getAttribute('data-fill');
                    recalc();
                });
            });

            form.addEventListener('submit', function (event) {
                if (belowMinimum()) {
                    event.preventDefault();
                    alert('Jumlah poin di bawah batas minimal penarikan (' + formatNumber(minPoints) + ' poin).');
                    pointsInput.focus();
                }
            });

            recalc();
        });
    </script>
@endpush
