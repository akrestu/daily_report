@php
    $user = auth()->user();
    $nav = new \App\Support\Navigation($user);
    $groups = $nav->groups();
    $bottom = $nav->bottomNav();

    // Judul halaman: prop :title, atau teks <h1>/<h2> pertama dari slot header lama
    $headerHtml = isset($header) ? (string) $header : '';
    preg_match('/<h[12][^>]*>(.*?)<\/h[12]>/s', $headerHtml, $match);
    $pageTitle = $attributes->get('title')
        ?? (trim(preg_replace('/\s+/', ' ', strip_tags($match[1] ?? $headerHtml))) ?: 'SiGAP');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @include('partials.theme-init')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle === 'SiGAP' ? 'SiGAP' : $pageTitle.' — SiGAP' }}</title>

    <link rel="icon" href="{{ asset('favicon.ico?v=6') }}" sizes="48x48">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png?v=6') }}">
    {{-- Ikon PWA di /images/pwa (bukan /icons: alias bawaan Apache/LiteSpeed → 404 di server) --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/pwa/apple-touch-icon.png?v=6') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest?v=6') }}">
    <meta name="theme-color" content="#111111">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SiGAP">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        window.__sgRoutes = {
            notifList: @js(route('notifications.get')),
            notifRead: @js(route('notifications.mark-read')),
            notifReadAll: @js(route('notifications.mark-all-read')),
            notifClear: @js(route('notifications.clear-all')),
            pending: @js(route('daily-reports.pending')),
        };
        window.__sgUserId = @js(auth()->id());
    </script>
    @stack('styles')
</head>
<body class="sg-app">
    <a href="#main" class="sg-skip">Lewati ke konten</a>

    {{-- Sidebar (desktop) / navigation rail (tablet) --}}
    <aside class="sg-sidebar" aria-label="Menu utama">
        <div class="sg-sidebar__brand">
            <a href="{{ route('dashboard') }}" aria-label="SiGAP — Beranda"><img src="{{ asset('images/brand/logo-sigap.png') }}" alt=""></a>
            <a href="{{ route('dashboard') }}" class="sg-sidebar__name">SiGAP</a>
            <button type="button" class="sg-sidebar__toggle d-none d-lg-inline-flex" onclick="sgToggleSidebar()" aria-label="Ciutkan atau lebarkan menu">
                <i class="bi bi-layout-sidebar" aria-hidden="true"></i>
            </button>
        </div>
        @if ($bottom['fab']['key'] !== 'home')
            <a href="{{ $bottom['fab']['url'] }}" class="sg-sidebar__fab" aria-label="{{ $bottom['fab']['label'] }}"><i class="bi bi-plus-lg" aria-hidden="true"></i></a>
        @endif
        <nav class="sg-sidebar__nav">
            @foreach ($groups as $group)
                @if ($group['label'])
                    <div class="sg-sidebar__group">{{ $group['label'] }}</div>
                @endif
                @foreach ($group['items'] as $item)
                    <a href="{{ $item['url'] }}" @class(['sg-nav-item', 'active' => $item['active']]) @if ($item['active']) aria-current="page" @endif title="{{ $item['label'] }}">
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                        <span class="sg-nav-item__label">{{ $item['label'] }}</span>
                        @if ($item['badge'])
                            <span class="sg-count">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>
        <a href="{{ route('profile.edit') }}" class="sg-sidebar__user text-reset text-decoration-none" title="Profil">
            <x-sg.avatar :user="$user" :size="36" />
            <div class="sg-sidebar__user-meta min-w-0">
                <div class="sg-sidebar__user-name">{{ $user->name }}</div>
                <div class="small text-muted sg-clamp-1">
                    {{ $user->isAdmin() ? 'Admin' : 'Level '.$user->getRoleLevel() }}{{ $user->department ? ' · '.$user->department->name : '' }}
                </div>
            </div>
        </a>
    </aside>

    <div class="sg-main">
        {{-- App bar (mobile & tablet) --}}
        <header class="sg-appbar">
            @if ($attributes->get('back'))
                <a href="{{ $attributes->get('back') }}" class="sg-icon-btn ms-n2" aria-label="Kembali"><i class="bi bi-arrow-left" aria-hidden="true"></i></a>
            @endif
            <h1 class="sg-appbar__title">{{ $pageTitle }}</h1>
            <button type="button" class="sg-icon-btn" data-bs-toggle="modal" data-bs-target="#sg-search" aria-label="Cari">
                <i class="bi bi-search" aria-hidden="true"></i>
            </button>
            <a href="{{ route('notifications.view-all') }}" class="sg-icon-btn" x-data
               :aria-label="'Notifikasi' + ($store.notif.unread ? ', ' + $store.notif.unread + ' belum dibaca' : '')" aria-label="Notifikasi">
                <i class="bi bi-bell" aria-hidden="true"></i>
                <span class="sg-count" x-show="$store.notif.unread" x-text="$store.notif.badge" x-cloak></span>
            </a>
            <a href="{{ route('profile.edit') }}" class="sg-icon-btn">
                <span class="visually-hidden">Profil</span>
                <x-sg.avatar :user="$user" :size="32" />
            </a>
        </header>

        {{-- Topbar (desktop) --}}
        <header class="sg-topbar">
            <nav class="sg-topbar__crumbs" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}">SiGAP</a>
                <i class="bi bi-chevron-right small" aria-hidden="true"></i>
                <strong aria-current="page">{{ $pageTitle }}</strong>
            </nav>

            <button type="button" class="sg-search-trigger" data-bs-toggle="modal" data-bs-target="#sg-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span class="me-auto">Cari…</span>
                <kbd>Ctrl K</kbd>
            </button>

            <div class="dropdown" x-data>
                <button type="button" class="sg-icon-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                        :aria-label="'Notifikasi' + ($store.notif.unread ? ', ' + $store.notif.unread + ' belum dibaca' : '')" aria-label="Notifikasi">
                    <i class="bi bi-bell" aria-hidden="true"></i>
                    <span class="sg-count" x-show="$store.notif.unread" x-text="$store.notif.badge" x-cloak></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end sg-notif-menu shadow">
                    <div class="sg-notif-menu__head">
                        <strong class="me-auto">Notifikasi</strong>
                        <button type="button" class="btn btn-sm btn-quiet" @click="$store.notif.markAll()" :disabled="!$store.notif.unread">Tandai semua dibaca</button>
                        <button type="button" class="sg-icon-btn" style="width:36px;height:36px;font-size:16px" data-bs-toggle="modal" data-bs-target="#sg-notif-clear"
                                aria-label="Hapus semua notifikasi" x-show="$store.notif.items.length" x-cloak>
                            <i class="bi bi-trash3" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="sg-notif-menu__list">
                        <template x-if="!$store.notif.loaded"><div class="p-3"><x-sg.skeleton :count="2" /></div></template>
                        <template x-if="$store.notif.loaded && !$store.notif.items.length">
                            <x-sg.empty icon="bi-bell-slash" title="Belum ada notifikasi" text="Kami akan memberi tahu saat ada laporan atau tiket yang perlu Anda lihat." />
                        </template>
                        <template x-for="n in $store.notif.items" :key="n.id">
                            <button type="button" class="sg-notif" :class="{ 'is-unread': !n.is_read }" @click="$store.notif.open(n)">
                                <span class="sg-notif__icon" :class="'sg-notif__icon--' + $store.notif.tone(n)"><i class="bi" :class="$store.notif.icon(n)" aria-hidden="true"></i></span>
                                <span class="min-w-0">
                                    <span class="d-block" x-text="n.message"></span>
                                    <span class="sg-notif__time" x-text="$store.notif.ago(n.created_at)"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                    <a href="{{ route('notifications.view-all') }}" class="sg-notif-menu__foot">Lihat semua notifikasi</a>
                </div>
            </div>

            <div class="dropdown" x-data>
                <button type="button" class="sg-user-btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <x-sg.avatar :user="$user" :size="32" />
                    <span class="text-truncate" style="max-width:160px">{{ $user->name }}</span>
                    <x-sg.badge-level :user="$user" short />
                    <i class="bi bi-chevron-down small text-muted" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2" aria-hidden="true"></i>Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Tema</h6></li>
                    @foreach (['light' => ['Terang', 'bi-sun'], 'dark' => ['Gelap', 'bi-moon'], 'system' => ['Ikuti sistem', 'bi-circle-half']] as $mode => [$label, $icon])
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center" @click="$store.theme.set('{{ $mode }}')">
                                <i class="bi {{ $icon }} me-2" aria-hidden="true"></i>{{ $label }}
                                <i class="bi bi-check2 ms-auto" x-show="$store.theme.mode === '{{ $mode }}'" aria-hidden="true"></i>
                            </button>
                        </li>
                    @endforeach
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main id="main" class="sg-content" tabindex="-1">
            @isset($header)
                <div class="sg-legacy-header">{{ $header }}</div>
            @endisset
            {{ $slot }}
        </main>

        <footer class="sg-footer">SiGAP · Smart Integrated Gateway for Activity &amp; Performance</footer>
    </div>

    {{-- Bottom nav (mobile) --}}
    <nav class="sg-bottom-nav" aria-label="Navigasi utama">
        @foreach (['home' => 'Beranda', 'reports' => null] as $slotKey => $short)
            @php($item = $bottom[$slotKey])
            <a href="{{ $item['url'] }}" @class(['sg-bottom-nav__item', 'active' => $item['active']]) @if ($item['active']) aria-current="page" @endif>
                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>{{ $short ?? \Illuminate\Support\Str::before($item['label'], ' ') }}
                @if ($item['badge'])<span class="sg-count">{{ $item['badge'] }}</span>@endif
            </a>
        @endforeach
        <div class="sg-bottom-nav__fab-wrap">
            <a href="{{ $bottom['fab']['url'] }}" class="sg-fab" aria-label="{{ $bottom['fab']['label'] }}">
                <i class="bi {{ $bottom['fab']['key'] === 'create' ? 'bi-plus-lg' : $bottom['fab']['icon'] }}" aria-hidden="true"></i>
            </a>
        </div>
        @if ($bottom['tickets'])
            <a href="{{ $bottom['tickets']['url'] }}" @class(['sg-bottom-nav__item', 'active' => $bottom['tickets']['active']]) @if ($bottom['tickets']['active']) aria-current="page" @endif>
                <i class="bi bi-ticket-perforated" aria-hidden="true"></i>Tiket
                @if ($bottom['tickets']['badge'])<span class="sg-count">{{ $bottom['tickets']['badge'] }}</span>@endif
            </a>
        @endif
        <button type="button" @class(['sg-bottom-nav__item', 'active' => $bottom['moreActive']]) data-bs-toggle="modal" data-bs-target="#sg-more">
            <i class="bi bi-grid" aria-hidden="true"></i>Lainnya
        </button>
    </nav>

    <x-sg.sheet id="sg-more" title="Menu lainnya">
        @foreach ($bottom['more'] as $group)
            @if ($group['label'])
                <p class="sg-section-label mt-2">{{ $group['label'] }}</p>
            @endif
            <div class="sg-more-grid mb-3">
                @foreach ($group['items'] as $item)
                    <a href="{{ $item['url'] }}" @class(['sg-more-item', 'active' => $item['active']])>
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}
                        @if ($item['badge'])<span class="sg-count">{{ $item['badge'] }}</span>@endif
                    </a>
                @endforeach
            </div>
        @endforeach
        <p class="sg-section-label mt-2">Akun</p>
        <div class="sg-more-grid" x-data>
            <a href="{{ route('profile.edit') }}" class="sg-more-item"><i class="bi bi-person" aria-hidden="true"></i>Profil</a>
            <a href="{{ route('pwa.install') }}" class="sg-more-item"><i class="bi bi-phone" aria-hidden="true"></i>Instal Aplikasi</a>
            <button type="button" class="sg-more-item border-0"
                    @click="$store.theme.set(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark')">
                <i class="bi bi-circle-half" aria-hidden="true"></i>Ganti Tema
            </button>
            <form method="POST" action="{{ route('logout') }}" style="display:contents">
                @csrf
                <button type="submit" class="sg-more-item border-0 w-100 text-danger"><i class="bi bi-box-arrow-right" aria-hidden="true"></i>Keluar</button>
            </form>
        </div>
    </x-sg.sheet>

    <x-sg.search-palette />

    <x-sg.sheet id="sg-notif-clear" title="Hapus semua notifikasi?">
        <p class="mb-0">Semua notifikasi akan dihapus dan tidak dapat dikembalikan.</p>
        <x-slot:footer>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-reject" data-bs-dismiss="modal" x-data @click="$store.notif.clearAll()">Hapus semua</button>
        </x-slot:footer>
    </x-sg.sheet>

    <x-sg.flash />

    {{-- Library UMD self-host (scripts/copy-vendor.mjs): harus sudah ada sebelum script halaman.
         Chart.js (70 KB gzip) hanya dimuat halaman yang memakainya: @push('scripts') di dashboard & tickets/dashboard. --}}
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
    </script>
    @stack('scripts')
</body>
</html>
