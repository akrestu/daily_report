# SiGAP — Dokumentasi

**SiGAP** (Sistem Informasi Giat Aktivitas Pekerjaan) adalah sistem pelaporan aktivitas kerja harian berbasis web dengan alur approval berjenjang multi-level.

- **Versi**: 1.1.0
- **Framework**: Laravel 12 / PHP 8.2+
- **Tanggal Update Dokumentasi**: April 2026

---

## Tech Stack

| Kategori | Teknologi |
|----------|-----------|
| Backend | Laravel 12, PHP 8.2+, Livewire 3 |
| Database | MySQL 8.0+ / SQLite (dev) |
| Frontend | Bootstrap 5.3, Tailwind CSS 4, Alpine.js 3, Chart.js 4 |
| Build Tool | Vite 6 |
| Queue | Database (`database-uuids` driver) |
| Image Processing | Intervention Image v3 (GD) |
| Excel | Maatwebsite/Excel 3.1 |
| PWA | Workbox 7 (service worker) |
| AI Dev Tools | Laravel Boost 1.1 (MCP server) |

---

## Dokumentasi

### Untuk Developer

| Dokumen | Deskripsi |
|---------|-----------|
| [architecture.md](architecture.md) | Arsitektur sistem: role hierarchy (9 roles), approval workflow, data models, Laravel 12 patterns |
| [development_setup.md](development_setup.md) | Setup environment lokal: instalasi, konfigurasi, menjalankan server |
| [deployment.md](deployment.md) | Deployment produksi: Plesk, Nginx, queue worker, cron, PWA |
| [api_reference.md](api_reference.md) | Semua route dan endpoint: AJAX JSON API, file attachment, admin routes |
| [features.md](features.md) | Dokumentasi teknis fitur: dashboard, CRUD, attachment, approval, notifikasi, Excel |

### Untuk Pengguna

| Dokumen | Deskripsi |
|---------|-----------|
| [user_guide.md](user_guide.md) | Panduan penggunaan untuk semua role (Level 1–8 dan Admin) |
| [admin_guide.md](admin_guide.md) | Panduan khusus Administrator: kelola user, dept, cleanup laporan |
| [troubleshooting.md](troubleshooting.md) | Solusi masalah umum: notifikasi, upload, approval, PWA, performa |

---

## Quick Start

### Developer Baru

```bash
git clone <repo> && cd SiGAP
composer install && npm install
cp .env.example .env && php artisan key:generate
# Edit .env: DB_CONNECTION, DB_DATABASE, dll
php artisan migrate --seed
php artisan storage:link
composer run dev          # Start server + queue + Vite
```

Login: `admin@example.com` / `password`

Panduan lengkap: [development_setup.md](development_setup.md)

### Deploy ke Produksi

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan optimize
php artisan storage:link
php artisan queue:restart
```

Panduan lengkap: [deployment.md](deployment.md)

---

## Role System (Ringkasan)

SiGAP menggunakan **9 role** dalam hierarki:

```
Admin (sistem)
  └── Level 8 (approve Level 6 & 7, cross-dept)
        └── Level 7 (approve Level 6)
              └── Level 6 (approve Level 5)
                    └── Level 5 (approve Level 4)
                          └── Level 4 (approve Level 3)
                                └── Level 3 (approve Level 2)
                                      └── Level 2 (approve Level 1)
                                            └── Level 1 (buat laporan)
```

Detail lengkap: [architecture.md → Role Hierarchy](architecture.md#2-role-hierarchy-9-roles)

---

## Laravel Boost

`laravel/boost` terinstall sebagai dev dependency. Untuk mengaktifkan MCP server dengan tools AI (Search Docs, Database Schema, List Routes):

```bash
php artisan boost:install   # Setup pertama kali
php artisan boost:mcp       # Jalankan MCP server
```

Daftarkan di Claude Code atau editor Anda:
```json
{
    "mcpServers": {
        "laravel-boost": {
            "command": "php",
            "args": ["artisan", "boost:mcp"]
        }
    }
}
```
