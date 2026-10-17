{{-- Instal Aplikasi (PWA). Tombol "Pasang sekarang" memakai prompt bawaan browser bila tersedia (window.sgInstall, sigap/pwa.js). --}}
<x-app-layout title="Instal Aplikasi">
    <div x-data="{
            can: !!window.__sgInstallPrompt,
            installed: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
            platform: /iphone|ipad|ipod/i.test(navigator.userAgent) ? 'ios' : (/android/i.test(navigator.userAgent) ? 'android' : 'desktop'),
            async install() { if (await window.sgInstall()) this.installed = true; this.can = false; },
         }" @sg-install-available.document="can = true">

        <section class="card overflow-hidden mb-3">
            <div class="row g-0">
                <div class="col-md-5 sg-install-hero">
                    <img src="{{ asset('images/brand/logo-sigap.png') }}" alt="" width="96" height="96">
                    <h1 class="h4 fw-bold mt-3 mb-1">SiGAP</h1>
                    <p class="mb-0 small">Laporan harian & persetujuan, langsung dari layar utama.</p>
                </div>
                <div class="col-md-7 p-3 p-lg-4">
                    <h2 class="h5 fw-bold mb-3">Kenapa dipasang?</h2>
                    <ul class="sg-install-list">
                        <li><i class="bi bi-lightning-charge" aria-hidden="true"></i><span><strong>Buka lebih cepat</strong><br><span class="small text-muted">Langsung dari ikon di layar utama, tanpa mengetik alamat.</span></span></li>
                        <li><i class="bi bi-wifi-off" aria-hidden="true"></i><span><strong>Tetap bisa melapor saat sinyal lemah</strong><br><span class="small text-muted">Laporan disimpan dan terkirim otomatis begitu online.</span></span></li>
                        <li><i class="bi bi-arrow-repeat" aria-hidden="true"></i><span><strong>Selalu versi terbaru</strong><br><span class="small text-muted">Tidak perlu memperbarui lewat Play Store / App Store.</span></span></li>
                    </ul>

                    <div class="sg-internal-note p-3 d-flex gap-2 align-items-center" x-show="installed" x-cloak>
                        <i class="bi bi-check-circle fs-5" aria-hidden="true"></i><span>SiGAP sudah terpasang di perangkat ini.</span>
                    </div>
                    <div x-show="!installed">
                        <button type="button" class="btn btn-primary w-100" x-show="can" x-cloak @click="install()">
                            <i class="bi bi-download" aria-hidden="true"></i> Pasang sekarang
                        </button>
                        <p class="small text-muted mb-0" x-show="!can">Ikuti langkah di bawah sesuai perangkat Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="card p-3 p-lg-4 mb-3" aria-labelledby="install-steps">
            <h2 id="install-steps" class="h5 fw-bold mb-3">Cara memasang</h2>
            <div class="sg-chip-row mb-3" role="tablist" aria-label="Perangkat">
                <button type="button" class="sg-chip" role="tab" :class="{ active: platform === 'android' }" :aria-selected="platform === 'android'" @click="platform = 'android'"><i class="bi bi-android2" aria-hidden="true"></i> Android</button>
                <button type="button" class="sg-chip" role="tab" :class="{ active: platform === 'ios' }" :aria-selected="platform === 'ios'" @click="platform = 'ios'"><i class="bi bi-apple" aria-hidden="true"></i> iPhone</button>
                <button type="button" class="sg-chip" role="tab" :class="{ active: platform === 'desktop' }" :aria-selected="platform === 'desktop'" @click="platform = 'desktop'"><i class="bi bi-laptop" aria-hidden="true"></i> Komputer</button>
            </div>
            <ol class="sg-install-steps" x-show="platform === 'android'">
                <li>Buka SiGAP di <strong>Chrome</strong>.</li>
                <li>Ketuk menu <i class="bi bi-three-dots-vertical" aria-label="titik tiga"></i> di pojok kanan atas.</li>
                <li>Pilih <strong>Instal aplikasi</strong> atau <strong>Tambahkan ke layar utama</strong>.</li>
                <li>Ketuk <strong>Instal</strong>. Ikon SiGAP muncul di layar utama.</li>
            </ol>
            <ol class="sg-install-steps" x-show="platform === 'ios'" x-cloak>
                <li>Buka SiGAP di <strong>Safari</strong> (bukan Chrome).</li>
                <li>Ketuk tombol <strong>Bagikan</strong> <i class="bi bi-box-arrow-up" aria-label="ikon bagikan"></i> di bagian bawah.</li>
                <li>Gulir dan pilih <strong>Tambah ke Layar Utama</strong>.</li>
                <li>Ketuk <strong>Tambah</strong> di pojok kanan atas.</li>
            </ol>
            <ol class="sg-install-steps" x-show="platform === 'desktop'" x-cloak>
                <li>Buka SiGAP di <strong>Chrome</strong> atau <strong>Edge</strong>.</li>
                <li>Klik ikon <strong>instal</strong> <i class="bi bi-window-plus" aria-label="ikon instal"></i> di ujung kanan bilah alamat.</li>
                <li>Klik <strong>Instal</strong>. SiGAP terbuka di jendela sendiri.</li>
            </ol>
        </section>

        <section class="card p-3 p-lg-4" aria-labelledby="install-faq">
            <h2 id="install-faq" class="h5 fw-bold mb-2">Pertanyaan umum</h2>
            @foreach ([
                ['Apakah memakan kuota?', 'Tampilan dan aset disimpan di perangkat, jadi pemakaian kuota lebih hemat. Data baru (laporan, notifikasi) tetap perlu internet.'],
                ['Bagaimana menghapusnya?', 'Android: tekan lama ikon SiGAP → Hapus instalan. iPhone: tekan lama ikon → Hapus App. Komputer: buka SiGAP → menu ⋮ → Uninstal.'],
                ['Apakah aman?', 'Aplikasi terpasang sama dengan situs SiGAP: memakai HTTPS dan akun yang sama. Tidak ada izin tambahan yang diminta.'],
            ] as [$q, $a])
                <details class="sg-faq">
                    <summary>{{ $q }}</summary>
                    <p class="small text-muted mb-0">{{ $a }}</p>
                </details>
            @endforeach
        </section>
    </div>
</x-app-layout>
