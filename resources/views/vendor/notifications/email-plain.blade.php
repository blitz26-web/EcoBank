{{ $greeting ?? ($level === 'error' ? 'Ups!' : 'Halo!') }}

@foreach ($introLines as $line)
{{ $line }}
@endforeach

@isset($actionText)
{{ $actionText }}: {{ $actionUrl }}
@endisset

@foreach ($outroLines as $line)
{{ $line }}
@endforeach

@isset($actionText)
Jika tombol "{{ $actionText }}" tidak bisa diklik, salin dan tempel URL di bawah ini ke peramban Anda:

[{{ $displayableActionUrl }}]({{ $actionUrl }})
@endisset

Salam hangat,
{{ config('app.name') }}
