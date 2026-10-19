<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('partials.theme-init')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Request Bantuan') — SiGAP</title>

    <link rel="icon" href="{{ asset('favicon.ico?v=6') }}" sizes="48x48">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png?v=6') }}">
    {{-- Ikon PWA di /images/pwa (bukan /icons: alias bawaan Apache/LiteSpeed → 404 di server) --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/pwa/apple-touch-icon.png?v=6') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest?v=6') }}">
    <meta name="theme-color" content="#111111">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="sg-public">
    {{-- Tiket tanpa login — mockup: TicketFormStep1, TicketFormMobile, TicketFormDesktop, TicketSuccessMobile, TicketTrackMobile --}}
    <header class="sg-public__header">
        <a href="{{ route('tickets.public.form') }}" class="sg-public__brand">
            <img src="{{ asset('images/brand/logo-sigap.png') }}" alt="" width="36" height="36">
            <span><strong>SiGAP</strong><small>Helpdesk IT</small></span>
        </a>
        <nav class="sg-public__nav" aria-label="Tiket">
            <a href="{{ route('tickets.public.form') }}" @class(['active' => request()->routeIs('tickets.public.form')])>Ajukan</a>
            <a href="{{ route('tickets.public.track') }}" @class(['active' => request()->routeIs('tickets.public.track*')])>Lacak</a>
            <a href="{{ route('login') }}">Masuk</a>
        </nav>
    </header>

    <main class="sg-public__main">
        <div class="sg-public__card">
            @yield('content')
        </div>
        <p class="sg-public__foot">&copy; {{ date('Y') }} SiGAP · Smart Integrated Gateway for Activity &amp; Performance</p>
    </main>

    <x-sg.flash />
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
