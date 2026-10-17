{{-- Teruskan flash session (success / error / info) ke toast SiGAP. Taruh sekali di layout. --}}
@php
    $flashes = collect(['success' => 'success', 'error' => 'danger', 'info' => 'info'])
        ->filter(fn ($tone, $key) => session()->has($key))
        ->map(fn ($tone, $key) => [$tone, (string) session($key)])
        ->values();
@endphp
@if ($flashes->isNotEmpty())
    <script>window.__sgFlash = @json($flashes);</script>
@endif
