import './bootstrap';
import Alpine from 'alpinejs';
import { registerSigap } from './sigap';
import { registerServiceWorker } from './sigap/pwa';

// Bootstrap JS & Chart.js dimuat sebagai script klasik dari public/vendor (lihat scripts/copy-vendor.mjs).
// Dropdown/tooltip memakai data-API Bootstrap (tooltip diinisialisasi di layouts/app).

// Cegah Alpine dimulai dua kali
if (!window.__alpine_was_already_initialized) {
    window.Alpine = Alpine;
    window.__alpine_was_already_initialized = true;
    registerSigap(Alpine);
    Alpine.start();
}

registerServiceWorker();

document.addEventListener('DOMContentLoaded', () => {
    // Pilih semua di tabel admin lama (.select-all-checkbox / .select-checkbox)
    const mainCheckbox = document.querySelector('.select-all-checkbox');
    if (mainCheckbox) {
        const checkboxes = () => document.querySelectorAll('.select-checkbox');
        mainCheckbox.addEventListener('change', () => {
            checkboxes().forEach((checkbox) => {
                checkbox.checked = mainCheckbox.checked;
            });
        });
        checkboxes().forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const list = Array.from(checkboxes());
                const all = list.every((c) => c.checked);
                const any = list.some((c) => c.checked);
                mainCheckbox.checked = all;
                mainCheckbox.indeterminate = any && !all;
            });
        });
    }
    // Konfirmasi [data-confirm] ditangani sheet design system (resources/js/sigap/confirm.js)
});
