@component('mail::message')
{{-- ============================================================= --}}
{{-- Template email notifikasi Kas Hijau (versi bahasa Indonesia) --}}
{{-- Menimpa template bawaan framework secara otomatis karena --}}
{{-- berada di resources/views/vendor/notifications/email.blade.php --}}
{{-- ============================================================= --}}

{{-- Sapaan --}}
@if (! empty($greeting))
    # {{ $greeting }}
@else
    @if ($level === 'error')
        # Ups!
    @else
        # Halo!
    @endif
@endif

{{-- Baris pembuka --}}
@foreach ($introLines as $line)
    {{ $line }}

@endforeach

{{-- Tombol aksi --}}
@isset($actionText)
    @php
        $color = $level === 'error' ? 'red' : ($level === 'success' ? 'green' : 'blue');
    @endphp
    @component('mail::button', ['url' => $actionUrl, 'color' => $color])
        {{ $actionText }}
    @endcomponent
@endisset

{{-- Baris penutup --}}
@foreach ($outroLines as $line)
    {{ $line }}

@endforeach

{{-- Bantuan bila tombol tidak bisa diklik --}}
@isset($actionText)
    @slot('subcopy')
        Jika tombol "{{ $actionText }}" tidak bisa diklik, salin dan tempel URL
        di bawah ini ke peramban Anda:
        <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
    @endslot
@endisset

@endcomponent
