@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-bell me-2 text-success"></i>Notifikasi</h1>
            <p class="text-muted mb-0">
                @if ($unreadCount > 0)
                    {{ $unreadCount }} notifikasi belum dibaca.
                @else
                    Semua notifikasi sudah dibaca.
                @endif
            </p>
        </div>
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn btn-outline-success">
                    <i class="bi bi-check2-all me-1"></i>Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="card app-card">
        <div class="card-body p-0">
            @forelse ($notifications as $notification)
                @php $payload = $notification->data; @endphp
                <div class="d-flex gap-3 px-4 py-3 border-bottom {{ $notification->unread() ? 'bg-success bg-opacity-10' : '' }}">
                    <span class="notif-icon fs-5 text-{{ $notification->unread() ? ($payload['tone'] ?? 'secondary') : 'body-tertiary' }}">
                        <i class="bi bi-{{ $payload['icon'] ?? 'bell' }}"></i>
                    </span>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="fw-semibold">{{ $payload['title'] ?? 'Notifikasi' }}</span>
                            @if ($notification->unread())
                                <span class="badge bg-success">Baru</span>
                            @endif
                            <span class="small text-muted ms-auto">{{ $notification->created_at->translatedFormat('d M Y H:i') }}</span>
                        </div>
                        <p class="mb-1 small text-body-secondary">{{ $payload['message'] ?? '' }}</p>
                        <div class="d-flex gap-2">
                            @if ($notification->unread())
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none small text-success">
                                        Tandai dibaca
                                    </button>
                                </form>
                                <span class="text-body-tertiary small">|</span>
                            @endif
                            @if (! empty($payload['url']))
                                <a href="{{ $payload['url'] }}" class="btn btn-link btn-sm p-0 text-decoration-none small">
                                    Buka halaman terkait <i class="bi bi-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                    Belum ada notifikasi. Setor sampah atau ajukan penarikan untuk mulai menerima kabar.
                </div>
            @endforelse
        </div>
    </div>

    @if ($notifications->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
    @endif
@endsection
