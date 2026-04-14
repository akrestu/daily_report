# SiGAP — Troubleshooting

> Panduan diagnosis dan solusi masalah umum pada aplikasi SiGAP.

---

## Daftar Isi

1. [Notifikasi Tidak Terkirim](#1-notifikasi-tidak-terkirim)
2. [File Upload Gagal / Gambar Tidak Terkompresi](#2-file-upload-gagal--gambar-tidak-terkompresi)
3. [Foto Profil Tampil sebagai Avatar Default](#3-foto-profil-tampil-sebagai-avatar-default)
4. [Tombol Approve Tidak Muncul / Unauthorized](#4-tombol-approve-tidak-muncul--unauthorized)
5. [Laporan Tidak Terlihat oleh Atasan](#5-laporan-tidak-terlihat-oleh-atasan)
6. [Dropdown Seksi Kosong di Form Laporan](#6-dropdown-seksi-kosong-di-form-laporan)
7. [Masalah Login & Sesi](#7-masalah-login--sesi)
8. [Masalah Chart Dashboard](#8-masalah-chart-dashboard)
9. [Masalah PWA & Service Worker](#9-masalah-pwa--service-worker)
10. [Masalah Import Excel](#10-masalah-import-excel)
11. [Masalah Database & Migrasi](#11-masalah-database--migrasi)
12. [Masalah Performa](#12-masalah-performa)
13. [Catatan Keamanan](#13-catatan-keamanan)
14. [Debug Routes](#14-debug-routes)
15. [Perintah Cepat](#15-perintah-cepat)

---

## 1. Notifikasi Tidak Terkirim

### Gejala
- Approval/rejection laporan tidak menghasilkan notifikasi
- Komentar baru tidak menghasilkan notifikasi
- Badge notifikasi tidak update

### Penyebab Utama

**Queue worker tidak berjalan**

```bash
# Cek apakah queue worker aktif
php artisan queue:monitor

# Jalankan queue worker
php artisan queue:listen --tries=1

# Cek job yang gagal
php artisan queue:failed
```

**Konfigurasi queue salah**

Pastikan `.env` berisi:
```env
QUEUE_CONNECTION=database
```

Bukan `sync` (sync menjalankan job sekarang tapi sering timeout) atau `null` (ignore semua job).

**User memiliki preferensi notifikasi nonaktif**

Cek preferensi notifikasi user di:
- UI: klik nama user → Preferensi Notifikasi
- DB: `SELECT notification_preferences FROM users WHERE id = ?`

Default preferences:
```json
{
    "job_approved": true,
    "job_rejected": true,
    "pending_approval": true,
    "new_comment": true,
    "email_notifications": false
}
```

**Job gagal di queue**

```bash
# Lihat failed jobs
php artisan queue:failed

# Retry semua failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

---

## 2. File Upload Gagal / Gambar Tidak Terkompresi

### Gejala
- Error saat upload file attachment atau foto profil
- Gambar tersimpan tanpa kompresi (ukuran file besar)

### Cek Ekstensi GD

```bash
php -m | grep -i gd
```

Jika tidak ada output, ekstensi GD tidak aktif.

**XAMPP (Windows):**
1. Buka `php.ini` (biasanya di `C:\xampp\php\php.ini`)
2. Cari baris `;extension=gd` dan hapus titik koma
3. Restart Apache

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get install php8.2-gd
sudo systemctl restart php8.2-fpm
```

**Verifikasi:**
```bash
php -r "echo extension_loaded('gd') ? 'GD aktif' : 'GD tidak aktif';"
```

### Cek Permission Storage

```bash
# Storage harus writable
ls -la storage/app/public/

# Set permission
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Untuk Linux dengan web server (sesuaikan user)
chown -R www-data:www-data storage/ bootstrap/cache/
```

### Cek Symlink Storage

```bash
ls -la public/storage
# Harus: public/storage -> ../storage/app/public

# Jika tidak ada, buat symlink
php artisan storage:link
```

### Batas Ukuran Upload PHP

Jika muncul error "file terlalu besar", cek `php.ini`:
```ini
upload_max_filesize = 10M   ; minimum 5M (batas SiGAP 5MB per file)
post_max_size = 30M         ; harus lebih besar dari upload_max_filesize * 3
memory_limit = 256M
```

---

## 3. Foto Profil Tampil sebagai Avatar Default

### Gejala
- User sudah upload foto tapi tetap tampil avatar inisial
- Gambar menunjukkan URL valid tapi file tidak ditemukan (404)

### Penyebab

File foto dihapus dari storage tapi referensi di database masih ada.

### Solusi

```bash
# Preview: lihat referensi foto profil yang file-nya hilang
php artisan cleanup:orphaned-profile-pictures --dry-run

# Eksekusi: set profile_picture = null untuk yang hilang
php artisan cleanup:orphaned-profile-pictures
```

Command ini **tidak** menghapus file — hanya membersihkan referensi database yang stale.

### Catatan Kode

`User::getProfilePictureUrlAttribute()` mencatat warning di log jika file tidak ditemukan:
```
WARNING: Profile picture file not found for user {id}: {path}
```

Accessor ini bersifat read-only — tidak memodifikasi database secara otomatis.

---

## 4. Tombol Approve Tidak Muncul / Unauthorized

### Gejala
- Tombol "Setujui" tidak muncul di halaman laporan
- Muncul error 403 Forbidden saat mencoba approve

### Diagnosis

**Cek level user vs level pemilik laporan:**

- Level N hanya bisa approve laporan dari Level N-1
- Level 8 hanya approve Level 6 dan Level 7 (bukan Level 1-5)
- Level 1 tidak bisa approve siapapun
- Admin bisa approve semua

Contoh:
```
User Level 3 mencoba approve laporan dari Level 3 → GAGAL (harus beda level)
User Level 3 mencoba approve laporan dari Level 1 → GAGAL (harus Level N-1, bukan lebih rendah)
User Level 3 mencoba approve laporan dari Level 2 → BERHASIL ✓
```

**Cek job_site_id untuk Level 8:**

Level 8 hanya bisa approve laporan yang `job_site_id` sama dengan job_site user tersebut. Jika salah satu null, approval akan gagal.

```sql
-- Cek job_site user dan laporan
SELECT u.name, u.job_site_id, dr.id, dr.job_site_id
FROM users u
JOIN daily_reports dr ON dr.user_id = ?
WHERE u.id = ? -- approver
```

**Debug via `/debug/roles`:**

```
GET /debug/roles
```

Menampilkan semua role dan level untuk membantu diagnosis.

---

## 5. Laporan Tidak Terlihat oleh Atasan

### Gejala
- Atasan tidak bisa menemukan laporan bawahan
- Halaman daftar laporan kosong atau laporan tidak ada

### Penyebab & Solusi

**`department_id` tidak di-set pada user atau laporan:**

```sql
-- Cek apakah user punya department_id
SELECT id, name, department_id, role_id FROM users WHERE id = ?;

-- Cek apakah laporan punya department_id
SELECT id, job_name, department_id, user_id FROM daily_reports WHERE id = ?;
```

Solusi: isi `department_id` untuk kedua user (atasan dan bawahan) dan pastikan laporan memiliki `department_id` yang benar.

**Level visibility tidak sesuai:**

| Atasan | Yang Bisa Dilihat |
|--------|-------------------|
| Level 2 | Hanya Level 1 di dept yang sama |
| Level 3 | Level 1 dan 2 di dept yang sama |
| Level 4 | Semua laporan di dept yang sama |
| Level 5–7 | Semua laporan di dept yang sama |
| Level 8 | Semua laporan di job_site yang sama (lintas dept) |

Level 1-4 TIDAK bisa melihat laporan dari Level 5 ke atas.

---

## 6. Dropdown Seksi Kosong di Form Laporan

### Gejala
- Pilih departemen di form laporan tapi dropdown seksi tetap kosong
- AJAX request ke `/sections/by-department` gagal atau kosong

### Penyebab

1. **Belum ada seksi untuk departemen tersebut**: Buat seksi di `/admin/sections`
2. **Seksi tidak aktif**: Aktifkan seksi di admin panel (field `is_active`)
3. **AJAX gagal**: Buka DevTools → Network, cek response dari `/sections/by-department?department_id=N`
4. **JavaScript error**: Cek Console tab di DevTools

### Verifikasi via Database

```sql
SELECT s.id, s.name, s.is_active, d.name as dept
FROM sections s
JOIN departments d ON s.department_id = d.id
WHERE s.department_id = ?;
```

---

## 7. Masalah Login & Sesi

### Tidak Bisa Login

```bash
# Clear session dan cache
php artisan cache:clear
php artisan session:flush  # jika ada

# Cek tabel session ada
php artisan migrate:status | grep session
```

**Session driver:**
- `SESSION_DRIVER=database` memerlukan tabel `sessions`
- Jalankan `php artisan migrate` jika tabel belum ada

### Sesi Sering Expired

Edit `.env`:
```env
SESSION_LIFETIME=240   # 4 jam (default: 120 menit)
```

### CSRF Token Mismatch

- Pastikan form menggunakan `@csrf` directive Blade
- Jika AJAX, pastikan header `X-XSRF-TOKEN` dikirim (Axios melakukan ini otomatis)
- Clear browser cache jika masalah muncul di form lama

---

## 8. Masalah Chart Dashboard

### Gejala
- Chart tidak muncul di dashboard
- Error "Canvas is already in use" di console
- Grafik tampil kosong

### Solusi

**Canvas already in use:**

Ini terjadi ketika komponen Livewire reload tanpa destroy chart sebelumnya. Solusi ada di `public/js/`:

```javascript
// Sebelum inisialisasi chart baru, destroy yang lama
if (window.myChart) {
    window.myChart.destroy();
}
window.myChart = new Chart(ctx, {...});
```

**Chart tidak muncul setelah navigate:**

Alpine.js dan Chart.js perlu diinisialisasi ulang saat komponen mount. Pastikan inisialisasi chart ada di dalam `x-init` atau Livewire lifecycle hook.

---

## 9. Masalah PWA & Service Worker

### PWA Tidak Bisa Diinstall

1. Aplikasi harus diakses via **HTTPS** (Chrome tidak izinkan install dari HTTP)
2. `site.webmanifest` harus bisa diakses dan valid
3. Cek DevTools → Application → Manifest: tidak boleh ada error

### Icon PWA Tidak Muncul

**Cek route fallback aktif:**
```bash
php artisan route:list | grep icons
# Harus ada: GET /icons/{filename}
```

**Force update cache service worker:**

1. Increment `CACHE_VERSION` di `public/sw.js`:
   ```javascript
   const CACHE_VERSION = 'v3.1.0'; // naikkan versi
   ```
2. Deploy ulang
3. User buka aplikasi → service worker baru akan terdeteksi
4. Klik "Update" pada banner yang muncul, atau:
   - DevTools → Application → Service Workers → klik "Update"
   - Hard refresh: `Ctrl+Shift+R`

**Unregister service worker (untuk testing):**
```
DevTools → Application → Service Workers → Unregister
```

### Cache Lama Setelah Deploy

```bash
# Hapus cache public build
php artisan view:clear
npm run build  # rebuild assets dengan hash baru
```

User yang masih menggunakan SW lama akan mendapat update otomatis saat service worker baru terdeteksi.

---

## 10. Masalah Import Excel

### Error Saat Upload Template

**Format tanggal tidak dikenali:**

Template menggunakan format `DD/MM/YYYY`. Import juga mendukung:
- `YYYY-MM-DD`
- `DD-MM-YYYY`
- `YYYY/MM/DD`
- Angka Excel (serial date)

**Nama departemen tidak ditemukan:**

Nama departemen di template harus **persis sama** (case-sensitive) dengan nama di database.

```sql
SELECT name FROM departments;
```

**User ID tidak ditemukan:**

Field `user_id` di template mengacu pada kolom `user_id` di tabel `users` (bukan `id`). Ambil dari export user admin.

### Download Template Gagal

```bash
# Cek ekstensi zip PHP (dibutuhkan PhpSpreadsheet)
php -m | grep zip

# Jika tidak ada (Ubuntu):
sudo apt-get install php8.2-zip
```

---

## 11. Masalah Database & Migrasi

### Migration Error

```bash
# Cek status migrasi
php artisan migrate:status

# Rollback dan coba lagi
php artisan migrate:rollback
php artisan migrate
```

**File migrasi disabled:**

File `2025_04_21_090550_add_department_id_to_daily_reports_table.php.disabled` sengaja dinonaktifkan (ekstensi `.disabled`). Jangan ubah nama file ini.

### Fresh Install

```bash
# Hapus semua tabel dan seed ulang
php artisan migrate:fresh --seed
```

> Peringatan: `migrate:fresh` akan menghapus SEMUA data.

### Koneksi Database Gagal

```bash
# Test koneksi
php artisan db:show

# Cek konfigurasi
php artisan config:show database
```

---

## 12. Masalah Performa

### Dashboard Lambat

**Aktifkan query caching:**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Cek slow queries di log:**

Slow queries (>500ms) dicatat otomatis di environment lokal:
```bash
grep "Slow query" storage/logs/laravel.log
```

**Eager loading untuk N+1:**

Pastikan query laporan selalu eager load:
```php
DailyReport::with(['user', 'department', 'pic', 'approver', 'jobSite', 'section'])->get()
```

### Performance Indexes

Indexes yang sudah ada (dari migration `2025_11_13_145552`):

| Tabel | Index | Kolom |
|-------|-------|-------|
| `daily_reports` | `idx_user_status` | `(user_id, status)` |
| `daily_reports` | `idx_dept_approval` | `(department_id, approval_status)` |
| `daily_reports` | `idx_report_date` | `(report_date, status)` |
| `notifications` | `idx_user_read_date` | `(user_id, is_read, created_at)` |
| `job_comments` | `idx_report_id` | `(daily_report_id)` |

Jika performa masih lambat, verifikasi index aktif:
```sql
SHOW INDEX FROM daily_reports;
SHOW INDEX FROM notifications;
```

---

## 13. Catatan Keamanan

### Path Traversal

Route `GET /storage/attachments/{filename}` menggunakan `basename($filename)` untuk mencegah path traversal. Jangan ubah logika ini.

### MIME Whitelist

Hanya MIME type tertentu yang ditampilkan inline di browser. File lain di-force download untuk mencegah XSS via SVG atau HTML upload.

### Mass Assignment

Semua model menggunakan `$fillable` eksplisit. Jangan ganti dengan `$guarded = []`.

### Debug Routes di Produksi

Route berikut harus dihapus atau dilindungi di produksi:

```
GET /debug/roles
GET /debug/comments/{reportId}
GET /test-create-user
GET /test-chart
```

Cara aman: bungkus dalam middleware atau hapus dari `routes/web.php`.

---

## 14. Debug Routes

> Hanya gunakan di environment lokal/development.

| Route | Fungsi |
|-------|--------|
| `GET /debug/roles` | Tampilkan semua role dan level-nya (JSON) |
| `GET /debug/comments/{reportId}` | Info komentar untuk laporan tertentu (JSON) |
| `GET /test-create-user` | Test buat user (TestController) |

Semua memerlukan autentikasi (middleware `auth`).

---

## 15. Perintah Cepat

### Development

```bash
composer run dev          # Start Laravel + Queue + Vite sekaligus
php artisan serve         # Hanya Laravel server
php artisan queue:listen --tries=1  # Hanya queue worker
npm run dev               # Hanya Vite HMR
php artisan pail          # Stream logs real-time
```

### Database

```bash
php artisan migrate                  # Jalankan migrasi baru
php artisan migrate:fresh --seed     # Reset + seed (hapus semua data!)
php artisan migrate:status           # Status semua migrasi
php artisan db:show                  # Info koneksi database
```

### Cache & Optimasi

```bash
php artisan optimize         # Cache semua (produksi)
php artisan optimize:clear   # Hapus semua cache
php artisan config:clear     # Hapus config cache saja
php artisan route:clear      # Hapus route cache saja
php artisan view:clear       # Hapus view cache saja
```

### Maintenance

```bash
php artisan reports:cleanup --dry-run    # Preview cleanup laporan
php artisan notifications:cleanup --dry-run  # Preview cleanup notifikasi
php artisan cleanup:orphaned-profile-pictures --dry-run
php artisan list:users
php artisan route:list       # Daftar semua route
```

### Queue

```bash
php artisan queue:monitor    # Status queue
php artisan queue:failed     # Lihat failed jobs
php artisan queue:retry all  # Retry semua failed jobs
php artisan queue:flush      # Hapus semua failed jobs
php artisan queue:restart    # Restart worker (setelah deploy)
```
