# SiGAP — Panduan Setup Development

> Untuk panduan deployment produksi, lihat [deployment.md](deployment.md)

---

## Daftar Isi

1. [Prerequisites](#1-prerequisites)
2. [Instalasi](#2-instalasi)
3. [Konfigurasi Environment](#3-konfigurasi-environment)
4. [Database Setup](#4-database-setup)
5. [Menjalankan Server Development](#5-menjalankan-server-development)
6. [Kredensial Test](#6-kredensial-test)
7. [Artisan Maintenance Commands](#7-artisan-maintenance-commands)
8. [Code Quality Tools](#8-code-quality-tools)
9. [Testing](#9-testing)

---

## 1. Prerequisites

### PHP & Ekstensi

- **PHP 8.2+** (wajib, Laravel 12 tidak mendukung PHP < 8.2)
- Ekstensi yang diperlukan:
  - `pdo` + `pdo_mysql` — koneksi database
  - `mbstring` — string multibyte (Bahasa Indonesia)
  - `gd` — **wajib** untuk kompresi gambar saat upload (Intervention Image v3)
  - `xml` — parsing XML
  - `zip` — export Excel (maatwebsite/excel)
  - `bcmath` — operasi numerik
  - `fileinfo` — validasi MIME type file upload

> Jika ekstensi `gd` tidak tersedia, upload gambar masih berjalan tetapi **tidak akan dikompresi** (file asli disimpan langsung). Warning akan muncul di `storage/logs/laravel.log`.

### Tools Lain

| Tool | Versi Minimum | Catatan |
|------|--------------|---------|
| Composer | 2.x | Package manager PHP |
| Node.js | 18+ | Untuk build frontend (Vite) |
| npm atau yarn | Terbaru | Package manager JS |
| MySQL | 8.0+ | Database produksi/dev |
| Git | Terbaru | Version control |

---

## 2. Instalasi

```bash
# 1. Clone repository
git clone <repository-url> SiGAP
cd SiGAP

# 2. Install PHP dependencies
composer install

# 3. Install JavaScript dependencies
npm install

# 4. Salin file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Buat symlink storage
php artisan storage:link
```

---

## 3. Konfigurasi Environment

Edit file `.env` dengan konfigurasi berikut:

```env
# App
APP_NAME="SiGAP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

# Database (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sigap
DB_USERNAME=root
DB_PASSWORD=

# Queue — wajib 'database' agar notifikasi berjalan
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_STORE=database

# Mail (development: log saja)
MAIL_MAILER=log
```

> **SQLite (opsional):** Untuk development cepat tanpa MySQL, set `DB_CONNECTION=sqlite`. Laravel akan membuat file `database/database.sqlite` secara otomatis.

---

## 4. Database Setup

```bash
# Jalankan semua migrasi + seeder
php artisan migrate --seed

# Atau fresh install (hapus semua data, mulai ulang)
php artisan migrate:fresh --seed

# Rollback satu batch terakhir
php artisan migrate:rollback
```

### Urutan Seeder

`DatabaseSeeder.php` menjalankan seeder dalam urutan:

1. `RolesSeeder` — 9 roles (admin, level1–level8)
2. `DepartmentsSeeder` — 5 departemen (IT, HR, Finance, Marketing, Operations)
3. `JobSiteSeeder` — 4 job site (Head Office Jakarta, Surabaya, Bandung, Medan)
4. `SectionSeeder` — Seksi untuk IT dan HR
5. `GenesisAdminSeeder` — Admin utama
6. `UsersSeeder` — User contoh per role per departemen

---

## 5. Menjalankan Server Development

### Cara Direkomendasikan (semua sekaligus)

```bash
composer run dev
```

Perintah ini menjalankan **3 proses concurrent** via `concurrently`:

| Proses | Perintah | Port | Fungsi |
|--------|----------|------|--------|
| Laravel | `php artisan serve` | 8000 | HTTP server aplikasi |
| Queue | `php artisan queue:listen --tries=1` | — | Proses job async & notifikasi |
| Vite | `npm run dev` | 5173 | HMR frontend assets |

> **Queue wajib berjalan** untuk notifikasi otomatis (observer DailyReport & JobComment mengirim notifikasi via queue).

### Cara Manual (jalankan terpisah)

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Queue worker
php artisan queue:listen --tries=1

# Terminal 3 — Vite HMR
npm run dev
```

### Windows (via batch script)

```bat
.\start-dev.bat
```

### Build Production Assets (tanpa HMR)

```bash
npm run build
```

Output: `public/build/` dengan asset yang di-hash.

---

## 6. Kredensial Test

Setelah `migrate --seed`:

| Email | Password | Role | Akses |
|-------|----------|------|-------|
| `admin@example.com` | `password` | Administrator | Full akses |

User lain dibuat per role dan per departemen dengan pola:
- Email: `{role_slug}@{dept_code}.example.com`
- Password: `password`

Contoh: `level1@it.example.com` / `password`

---

## 7. Artisan Maintenance Commands

### `reports:cleanup` — Hapus Laporan Lama

```bash
# Dry-run: lihat apa yang akan dihapus (tidak ada perubahan)
php artisan reports:cleanup --dry-run

# Hapus laporan completed/approved > 365 hari
php artisan reports:cleanup --days=365

# Hapus laporan dengan status tertentu
php artisan reports:cleanup --status=completed --status=approved

# Hapus tapi simpan file attachment
php artisan reports:cleanup --keep-attachments

# Kombinasi
php artisan reports:cleanup --days=180 --status=rejected --dry-run
```

### `notifications:cleanup` — Hapus Notifikasi Lama

```bash
# Dry-run
php artisan notifications:cleanup --dry-run

# Hapus notifikasi > 30 hari (default)
php artisan notifications:cleanup

# Hapus notifikasi > 7 hari
php artisan notifications:cleanup --days=7
```

### `cleanup:orphaned-profile-pictures` — Bersihkan Referensi Foto Profil Rusak

```bash
# Dry-run: lihat foto mana yang hilang dari storage
php artisan cleanup:orphaned-profile-pictures --dry-run

# Eksekusi: set profile_picture = null untuk entri yang file-nya hilang
php artisan cleanup:orphaned-profile-pictures
```

> Command ini TIDAK menghapus file, hanya membersihkan referensi database.

### `list:users` — Tampilkan Daftar User

```bash
php artisan list:users
# Output: tabel ID, Email, Name, Role, Department
```

---

## 8. Code Quality Tools

### Laravel Pint (Code Formatter)

```bash
# Format semua file PHP
./vendor/bin/pint

# Format file tertentu
./vendor/bin/pint app/Models/User.php

# Dry-run (tampilkan perubahan tanpa mengeksekusi)
./vendor/bin/pint --test
```

### Real-time Log Monitoring

```bash
php artisan pail
# Stream log Laravel secara real-time di terminal
```

### Route List

```bash
php artisan route:list
# Tampilkan semua route dengan method, URI, action
```

### Debug Endpoints (hanya development)

- `GET /debug/roles` — daftar semua role (auth required)
- `GET /debug/comments/{reportId}` — debug info komentar (auth required)
- `GET /test-create-user` — test buat user

> **Hapus atau amankan endpoint ini di produksi.**

---

## 9. Testing

```bash
# Jalankan semua test (akan clear config cache dulu)
composer run test

# Atau langsung via PHPUnit
php artisan test

# Test spesifik
php artisan test --filter=NamaTest

# Dengan coverage (butuh Xdebug)
php artisan test --coverage
```

### Konfigurasi Test

Test menggunakan `phpunit.xml` dengan:
- Database: SQLite in-memory (`:memory:`) agar cepat dan terisolasi
- Environment terpisah dari `.env` utama

### Catatan Laravel Boost

`laravel/boost` sudah terinstall sebagai dev dependency. Untuk mengaktifkan MCP tools (Database Schema, List Routes, Application Info, Search Docs):

```bash
# Instalasi guidelines dan konfigurasi MCP server
php artisan boost:install

# Jalankan MCP server (untuk IDE seperti Cursor, Claude Code)
php artisan boost:mcp
```

Setelah `boost:install`, direktori `.ai/guidelines/` akan dibuat dengan AI guidelines untuk Laravel 12, Livewire 3, dan package lainnya.
