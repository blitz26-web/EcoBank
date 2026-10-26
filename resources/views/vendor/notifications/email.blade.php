@component('mail::message')
@if (! empty($greeting))
    # {{ $greeting }}
@else
    @if ($level === 'error')
        # Ups!
    @else
        # Halo!
    @endif
@endif

@foreach ($introLines as $line)
    {{ $line }}

@endforeach

@isset($actionText)
    @php
        $color = $level === 'error' ? 'red' : ($level === 'success' ? 'green' : 'blue');
    @endphp
    @component('mail::button', ['url' => $actionUrl, 'color' => $color])
        {{ $actionText }}
    @endcomponent
@endisset

@foreach ($outroLines as $line)
    {{ $line }}

@endforeach

@isset($actionText)
    @slot('subcopy')
        Jika tombol "{{ $actionText }}" tidak bisa diklik, salin dan tempel URL
        di bawah ini ke peramban Anda:
        <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
    @endslot
@endisset

@endcomponent
