@extends('layouts.app')

@section('title', 'Dashboard Warga')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Halo, {{ $user->name }}!</h1>
            <p class="text-muted mb-0">Rangkumlah sampah, tukarkan poinnya.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2"
                  title="Total {{ number_format($level['points'], 0, ',', '.') }} poin diraih seumur hidup">
                <i class="bi bi-{{ $level['icon'] }} me-1"></i>Level {{ $level['name'] }}
            </span>
            @if ($weeklyStreak >= 2)
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2"
                      title="Setoran selesai pada {{ $weeklyStreak }} minggu berturut-turut">
                    <i class="bi bi-fire me-1"></i>Streak {{ $weeklyStreak }} minggu
                </span>
            @endif
            <span class="badge bg-{{ $user->role->badgeClass() }} fs-6 px-3 py-2">
                <i class="bi bi-person-badge me-1"></i>{{ $user->role->label() }}
            </span>
        </div>
    </div>

    <div class="card app-card text-white bg-success mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-white-50 small mb-1">Saldo Poin Anda</div>
                    <div class="display-5 fw-bold"><span data-count-up="{{ $user->balance_points }}">{{ number_format($user->balance_points, 0, ',', '.') }}</span> <span class="h5 fw-semibold">poin</span></div>
                    <div class="mt-1 opacity-75">
                        <i class="bi bi-cash-coin me-1"></i>
                        Setara Rp {{ number_format($user->balance_points * $pointValue, 0, ',', '.') }}
                        <span class="small">(1 poin = Rp {{ number_format($pointValue, 0, ',', '.') }})</span>
                    </div>
                </div>
                <i class="bi bi-piggy-bank display-3 opacity-50 d-none d-sm-block"></i>
            </div>

            @if ($user->hasSavingsGoal())
                <hr class="border-white border-opacity-50 my-3">
                <div>
                    <div class="d-flex flex-wrap justify-content-between gap-2 small mb-1">
                        <span class="fw-semibold">
                            <i class="bi bi-flag-fill me-1"></i>Target "{{ $user->savings_goal_title }}"
                        </span>
                        <span>{{ $user->savingsGoalPercent() }}% dari {{ number_format($user->savings_goal_points, 0, ',', '.') }} poin</span>
                    </div>
                    <div class="progress bg-white bg-opacity-25" style="height: .65rem;">
                        <div class="progress-bar bg-warning {{ $user->savingsGoalPercent() >= 100 ? 'progress-bar-striped progress-bar-animated' : '' }}"
                             role="progressbar" aria-valuenow="{{ $user->savingsGoalPercent() }}"
                             aria-valuemin="0" aria-valuemax="100"
                             data-motion-width="{{ $user->savingsGoalPercent() }}" style="width: 0%;"></div>
                    </div>
                    <div class="small mt-2 opacity-75">
                        @if ($user->savingsGoalPercent() >= 100)
                            <i class="bi bi-trophy-fill me-1"></i>Target tercapai! Ajukan penarikan untuk menukarkannya.
                        @else
                            <i class="bi bi-flag me-1"></i>Sisa {{ number_format(max(0, $user->savings_goal_points - $user->balance_points), 0, ',', '.') }} poin lagi menuju target.
                        @endif
                    </div>
                </div>
            @else
                <hr class="border-white border-opacity-50 my-3">
                <a href="{{ route('profile.edit') }}#target-tabungan" class="text-white small text-decoration-none opacity-75 hover-lift-link d-inline-block">
                    <i class="bi bi-plus-circle me-1"></i>Atur target tabungan pertama Anda
                </a>
            @endif
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('deposits.create') }}" class="btn btn-success">
            <i class="bi bi-basket3 me-1"></i>Ajukan Setoran Mandiri
        </a>
        <a href="{{ route('withdrawals.create') }}" class="btn btn-outline-success">
            <i class="bi bi-cash-coin me-1"></i>Tarik Poin
        </a>
        <a href="{{ route('deposits.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>Riwayat Setoran
        </a>
        <a href="{{ route('withdrawals.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-cash-stack me-1"></i>Riwayat Penarikan
        </a>
        <a href="{{ route('points.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-journal-text me-1"></i>Riwayat Poin
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-trash3"></i></span>
                    <div>
                        <div class="text-muted small">Total Setoran</div>
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $totalDeposits }}">{{ number_format($totalDeposits, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-star-fill"></i></span>
                    <div>
                        <div class="text-muted small">Poin Diperoleh</div>
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $totalPoints }}">{{ number_format($totalPoints, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-info-subtle text-info"><i class="bi bi-cash-stack"></i></span>
                    <div>
                        <div class="text-muted small">Penarikan Aktif</div>
                        <div class="fs-4 fw-bold">
                            @if ($hasActiveWithdrawal)
                                <span class="badge bg-info">Sedang diproses</span>
                            @else
                                Tidak ada
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-graph-up-arrow me-1 text-success"></i>Poin 30 Hari Terakhir</h2>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" style="max-height: 260px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-pie-chart me-1 text-success"></i>Komposisi Kategori</h2>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" style="max-height: 260px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card app-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h6 fw-bold mb-0"><i class="bi bi-patch-check-fill me-1 text-success"></i>Lencana Saya</h2>
                <span class="badge bg-success-subtle text-success">
                    {{ collect($badges)->where('earned', true)->count() }} dari {{ count($badges) }} diraih
                </span>
            </div>
            <div class="d-flex flex-wrap gap-3">
                @foreach ($badges as $badge)
                    <div class="badge-orb-wrap text-center" title="{{ $badge['hint'] }}">
                        <span class="badge-orb {{ $badge['earned'] ? 'badge-earned' : 'badge-locked' }}">
                            <i class="bi bi-{{ $badge['icon'] }}"></i>
                        </span>
                        <div class="small mt-1 {{ $badge['earned'] ? 'fw-semibold' : 'text-body-secondary' }}">
                            {{ $badge['label'] }}
                        </div>
                    </div>
                @endforeach
                <div class="badge-orb-wrap text-center" title="Total {{ number_format($level['points'], 0, ',', '.') }} poin diraih seumur hidup">
                    <span class="badge-orb badge-level"><i class="bi bi-{{ $level['icon'] }}"></i></span>
                    <div class="small mt-1 fw-semibold">Level {{ $level['name'] }}</div>
                </div>
            </div>
            @if ($level['next_name'] !== null)
                <div class="mt-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Progres menuju level {{ $level['next_name'] }}</span>
                        <span>{{ $level['progress'] }}%</span>
                    </div>
                    <div class="progress" style="height: .5rem;">
                        <div class="progress-bar bg-success" role="progressbar"
                             aria-valuenow="{{ $level['progress'] }}" aria-valuemin="0" aria-valuemax="100"
                             data-motion-width="{{ $level['progress'] }}" style="width: 0%;"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card app-card">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-journal-text me-1 text-success"></i>5 Mutasi Poin Terakhir</h2>
                    <a href="{{ route('points.index') }}" class="small text-decoration-none">Lihat semua <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body p-0">
                    @if ($recentLedgers->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada mutasi poin. Setor sampah Anda untuk mulai mengumpulkan poin!
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Arah</th>
                                        <th>Jumlah</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Saldo Akhir</th>
                                        <th class="pe-4 text-end">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentLedgers as $ledger)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge bg-{{ $ledger->type->badgeClass() }}">
                                                    <i class="bi bi-{{ $ledger->type === \App\Enums\LedgerType::Credit ? 'plus-lg' : 'dash-lg' }} me-1"></i>
                                                    {{ $ledger->type->label() }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold {{ $ledger->type === \App\Enums\LedgerType::Credit ? 'text-success' : 'text-danger' }}">
                                                {{ $ledger->type->sign() > 0 ? '+' : '-' }}{{ number_format($ledger->amount, 0, ',', '.') }}
                                            </td>
                                            <td class="text-muted small">{{ $ledger->description ?? '-' }}</td>
                                            <td class="text-end">{{ number_format($ledger->balance_after, 0, ',', '.') }} poin</td>
                                            <td class="pe-4 text-end text-muted small">{{ $ledger->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            (function () {
                var textColor = getComputedStyle(document.body).color;
                var gridColor = 'rgba(128, 128, 128, 0.15)';

                new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: {
                        labels: @json($trendLabels),
                        datasets: [{
                            data: @json($trendData),
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.12)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 2
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { color: textColor, maxTicksLimit: 10 }, grid: { color: gridColor } },
                            y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } }
                        }
                    }
                });

                new Chart(document.getElementById('categoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($categoryLabels),
                        datasets: [{
                            data: @json($categoryData),
                            backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14', '#20c997', '#6610f2', '#d63384']
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { color: textColor, boxWidth: 14 } } }
                    }
                });
            })();
        </script>
    @endpush
@endsection
