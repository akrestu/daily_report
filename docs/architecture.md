# SiGAP — Arsitektur Sistem

> Versi: 1.1.0 | Framework: Laravel 12 | PHP: 8.2+

---

## Daftar Isi

1. [Gambaran Umum](#1-gambaran-umum)
2. [Role Hierarchy (9 Roles)](#2-role-hierarchy-9-roles)
3. [Alur Approval](#3-alur-approval)
4. [Sistem PIC (Person In Charge)](#4-sistem-pic-person-in-charge)
5. [Access Control Matrix](#5-access-control-matrix)
6. [Data Models](#6-data-models)
7. [Dua Kolom Status Laporan](#7-dua-kolom-status-laporan)
8. [Struktur Direktori](#8-struktur-direktori)
9. [Laravel 12 Patterns](#9-laravel-12-patterns)
10. [Frontend Stack](#10-frontend-stack)

---

## 1. Gambaran Umum

SiGAP (Sistem Informasi Giat Aktivitas Pekerjaan) adalah sistem pelaporan aktivitas kerja harian berbasis web. Sistem ini mengelola pembuatan laporan, alur approval berjenjang, notifikasi otomatis, komentar, dan lampiran file dengan kontrol akses berbasis peran.

**Tech Stack Ringkas:**

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+, Livewire 3 |
| Database | MySQL/MariaDB (prod), SQLite (dev) |
| Frontend | Bootstrap 5.3 + Tailwind CSS 4, Alpine.js 3, Chart.js 4 |
| Build Tool | Vite 6 |
| Queue | Database (driver: `database-uuids`) |
| File Storage | Laravel Storage (local disk, symlink ke `public/storage/`) |
| Image Processing | Intervention Image v3 (GD driver) |
| Excel | Maatwebsite/Excel 3.1 |
| PWA | Workbox 7 (service worker) |

---

## 2. Role Hierarchy (9 Roles)

Sistem menggunakan **9 role** yang didefinisikan di `database/seeders/RolesSeeder.php` dan dikelola oleh model `app/Models/Role.php`.

| # | Slug | Name | Level | Deskripsi |
|---|------|------|-------|-----------|
| 1 | `admin` | Administrator | — | Full akses sistem, tidak masuk dalam hierarki operasional |
| 2 | `level1` | Level 1 | 1 | Level terendah. Tidak bisa jadi PIC, tidak bisa approve. Assign ke Level 2 |
| 3 | `level2` | Level 2 | 2 | Bisa approve Level 1. Assign PIC ke Level 3 |
| 4 | `level3` | Level 3 | 3 | Bisa approve Level 2. Assign PIC ke Level 4 |
| 5 | `level4` | Level 4 | 4 | Bisa approve Level 3. Assign PIC ke Level 5 |
| 6 | `level5` | Level 5 | 5 | Bisa approve Level 4. Assign PIC ke Level 6. Bisa batch-delete laporan departemen |
| 7 | `level6` | Level 6 | 6 | Bisa approve Level 5. Assign PIC ke Level 7/8 |
| 8 | `level7` | Level 7 | 7 | Bisa approve Level 6. Assign PIC ke Level 8 |
| 9 | `level8` | Level 8 | 8 | **TIDAK BISA membuat laporan**. Approve Level 6 DAN Level 7. Cross-department dalam job site yang sama |

> **Catatan Penting:** Slug lama (`department_head`, `leader`, `staff`) adalah legacy alias untuk backward-compatibility saja dan TIDAK di-seed di sistem baru.

**Method di `app/Models/User.php` untuk cek role:**

```php
$user->isAdmin()        // slug === 'admin'
$user->isLevel1()       // slug === 'level1'
// ...
$user->isLevel8()       // slug === 'level8'
$user->getRoleLevel()   // returns 0-8 (0 untuk admin/non-level roles)
```

---

## 3. Alur Approval

### Aturan Umum

Approval mengikuti hierarki level: **Level N bisa meng-approve laporan dari Level N-1**.

```
Level 1 → (submit) →
Level 2 → (approve Level 1) →
Level 3 → (approve Level 2) →
Level 4 → (approve Level 3) →
Level 5 → (approve Level 4) →
Level 6 → (approve Level 5) →
Level 7 → (approve Level 6) →
Level 8 → (approve Level 6 DAN Level 7)
Admin   → (approve siapapun)
```

### Aturan Khusus Level 8

Level 8 bisa meng-approve laporan dari **Level 6 dan Level 7** (melewati satu level). Untuk laporan cross-department, Level 8 hanya bisa approve laporan yang berada di `job_site_id` yang sama.

### Implementasi di Kode

**`app/Models/User.php` — method `canApprove(User $targetUser)`:**

```php
public function canApprove(User $user): bool
{
    if ($this->isAdmin()) return true;

    $myLevel = $this->getRoleLevel();
    $targetLevel = $user->getRoleLevel();

    if ($myLevel === 8) {
        return in_array($targetLevel, [6, 7]);
    }

    return $myLevel > 0 && $targetLevel === ($myLevel - 1);
}
```

**`app/Policies/DailyReportPolicy.php` — method `approve()`:**

```php
public function approve(User $user, DailyReport $dailyReport): bool
{
    $reportOwner = $dailyReport->user;
    if ($user->isLevel8()) {
        // Level 8 approve Level 6 & 7 dalam job site yang sama
        return in_array($reportOwner->getRoleLevel(), [6, 7])
            && $user->job_site_id === $reportOwner->job_site_id;
    }
    return $user->canApprove($reportOwner);
}
```

### Status Kolom Saat Approval

| Aksi | `approval_status` | `status` | `approved_by` |
|------|-------------------|----------|---------------|
| Submit | `pending` | `pending` | null |
| Disetujui | `approved` | `completed` | id approver |
| Ditolak | `rejected` | tidak berubah | null |

---

## 4. Sistem PIC (Person In Charge)

Field `job_pic` pada tabel `daily_reports` menyimpan `user.id` dari PIC yang ditugaskan.

**Aturan pemilihan PIC** (`User::getEligiblePicRoles()`):

| Pembuat laporan | PIC yang eligible |
|-----------------|-------------------|
| Level 1 | Level 2 |
| Level 2 | Level 3 |
| Level 3 | Level 4 |
| Level 4 | Level 5 |
| Level 5 | Level 6 |
| Level 6 | Level 7, Level 8 |
| Level 7 | Level 8 |
| Admin | Semua kecuali diri sendiri |
| Level 8 | — (tidak bisa buat laporan) |

**Yang TIDAK bisa jadi PIC:**
- Level 1 (terlalu rendah untuk ditugaskan)
- Admin (tidak masuk hierarki operasional)

**Method `User::canBePic()`** mengembalikan `false` untuk Level 1 dan Admin.

---

## 5. Access Control Matrix

Berdasarkan `app/Policies/DailyReportPolicy.php` — method `view()`:

| Role | Bisa lihat laporan milik siapa |
|------|-------------------------------|
| Admin | Semua laporan |
| Level 8 | Semua laporan dalam `job_site_id` yang sama (cross-department) |
| Level 7 | Semua laporan dalam departemen yang sama |
| Level 6 | Semua laporan dalam departemen yang sama |
| Level 5 | Semua laporan dalam departemen yang sama (monitoring) |
| Level 4 | Semua laporan dalam departemen yang sama |
| Level 3 | Laporan Level 1 dan Level 2 dalam departemen yang sama |
| Level 2 | Laporan Level 1 dalam departemen yang sama |
| Level 1 | Hanya laporan milik sendiri + laporan completed/approved di departemen |

**Aturan delete:**

| Role | Bisa delete laporan |
|------|---------------------|
| Admin | Semua laporan |
| Level 8 | Laporan dalam job_site yang sama |
| Level 7 | Laporan dalam departemen yang sama |
| Level 6 | Laporan dalam departemen yang sama |
| Level 5 | Laporan dalam departemen yang sama |
| Level 1–4 | Hanya laporan milik sendiri yang masih `pending` |

---

## 6. Data Models

### Relasi Antar Model

```
User ──────────── Role           (many-to-one, FK: role_id)
User ──────────── Department     (many-to-one, FK: department_id)
User ──────────── JobSite        (many-to-one, FK: job_site_id)
User ──────────── User           (self-ref parent, FK: user_id)

DailyReport ───── User           (creator, FK: user_id)
DailyReport ───── Department     (FK: department_id)
DailyReport ───── JobSite        (FK: job_site_id)
DailyReport ───── Section        (FK: section_id, nullable)
DailyReport ───── User           (PIC, FK: job_pic)
DailyReport ───── User           (approver, FK: approved_by)
DailyReport ───── JobComment[]   (hasMany)

JobComment ─────── User          (commenter, FK: user_id)
JobComment ─────── DailyReport   (FK: daily_report_id)

Notification ───── User          (FK: user_id)
Notification ───── DailyReport   (FK: daily_report_id)
Notification ───── JobComment    (FK: comment_id, nullable)

Section ─────────── Department   (FK: department_id)
```

### DailyReport — Fillable Fields Lengkap

```php
protected $fillable = [
    'user_id', 'department_id', 'job_site_id', 'section_id',
    'job_name', 'report_date', 'due_date',
    'description', 'remark', 'status', 'approval_status',
    'job_pic', 'approved_by', 'rejection_reason',
    'attachment_path', 'attachment_original_name',
    'attachment_path_2', 'attachment_original_name_2',
    'attachment_path_3', 'attachment_original_name_3',
];
```

### User — notification_preferences JSON

Field `notification_preferences` di tabel `users` menyimpan preferensi notifikasi sebagai JSON:

```json
{
    "job_approved": true,
    "job_rejected": true,
    "pending_approval": true,
    "new_comment": true,
    "email_notifications": false
}
```

Default: semua notifikasi aktif kecuali email. Method `User::wantsNotification(string $type)` memeriksa preferensi ini sebelum observer membuat notifikasi.

### JobSite

```php
protected $fillable = ['name', 'code', 'description', 'location', 'is_active'];
// Scope: JobSite::active() — hanya site yang is_active = true
```

### Section

```php
protected $fillable = ['department_id', 'name', 'code', 'description', 'is_active'];
// belongsTo Department, hasMany DailyReport
// Scope: Section::active() — hanya section yang is_active = true
```

### Notification (Custom — bukan Laravel built-in)

> Model `app/Models/Notification.php` menggunakan tabel `notifications` **kustom**, bukan tabel `notifications` bawaan Laravel (yang polymorphic). Schema berbeda.

```php
protected $fillable = [
    'user_id', 'daily_report_id', 'comment_id',
    'type', 'message', 'is_read'
];
// type: 'job_approved' | 'job_rejected' | 'pending_approval' | 'new_comment'
```

---

## 7. Dua Kolom Status Laporan

Tabel `daily_reports` memiliki **dua kolom status yang berbeda** — sering membingungkan:

| Kolom | Nilai | Arti |
|-------|-------|------|
| `status` | `pending` | Pekerjaan belum dimulai |
| `status` | `in_progress` | Pekerjaan sedang berjalan |
| `status` | `completed` | Pekerjaan selesai (di-set otomatis saat diapprove) |
| `approval_status` | `pending` | Menunggu approval atasan |
| `approval_status` | `approved` | Sudah disetujui |
| `approval_status` | `rejected` | Ditolak, ada `rejection_reason` |

Saat laporan **diapprove**: `approval_status = 'approved'` DAN `status = 'completed'`.
Saat laporan **ditolak**: `approval_status = 'rejected'`, `status` tidak berubah.

---

## 8. Struktur Direktori

```
app/
├── Console/Commands/
│   ├── CleanupDailyReports.php          # php artisan reports:cleanup
│   ├── CleanupNotifications.php         # php artisan notifications:cleanup
│   ├── CleanupOrphanedProfilePictures.php # php artisan cleanup:orphaned-profile-pictures
│   └── ListUsers.php                    # php artisan list:users
├── Exports/
│   ├── DailyReportsExport.php           # Export laporan (dengan filter)
│   ├── DailyReportsTemplateExport.php   # Template import laporan
│   ├── UsersExport.php                  # Export daftar user
│   └── UsersTemplateExport.php          # Template import user
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DepartmentController.php
│   │   │   ├── JobSiteController.php
│   │   │   ├── ReportCleanupController.php
│   │   │   ├── SectionController.php
│   │   │   └── UserController.php
│   │   ├── DailyReportController.php    # CRUD + approval + batch + export
│   │   ├── DashboardController.php
│   │   ├── JobCommentController.php
│   │   ├── NotificationController.php
│   │   ├── OrganizationChartController.php
│   │   └── ProfileController.php
│   └── Middleware/
│       └── AdminOnly.php                # Cek role admin untuk /admin/* routes
├── Imports/
│   ├── DailyReportsImport.php
│   └── UsersImport.php
├── Livewire/
│   └── DailyReports/
│       ├── DailyReportForm.php          # Form buat/edit laporan (real-time)
│       └── DailyReportList.php          # List laporan dengan filter & batch
├── Models/
│   ├── DailyReport.php
│   ├── Department.php
│   ├── JobComment.php
│   ├── JobSite.php
│   ├── Notification.php                 # Custom model (bukan built-in Laravel)
│   ├── Role.php
│   ├── Section.php
│   └── User.php
├── Observers/
│   ├── DailyReportObserver.php          # Notifikasi saat laporan dibuat/diapprove/ditolak
│   └── JobCommentObserver.php           # Notifikasi saat komentar ditambahkan
├── Policies/
│   └── DailyReportPolicy.php            # Otorisasi akses laporan
└── Providers/
    ├── AppServiceProvider.php           # Bootstrap: pagination, Carbon locale, slow query
    ├── MiddlewareServiceProvider.php    # Daftarkan alias middleware
    └── NotificationServiceProvider.php
```

**Database:**

```
database/
├── migrations/          # 28 file migrasi
└── seeders/
    ├── DatabaseSeeder.php
    ├── RolesSeeder.php          # 9 roles
    ├── DepartmentsSeeder.php    # 5 departemen
    ├── JobSiteSeeder.php        # 4 job site
    ├── SectionSeeder.php        # Seksi IT & HR
    ├── UsersSeeder.php          # User per role per dept
    ├── GenesisAdminSeeder.php   # Admin awal
    └── UserIdSeeder.php         # Setup parent-child user
```

**Resources:**

```
resources/views/
├── layouts/             # app.blade.php, guest.blade.php, navigation.blade.php
├── auth/                # login.blade.php, register.blade.php
├── components/          # input, select, textarea, modal-dialogs, pwa-install
├── dashboard/           # admin, department-head, leader, staff
├── daily-reports/       # index, create, edit, show, pending, user-jobs, assigned-jobs, import
├── admin/               # users/, departments/, job-sites/, sections/, reports/
├── notifications/       # all.blade.php
├── organization/        # chart.blade.php
└── profile/             # edit.blade.php
```

---

## 9. Laravel 12 Patterns

### Model Casts — Method, bukan Property

Laravel 12 menggunakan method `casts()`, bukan property `$casts`:

```php
// BENAR (Laravel 12)
protected function casts(): array
{
    return [
        'report_date' => 'date',
        'due_date' => 'date',
        'is_read' => 'boolean',
        'notification_preferences' => 'json',
    ];
}

// SALAH (Laravel < 11)
// protected $casts = ['report_date' => 'date'];
```

### bootstrap/app.php — Application Configuration

Laravel 12 menggunakan `bootstrap/app.php` dengan fluent builder:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        console: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) { ... })
    ->withExceptions(function (Exceptions $exceptions) { ... })
    ->create();
```

Timezone di-set di awal `bootstrap/app.php`:
```php
date_default_timezone_set('Asia/Jakarta');
```

### AppServiceProvider Bootstrap

```php
public function boot(): void
{
    // Bootstrap styling untuk pagination
    Paginator::useBootstrapFive();

    // Locale Indonesia untuk Carbon
    Carbon::setLocale('id');
    setlocale(LC_TIME, 'id_ID');

    // Monitor slow queries di environment lokal
    if (app()->environment('local')) {
        DB::listen(function ($query) {
            if ($query->time > 500) {
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'time' => $query->time . 'ms',
                ]);
            }
        });
    }
}
```

### Queue Configuration

```env
QUEUE_CONNECTION=database
```

Driver `database-uuids` digunakan untuk performa lebih baik (UUID-based job IDs). Queue harus berjalan untuk notifikasi dan async jobs:

```bash
php artisan queue:listen --tries=1
```

### Development Concurrent Script

`composer run dev` menjalankan 3 proses sekaligus via `concurrently`:
```
php artisan serve | php artisan queue:listen --tries=1 | npm run dev
```

---

## 10. Frontend Stack

### CSS Framework (Hybrid Approach)

Sistem menggunakan **dua framework CSS secara bersamaan**:

| Framework | Versi | Penggunaan |
|-----------|-------|-----------|
| Bootstrap | 5.3.5 | Komponen utama (card, table, modal, button, form, nav) |
| Tailwind CSS | 4.1.4 | Utility classes untuk spacing/layout tertentu |

### JavaScript

| Library | Versi | Penggunaan |
|---------|-------|-----------|
| Alpine.js | 3.4.2 | Interaktivitas ringan (dropdown, toggle, kondisional) |
| Chart.js | 4.4.9 | Grafik di dashboard |
| FontAwesome | 6.7.2 | Icon |
| Animate.css | 4.1.1 | Animasi CSS |
| Axios | 1.8.2 | HTTP requests (AJAX) |

### Build Tool

- **Vite 6.3.2** dengan plugin `laravel-vite-plugin`
- Output: `public/build/` (manifest.json, hashed assets)
- HMR (Hot Module Replacement) saat development

### PWA

- **Workbox 7.3.0** via CDN di `public/sw.js`
- Cache version: `v3.0.0` (increment saat update besar)
- Strategi caching:
  - `CacheFirst` — static assets (CSS, JS, fonts)
  - `NetworkFirst` — dynamic/API routes
  - `StaleWhileRevalidate` — CDN resources
- Offline fallback: `public/offline.html`
- Manifest: `public/site.webmanifest` (static file)

### Livewire 3

Dua komponen Livewire di `app/Livewire/DailyReports/`:

**DailyReportForm** (`WithFileUploads`):
- Form multi-field untuk buat/edit laporan
- Upload 3 file attachment secara terpisah
- Cascade: pilih department → section tersedia, pilih PIC berdasarkan eligible roles
- Image compression via Intervention Image v3 (max 1920px, JPEG 60%)

**DailyReportList** (`WithPagination`):
- Filter: search, status, department, date range
- Batch select + approve/reject/delete
- Real-time refresh via `$refresh` listener
- 10 item per halaman
