# SiGAP — Panduan Administrator

> Panduan ini khusus untuk pengguna dengan role **Administrator**. Untuk fitur umum, lihat [user_guide.md](user_guide.md).

---

## Daftar Isi

1. [Panel Admin](#1-panel-admin)
2. [Manajemen User](#2-manajemen-user)
3. [Manajemen Departemen](#3-manajemen-departemen)
4. [Manajemen Job Site](#4-manajemen-job-site)
5. [Manajemen Seksi (Section)](#5-manajemen-seksi-section)
6. [Melihat Semua Laporan](#6-melihat-semua-laporan)
7. [Report Cleanup](#7-report-cleanup)
8. [Maintenance Commands](#8-maintenance-commands)
9. [Pengaturan Sistem](#9-pengaturan-sistem)

---

## 1. Panel Admin

Akses panel admin via menu **Admin** di sidebar (hanya terlihat jika Anda adalah Administrator).

URL langsung: `/admin/dashboard`

### Middleware Proteksi

Semua route `/admin/*` dilindungi oleh middleware `admin.only` (`app/Http/Middleware/AdminOnly.php`). Jika non-admin mencoba mengakses, akan diredirect ke dashboard dengan pesan error.

### Menu Admin

| Menu | URL | Fungsi |
|------|-----|--------|
| Dashboard | `/admin/dashboard` | Statistik sistem |
| Kelola User | `/admin/users` | CRUD user |
| Kelola Departemen | `/admin/departments` | CRUD departemen |
| Job Sites | `/admin/job-sites` | CRUD lokasi kerja |
| Seksi | `/admin/sections` | CRUD seksi departemen |
| Semua Laporan | `/admin/reports` | View semua laporan |
| Cleanup Laporan | `/admin/reports/cleanup` | Hapus laporan lama |
| Pengaturan | `/admin/settings` | Konfigurasi sistem |

---

## 2. Manajemen User

### Membuat User Baru

1. Buka `/admin/users/create`
2. Isi form:

| Field | Wajib | Keterangan |
|-------|-------|------------|
| Nama | ✓ | Nama lengkap |
| Email | — | Nullable, harus unik jika diisi |
| User ID | — | ID karyawan (misal: EMP001). Auto-generate dari nama jika kosong |
| Password | — | Default: `password123` jika kosong |
| Role | ✓ | Salah satu dari 9 role tersedia |
| Departemen | — | Sangat penting untuk routing laporan & approval |
| Job Site | — | Penting untuk Level 8 (cross-department) |
| Email Verified | — | Centang jika email sudah terverifikasi |

3. Klik **Simpan**

### 9 Role yang Tersedia

| Role | Slug | Fungsi Utama |
|------|------|--------------|
| Administrator | `admin` | Full akses sistem |
| Level 1 | `level1` | Pembuat laporan, tidak bisa approve |
| Level 2 | `level2` | Approve Level 1 |
| Level 3 | `level3` | Approve Level 2 |
| Level 4 | `level4` | Approve Level 3 |
| Level 5 | `level5` | Approve Level 4, monitoring |
| Level 6 | `level6` | Approve Level 5, management |
| Level 7 | `level7` | Approve Level 6, kepala dept |
| Level 8 | `level8` | Approve Level 6 & 7, cross-dept, tidak bisa buat laporan |

### Pentingnya Assignment Departemen & Job Site

- **Departemen**: menentukan laporan mana yang bisa dilihat/diapprove user
- **Job Site**: wajib diisi untuk Level 8 agar bisa approve lintas departemen
- Tanpa departemen, user Level 2–7 tidak bisa melihat laporan siapapun

### Import User dari Excel

1. Download template: `/admin/users/export-template`
2. Isi template (kolom: name, email, user_id, role, department, password, email_verified)
3. Upload di `/admin/users/import`

**Catatan template:**
- Kolom `role`: isi dengan nama role (misal: `Level 1`, `Level 2`, `Administrator`)
- Kolom `department`: isi dengan nama departemen yang ada di sistem
- Kolom `email_verified`: isi `Yes` untuk langsung verified
- Kolom `password`: isi atau kosongkan (default: `password123`)

### Export User

Klik **Export** di `/admin/users` untuk download Excel berisi semua user dengan data lengkap.

### Batch Delete User

1. Centang user yang ingin dihapus
2. Klik **Hapus Terpilih**
3. Konfirmasi

> Perhatian: menghapus user juga berdampak pada laporan yang dibuat user tersebut (field `user_id` menjadi null atau cascade tergantung constraint).

---

## 3. Manajemen Departemen

### Membuat Departemen

1. Buka `/admin/departments/create`
2. Isi:
   - **Nama** (wajib): nama departemen (misal: IT, HR, Finance)
   - **Kode** (wajib): kode singkat unik (misal: IT, HR, FIN)
   - **Deskripsi** (opsional): penjelasan departemen

### Melihat Detail Departemen

Halaman `/admin/departments/{id}` menampilkan:
- Info departemen
- Daftar user dalam departemen
- Daftar seksi dalam departemen

### Batch Delete Departemen

Centang departemen → klik **Hapus Terpilih**. Pastikan tidak ada user/laporan aktif yang terhubung sebelum menghapus.

---

## 4. Manajemen Job Site

Job site adalah lokasi fisik tempat pekerjaan berlangsung. Digunakan untuk routing approval Level 8 (lintas departemen).

### Field Job Site

| Field | Wajib | Keterangan |
|-------|-------|------------|
| Nama | ✓ | Nama lokasi (misal: Head Office Jakarta) |
| Kode | ✓ | Kode unik (misal: HO-JKT) |
| Deskripsi | — | Penjelasan lokasi |
| Lokasi | — | Alamat atau koordinat |
| Status Aktif | ✓ | Hanya job site aktif yang muncul di dropdown laporan |

### Menonaktifkan Job Site

Toggle field **Status Aktif** ke nonaktif. Job site tidak akan muncul di form laporan baru, tapi data laporan lama tetap tersimpan.

---

## 5. Manajemen Seksi (Section)

Seksi adalah subdivisi dari departemen. Digunakan sebagai filter opsional pada laporan.

### Membuat Seksi

1. Buka `/admin/sections/create`
2. Isi:
   - **Departemen**: pilih departemen induk
   - **Nama Seksi**: nama subdivisi
   - **Kode**: kode unik dalam departemen
   - **Status Aktif**: aktif/nonaktif

> Seksi hanya bisa dipilih di form laporan jika departemen yang sama sudah dipilih (cascade dropdown via AJAX ke `/sections/by-department`).

---

## 6. Melihat Semua Laporan

Admin dapat melihat semua laporan dari semua departemen di `/admin/reports`.

Tersedia fitur:
- Filter berdasarkan status, departemen, tanggal
- Pencarian nama pekerjaan
- Melihat detail laporan
- Meng-approve/menolak laporan manapun

---

## 7. Report Cleanup

Fitur untuk menghapus laporan lama secara bulk. Akses di `/admin/reports/cleanup`.

### Langkah Cleanup via Web UI

1. Buka `/admin/reports/cleanup`
2. Statistik halaman menampilkan:
   - Total laporan saat ini
   - Estimasi storage yang digunakan
   - Laporan per status
3. Atur parameter cleanup:
   - **Hari**: hapus laporan lebih tua dari N hari
   - **Status**: filter status laporan yang dihapus
4. Klik **Preview** untuk melihat apa yang akan dihapus (tidak ada perubahan)
5. Tinjau hasil preview
6. Jika yakin, klik **Eksekusi**

### Cleanup via Artisan Command

Untuk automation atau penghapusan lebih besar:

```bash
# Preview dulu (dry-run)
php artisan reports:cleanup --dry-run --days=365

# Eksekusi
php artisan reports:cleanup --days=365 --status=completed

# Hapus semua status > 2 tahun
php artisan reports:cleanup --days=730

# Simpan file attachment, hapus record saja
php artisan reports:cleanup --days=365 --keep-attachments
```

### Yang Dihapus Saat Cleanup

Saat laporan dihapus (baik via UI maupun command):
1. Record di tabel `daily_reports`
2. Komentar terkait di `job_comments`
3. Notifikasi terkait di `notifications`
4. File attachment dari `storage/app/public/attachments/` (kecuali `--keep-attachments`)

---

## 8. Maintenance Commands

Jalankan dari root project via terminal:

### Cleanup Notifikasi

```bash
# Preview notifikasi yang akan dihapus
php artisan notifications:cleanup --dry-run

# Hapus notifikasi > 30 hari (default)
php artisan notifications:cleanup

# Hapus notifikasi > 7 hari
php artisan notifications:cleanup --days=7
```

### Cleanup Foto Profil Rusak

```bash
# Preview referensi foto yang file-nya hilang
php artisan cleanup:orphaned-profile-pictures --dry-run

# Bersihkan referensi rusak (set ke null)
php artisan cleanup:orphaned-profile-pictures
```

### Daftar User

```bash
php artisan list:users
```

### Queue Worker

```bash
# Jalankan queue worker (wajib untuk notifikasi)
php artisan queue:listen --tries=1

# Restart queue worker setelah deploy
php artisan queue:restart

# Cek status queue
php artisan queue:monitor
```

### Cache Management

```bash
# Cache semua (produksi)
php artisan optimize

# Clear semua cache
php artisan optimize:clear

# Individual cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database

```bash
# Lihat status migrasi
php artisan migrate:status

# Jalankan migrasi baru
php artisan migrate

# Rollback
php artisan migrate:rollback
```

---

## 9. Pengaturan Sistem

Halaman `/admin/settings` (dalam pengembangan) akan berisi pengaturan sistem seperti:
- Konfigurasi email
- Batas ukuran upload
- Jadwal cleanup otomatis

Saat ini, konfigurasi sistem dilakukan via file `.env` dan `config/` secara manual.

### Konfigurasi Kritis via .env

| Setting | Keterangan |
|---------|------------|
| `APP_URL` | URL aplikasi (penting untuk link notifikasi dan PWA) |
| `APP_TIMEZONE` | Timezone: `Asia/Jakarta` |
| `QUEUE_CONNECTION` | `database` (wajib untuk notifikasi) |
| `MAIL_MAILER` | `smtp` untuk produksi, `log` untuk development |
| `SESSION_LIFETIME` | Lama sesi login (menit, default: 120) |

Lihat [deployment.md](deployment.md) untuk konfigurasi produksi lengkap.
