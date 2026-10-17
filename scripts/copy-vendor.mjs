// Salin library UMD ke public/vendor agar dimuat sebagai <script> klasik (self-host, tanpa CDN).
// Bootstrap JS & Chart.js harus sudah ada sebelum script inline halaman berjalan, jadi tidak lewat bundle module Vite.
import { copyFileSync, mkdirSync } from 'node:fs';
import { dirname } from 'node:path';

const files = {
    'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js': 'public/vendor/bootstrap/bootstrap.bundle.min.js',
    'node_modules/chart.js/dist/chart.umd.min.js': 'public/vendor/chart.js/chart.umd.min.js',
};

for (const [from, to] of Object.entries(files)) {
    mkdirSync(dirname(to), { recursive: true });
    copyFileSync(from, to);
    console.log(`vendor: ${to}`);
}
