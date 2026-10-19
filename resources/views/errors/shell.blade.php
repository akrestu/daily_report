{{--
    Kerangka halaman error (dipakai errors/{kode}.blade.php, 4xx, 5xx).
    Sengaja minim dependensi: tanpa route(), DB, Alpine, atau sesi — tetap tampil walau aplikasi sedang error.
    Variabel: $code, $title, $message, $icon (Bootstrap Icons), $tone (danger|pending|progress|closed),
              $retry (tampilkan "Muat ulang"), $detail (pesan khusus dari abort(), opsional)
--}}
@php
    $tone ??= 'closed';
    $retry ??= false;
    $detail ??= null;
    try {
        $signedIn = auth()->check();
    } catch (\Throwable $e) {
        $signedIn = false;
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex">
    @include('partials.theme-init')
    <title>{{ $code }} · {{ $title }} — SiGAP</title>
    <link rel="icon" href="{{ asset('favicon.ico?v=6') }}" sizes="48x48">
    <meta name="theme-color" content="#F8FAFC">
    <script>
        (function () {
            var meta = document.querySelector('meta[name="theme-color"]');
            if (document.documentElement.getAttribute('data-bs-theme') === 'dark') meta.content = '#0B1120';
        })();
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="sg-error">
    <main class="sg-error__wrap" role="main" aria-labelledby="sg-error-title">
        <a href="{{ url('/') }}" class="sg-error__brand" aria-label="SiGAP — ke beranda">
            <img src="{{ asset('images/brand/logo-sigap.png') }}" alt="" width="36" height="36">
            <strong>SiGAP</strong>
        </a>

        <section class="sg-error__card">
            <div class="sg-error__icon sg-error__icon--{{ $tone }}" aria-hidden="true"><i class="bi {{ $icon }}"></i></div>
            <p class="sg-error__code">Kode {{ $code }}</p>
            <h1 id="sg-error-title" class="sg-error__title">{{ $title }}</h1>
            <p class="sg-error__text">{{ $message }}</p>
            @if ($detail)
                <p class="sg-error__detail">{{ $detail }}</p>
            @endif

            <div class="sg-error__actions">
                @if ($retry)
                    <a href="{{ url()->current() }}" class="btn btn-primary"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Muat ulang</a>
                @endif
                @if ($code == 401)
                    <a href="{{ url('/login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Masuk</a>
                @endif
                <a href="{{ url($signedIn ? '/dashboard' : '/') }}" @class(['btn', 'btn-primary' => ! $retry && $code != 401, 'btn-outline-secondary' => $retry || $code == 401])>
                    <i class="bi bi-house" aria-hidden="true"></i> {{ $signedIn ? 'Ke Beranda' : 'Ke Halaman Utama' }}
                </a>
                <a href="javascript:history.back()" class="btn btn-quiet"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali</a>
            </div>
        </section>

        <p class="sg-error__help">Butuh bantuan? <a href="{{ url('/request') }}">Ajukan tiket ke tim IT</a> dan sebutkan kode {{ $code }}.</p>
    </main>
</body>
</html>
