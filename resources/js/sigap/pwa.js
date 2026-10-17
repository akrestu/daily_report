// Service worker SiGAP: daftar sekali, tanpa muat ulang otomatis (bisa menghapus isian form yang sedang diketik).
// sw.js memakai skipWaiting + clientsClaim; saat versi baru mengambil alih halaman yang sudah dikontrol,
// pengguna mendapat toast "Muat ulang". Cache lama dibersihkan di sw.js (activate), bukan di sini.
import { Workbox } from 'workbox-window';

export function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) return;

    const wb = new Workbox('/sw.js');

    wb.addEventListener('controlling', (event) => {
        if (!event.isUpdate) return; // pemasangan pertama: tidak perlu memberi tahu
        window.sgToast?.('Versi baru SiGAP siap.', {
            tone: 'info',
            delay: 0,
            undoLabel: 'Muat ulang',
            undo: () => window.location.reload(),
        });
    });

    wb.register()
        .then((registration) => {
            // Cek pembaruan tiap jam selama tab terbuka
            setInterval(() => registration?.update(), 60 * 60 * 1000);
        })
        .catch(() => {
            /* SW gagal didaftarkan (mis. bukan HTTPS): aplikasi tetap jalan tanpa offline */
        });

    // Simpan prompt instal agar tombol "Pasang sekarang" di /install-app bisa memakainya
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        window.__sgInstallPrompt = event;
        document.dispatchEvent(new CustomEvent('sg-install-available'));
    });

    window.addEventListener('appinstalled', () => {
        window.__sgInstallPrompt = null;
        window.sgToast?.('SiGAP terpasang di perangkat Anda.');
    });
}

// Tampilkan prompt instal bawaan browser (Chrome/Edge Android & desktop). false bila tidak tersedia (mis. iOS).
export async function promptInstall() {
    const event = window.__sgInstallPrompt;
    if (!event) return false;
    event.prompt();
    const { outcome } = await event.userChoice;
    window.__sgInstallPrompt = null;
    return outcome === 'accepted';
}
