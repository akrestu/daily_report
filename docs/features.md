# SiGAP — Fitur Lengkap

> Referensi teknis semua fitur aplikasi. Untuk panduan penggunaan, lihat [user_guide.md](user_guide.md).

---

## Daftar Isi

1. [Dashboard](#1-dashboard)
2. [Daily Report — CRUD](#2-daily-report--crud)
3. [Sistem Attachment File](#3-sistem-attachment-file)
4. [Alur Approval](#4-alur-approval)
5. [Batch Operations](#5-batch-operations)
6. [Livewire DailyReportForm](#6-livewire-dailyreportform)
7. [Livewire DailyReportList](#7-livewire-dailyreportlist)
8. [Sistem Notifikasi](#8-sistem-notifikasi)
9. [Komentar (Job Comments)](#9-komentar-job-comments)
10. [Organization Chart](#10-organization-chart)
11. [Excel Import / Export](#11-excel-import--export)
12. [PWA (Progressive Web App)](#12-pwa-progressive-web-app)
13. [Report Cleanup System](#13-report-cleanup-system)
14. [Profile Management](#14-profile-management)

---

## 1. Dashboard

**Controller:** `app/Http/Controllers/DashboardController.php`

Dashboard menampilkan konten berbeda berdasarkan role user. Setiap role memiliki view terpisah:
- `resources/views/dashboard/admin.blade.php`
- `resources/views/dashboard/department-head.blade.php`
- `resources/views/dashboard/leader.blade.php`
- `resources/views/dashboard/staff.blade.php`

### Data yang Dikirim ke View

| Variabel | Deskripsi |
|----------|-----------|
| `totalReports` | Total semua laporan |
| `pendingReports` | Laporan menunggu approval |
| `inProgressReports` | Laporan sedang berjalan |
| `completedReports` | Laporan selesai |
| `rejectedReports` | Laporan ditolak |
| `reportsToday` | Laporan dibuat hari ini |
| `reportsThisWeek` | Laporan minggu ini |
| `reportsThisMonth` | Laporan bulan ini |
| `completionPercentage` | % selesai (exclude rejected dari denominator) |
| `recentReports` | 5 laporan terbaru (eager-load user, department) |
| `topPerformers` | Top 5 PIC berdasarkan completion rate (raw SQL) |
| `urgentReports` | Laporan dengan due date dalam 3 hari, belum completed |
| `avgCompletionTimes` | Rata-rata waktu selesai per departemen |

### Chart Data

Metode private di DashboardController menyediakan data untuk Chart.js:
- `getReportTrendData()` — tren laporan 30 hari terakhir (per departemen untuk admin)
- `getPersonalReportTrendData($userId)` — tren personal untuk staff
- `getDepartmentPerformanceData()` — performa tiap departemen
- `getPersonalPerformanceData($userId)` — completion rate personal

**Sidebar toggle:** `POST /dashboard/toggle-sidebar` menyimpan preferensi sidebar (collapsed/expanded) ke session.

---

## 2. Daily Report — CRUD

**Controller:** `app/Http/Controllers/DailyReportController.php`

### Validasi Field

Method `getValidationRules()` mengembalikan aturan validasi:

| Field | Rule | Keterangan |
|-------|------|------------|
| `job_name` | `required|string|max:255` | Nama pekerjaan |
| `department_id` | `required|exists:departments,id` | Harus departemen valid |
| `job_site_id` | `nullable|exists:job_sites,id` | Opsional |
| `section_id` | `nullable|exists:sections,id` | Opsional |
| `report_date` | `required|date` | Tanggal laporan |
| `due_date` | `required|date|after_or_equal:report_date` | Harus >= report_date |
| `job_pic` | `required|exists:users,id` | PIC wajib dipilih |
| `description` | `required|string` | Deskripsi pekerjaan |
| `remark` | `nullable|string` | Catatan tambahan |
| `status` | `required|in:pending,in_progress,completed` | Status pekerjaan |
| `attachment` | `nullable|file|max:5120|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx,xls,xlsx` | Max 5MB |
| `attachment_2` | sama dengan attachment | |
| `attachment_3` | sama dengan attachment | |

### Pembuatan Multiple Laporan

`POST /daily-reports/store-multiple` — buat banyak laporan sekaligus dari satu form.

Array validasi per laporan, semua laporan dibungkus dalam `DB::transaction()` untuk konsistensi data.

### Permission Edit / Delete

| Aksi | Siapa yang Bisa |
|------|----------------|
| Edit | Hanya pemilik laporan, hanya saat `approval_status = 'pending'` |
| Delete (sendiri) | Pemilik laporan, hanya saat `approval_status = 'pending'` |
| Delete (semua dept) | Level 5, 6, 7 (dalam departemen yang sama) |
| Delete (cross-dept) | Level 8 (dalam job_site yang sama) |
| Delete (semua) | Admin |

Cek via `DailyReportPolicy::delete()`.

### Daftar View Laporan

| Route | View | Isi |
|-------|------|-----|
| `GET /daily-reports` | `index` | Semua laporan (filter by role) |
| `GET /daily-reports/pending` | `pending` | Laporan menunggu approval dari user ini |
| `GET /daily-reports/my-jobs` | `user-jobs` | Laporan yang dibuat user ini |
| `GET /daily-reports/assigned-jobs` | `assigned-jobs` | Laporan di mana user ini adalah PIC |

---

## 3. Sistem Attachment File

### Spesifikasi

- Maksimum **3 file** per laporan
- Field: `attachment_path`, `attachment_path_2`, `attachment_path_3`
- Original filename: `attachment_original_name`, `attachment_original_name_2`, `attachment_original_name_3`
- Lokasi storage: `storage/app/public/attachments/`
- Naming: `attachment_{uniqid()}_{time()}.{ext}` (mencegah tabrakan nama)

### Kompresi Gambar Otomatis

File gambar (jpg, jpeg, png, gif) dikompresi otomatis via **Intervention Image v3** (GD driver):

```php
// Logika di DailyReportForm::processAttachment() dan DailyReportController
$manager = new ImageManager(new Driver());
$image = $manager->read($file->getRealPath());
$image->scaleDown(width: 1920, height: 1920);  // Max dimensi 1920px
$image->toJpeg(quality: 60)->save($fullPath);   // JPEG 60% quality
```

Non-gambar (PDF, DOC, XLS) disimpan apa adanya tanpa modifikasi.

### Akses Secure

Route `GET /storage/attachments/{filename}` (middleware: `auth`) melakukan:
1. Sanitasi nama file: `basename($filename)` untuk cegah path traversal
2. Cek izin: user harus admin, pemilik laporan, PIC laporan, atau anggota departemen yang sama
3. Tentukan cara tampil berdasarkan MIME:
   - **Inline** (tampil di browser): gambar (jpg/png/gif), PDF, plain text
   - **Download** (paksa download): semua tipe lain
4. Header keamanan: `X-Content-Type-Options: nosniff`, `Content-Security-Policy`

---

## 4. Alur Approval

**Controller:** `DailyReportController::approval()`
**Route:** `POST /daily-reports/{dailyReport}/approval`

### Request Payload

```json
{
    "status": "approved",
    "rejection_reason": null
}
// atau
{
    "status": "rejected",
    "rejection_reason": "Deskripsi kurang detail"
}
```

### Logika Approval

1. Cek otorisasi via `DailyReportPolicy::approve()`
2. Jika **approved**:
   - Set `approval_status = 'approved'`
   - Set `status = 'completed'`
   - Set `approved_by = $approver->id`
3. Jika **rejected**:
   - Set `approval_status = 'rejected'`
   - Set `rejection_reason = $request->rejection_reason`
4. Buat `Notification` langsung (tidak via observer — observer hanya untuk `updated` event yang memantau perubahan `approval_status`)

### Notifikasi Approval

Saat approval, controller membuat notifikasi ke pemilik laporan dengan pesan:
- Approved: `"Laporan '{job_name}' Anda telah disetujui oleh {approver_name}"`
- Rejected: `"Laporan '{job_name}' Anda ditolak. Alasan: {rejection_reason}"`

---

## 5. Batch Operations

### Batch Approve / Reject

**Routes:**
- `POST /daily-reports/batch-approve`
- `POST /daily-reports/batch-reject`

**Request:** `{ "ids": [1, 2, 3], "rejection_reason": "..." }` (untuk reject)

**Logika:**
- Iterasi setiap ID, cek `canApprove()` untuk owner laporan tersebut
- Skip laporan yang tidak eligible, approve/reject sisanya
- Return redirect dengan flash message berisi count sukses/gagal
- Maksimum 100 laporan per request (anti-DoS, dibatasi di validasi)

### Batch Delete

**Route:** `DELETE /daily-reports/batch-delete`

**Siapa yang bisa:**
- Admin: hapus laporan apa saja
- Level 5+: hapus laporan dalam departemen sendiri
- Level 8: hapus dalam job_site yang sama
- Owner: hapus laporan sendiri yang masih `pending`

File attachment dihapus bersama laporan.

---

## 6. Livewire DailyReportForm

**File:** `app/Livewire/DailyReports/DailyReportForm.php`
**Traits:** `WithFileUploads`

### Properties

```php
// Data Form
public $reportId, $jobName, $departmentId, $jobSiteId, $sectionId;
public $reportDate, $dueDate, $jobPic;
public $description, $remark;
public $status = 'pending';

// Attachments
public $attachment, $attachment_2, $attachment_3;

// Edit mode
public $isEditMode = false;
public $existingAttachment, $existingAttachmentName;
// ... (attachment_2, attachment_3 serupa)

// Multiple form
public $showMultipleForm = false;
public array $multipleReports = [];

// State
public array $eligiblePics = [];
public bool $isSubmitting = false;
```

### Method Kunci

| Method | Fungsi |
|--------|--------|
| `mount($dailyReport = null)` | Init form. Jika edit, isi dari model. Load eligible PICs |
| `updatedDepartmentId()` | Reset PIC dan Section saat departemen berubah |
| `loadEligiblePics($currentPicId = null)` | Query user yang bisa jadi PIC berdasarkan `getEligiblePicRoles()` |
| `processAttachment($file, $oldPath)` | Upload + compress gambar. Return `['path', 'original_name']` |
| `save()` | Validasi, proses attachment, create/update DailyReport |
| `addReport()` / `removeReport($index)` | Manage multiple report form |

### Cascading Dropdown

1. Pilih Department → trigger `updatedDepartmentId()` → reset `jobPic`, `sectionId`, reload PICs
2. Sections di-filter via `Section::where('department_id', $this->departmentId)->active()->get()`
3. PICs di-filter via `User::whereIn('role_id', $eligibleRoleIds)->where('department_id', $dept)->get()`

---

## 7. Livewire DailyReportList

**File:** `app/Livewire/DailyReports/DailyReportList.php`
**Traits:** `WithPagination`

### Filter yang Tersedia

| Property | Filter |
|----------|--------|
| `$search` | Full-text pada `job_name`, `description`, `remark` |
| `$statusFilter` | Filter `approval_status` |
| `$departmentFilter` | Filter `department_id` |
| `$dateFrom` / `$dateTo` | Range `report_date` |

Setiap filter yang berubah secara otomatis reset halaman ke 1 via `resetPage()`.

### Query Builder

Method `getReportsQuery()`:
- Eager load: `user`, `approver`, `department`, `pic`
- Admin: lihat semua laporan
- Non-admin: filter berdasarkan departemen dan level
- Apply semua filter aktif
- Order by `created_at DESC`

### Batch Select

- `$selected` (array): ID laporan yang dipilih
- `$selectAll` (bool): toggle select semua
- `toggleSelectAll()`: select semua ID di halaman ini

### Permission Batch

```php
private function canApproveReports(User $user): bool
{
    return $user->getRoleLevel() >= 2 || $user->isAdmin();
}

private function canDeleteReports(User $user): bool
{
    return $user->isAdmin() || $user->getRoleLevel() >= 5;
}
```

---

## 8. Sistem Notifikasi

> Model notifikasi SiGAP adalah **custom model** (`app/Models/Notification.php`), bukan `Illuminate\Notifications\DatabaseNotification` bawaan Laravel. Schema berbeda.

### Tipe Notifikasi

| Type | Trigger | Penerima |
|------|---------|---------|
| `job_approved` | Laporan diapprove | Pemilik laporan |
| `job_rejected` | Laporan ditolak | Pemilik laporan |
| `pending_approval` | Laporan baru dibuat | Approver (PIC / atasan) |
| `new_comment` | Komentar baru ditambahkan | Owner laporan, PIC, commenter sebelumnya |

### Observer Pattern

**`DailyReportObserver`** (`app/Observers/DailyReportObserver.php`):

```php
// Saat laporan baru dibuat
public function created(DailyReport $report): void
{
    $this->notifyApprovers($report);
}

// Saat approval_status berubah
public function updated(DailyReport $report): void
{
    if ($report->wasChanged('approval_status')) {
        match ($report->approval_status) {
            'approved' => $this->createApprovalNotification($report),
            'rejected' => $this->createRejectionNotification($report),
        };
    }
}
```

**`JobCommentObserver`** (`app/Observers/JobCommentObserver.php`):

```php
// Saat komentar baru dibuat
public function created(JobComment $comment): void
{
    // Notify: pemilik laporan (jika bukan commenter)
    // Notify: PIC laporan (jika berbeda dengan owner & commenter)
    // Notify: commenter sebelumnya (jika bukan owner/PIC/commenter)
    // Skip admin kecuali admin adalah PIC
    // N+1 fix: load semua user dalam 1 query sebelum buat notifikasi
}
```

### Preference Gate

Sebelum membuat notifikasi, observer cek `User::wantsNotification(string $type)`:

```php
public function wantsNotification(string $type): bool
{
    $prefs = $this->notificationPreferences();
    return $prefs[$type] ?? true;
}
```

Admin tidak menerima `pending_approval` untuk mencegah spam.

### AJAX Polling

Notifikasi diupdate via AJAX polling di navbar:
- `GET /notifications` — return JSON `{notifications: [...], unread_count: N}`
- Interval polling: setiap 30 detik (dikonfigurasi di JavaScript)

### API Notifikasi

| Route | Method | Fungsi |
|-------|--------|--------|
| `GET /notifications` | JSON | Ambil 10 notifikasi terbaru + unread count |
| `POST /notifications/mark-as-read` | JSON | Mark notifikasi tertentu dibaca |
| `POST /notifications/mark-all-as-read` | JSON | Mark semua dibaca |
| `POST /notifications/clear-all` | JSON | Hapus semua notifikasi |
| `GET /notifications/all` | View | Halaman semua notifikasi |
| `GET /notifications/preferences` | JSON | Ambil preferensi notifikasi |
| `POST /notifications/preferences` | JSON | Update preferensi |

---

## 9. Komentar (Job Comments)

**Controller:** `app/Http/Controllers/JobCommentController.php`

### Endpoints

| Route | Method | Fungsi |
|-------|--------|--------|
| `GET /daily-reports/{reportId}/comments` | JSON/View | Load komentar |
| `POST /daily-reports/{reportId}/comments` | JSON | Buat komentar baru |
| `DELETE /comments/{commentId}` | JSON | Hapus komentar |

### Aturan Akses

- **Buat komentar**: user harus bisa melihat laporan (`DailyReportPolicy::view()`)
- **Hapus komentar**: hanya pemilik komentar atau admin
- Komentar diurutkan `created_at DESC` di relasi

### Response Format (GET comments)

```json
{
    "success": true,
    "comments": [
        {
            "id": 1,
            "comment": "Isi komentar",
            "visibility": "public",
            "created_at": "2 jam yang lalu",
            "formatted_date": "14 April 2026 10:30",
            "user": {
                "id": 5,
                "name": "Nama User",
                "profile_picture": "https://..."
            },
            "is_owner": true
        }
    ]
}
```

---

## 10. Organization Chart

**Controller:** `app/Http/Controllers/OrganizationChartController.php`
**Route:** `GET /organization-chart`
**View:** `resources/views/organization/chart.blade.php`

### Yang Ditampilkan

- Struktur hierarki departemen user
- Level 1 sampai Level 7 (Level 8 dan Admin dikecualikan dari chart)
- Color-coded badge per level
- Mobile-responsive dengan touch interaction

### Yang Dikecualikan

- **Level 8**: bersifat cross-department, tidak masuk hierarki per-departemen
- **Admin**: peran administrasi sistem, bukan hierarki operasional

---

## 11. Excel Import / Export

### Export Classes (`app/Exports/`)

**`DailyReportsExport`** — export laporan dengan heading dan styling:

| Header | Kolom |
|--------|-------|
| ID, Job Name, Created By, Department, Job Site, Section | |
| Status, Report Date, Due Date, Description, Remarks | |
| PIC, Approval Status, Approved/Rejected By, Rejection Reason | |
| Attachment 1, Attachment 2, Attachment 3, Created At | |

- Styling: header biru (RGB: 4472C4), teks putih, auto-size kolom
- Filter: bisa terima array `$filters` (search, status, department, date range)
- Non-admin hanya export laporan departemennya sendiri

**`DailyReportsTemplateExport`** — template kosong dengan komentar instruksi di setiap header

**`UsersExport`** — export daftar user (ID, Name, Email, User ID, Role, Department, Email Verified, Created At)

**`UsersTemplateExport`** — template import user

### Import Classes (`app/Imports/`)

**`DailyReportsImport`** — import laporan dari Excel:
- Flexible date parsing: mendukung format `d/m/Y`, `Y-m-d`, `d-m-Y`, `Y/m/d`, dan Excel numeric date
- Lookup department by name (case-sensitive)
- Lookup PIC by field `user_id` (bukan `id`)
- Validasi: job_name, department (harus ada di DB), status (enum), tanggal, description, user_id

**`UsersImport`** — import user:
- User ID di-generate dari name jika tidak diisi (`Str::slug($name)`)
- Password default: `password123` jika tidak diisi
- Email nullable, unik
- Role dan Department lookup by name

### Routes Export/Import

| Route | Fungsi |
|-------|--------|
| `GET /daily-reports/export` | Export laporan ter-filter |
| `GET /daily-reports/export-all` | Export semua laporan |
| `GET /daily-reports/export-template` | Download template import |
| `GET /daily-reports/import` | Halaman form import |
| `POST /daily-reports/import` | Proses upload import |

---

## 12. PWA (Progressive Web App)

### Service Worker (`public/sw.js`)

- **Library**: Workbox 7.3.0 (loaded via CDN)
- **Cache version**: `v3.0.0`
- **Strategies**:
  - `CacheFirst` — static assets (CSS, JS, fonts, icons)
  - `NetworkFirst` — halaman HTML dan API routes
  - `StaleWhileRevalidate` — CDN resources (Bootstrap, Chart.js)
- **Offline fallback**: `/offline.html` jika jaringan tidak tersedia

### Instalasi PWA

User dapat menginstall SiGAP sebagai app di:
- Android (Chrome) — install banner otomatis atau via `GET /install-app`
- iOS (Safari) — Add to Home Screen
- Desktop Chrome/Edge — install button di address bar

### Update PWA

Saat service worker baru terdeteksi:
1. `app.js` listen event `waiting` dari service worker
2. Tampilkan banner "Update tersedia"
3. User klik reload → service worker baru mengambil alih → halaman reload otomatis

---

## 13. Report Cleanup System

### Artisan Command

**`php artisan reports:cleanup`**

| Opsi | Default | Fungsi |
|------|---------|--------|
| `--days=N` | 365 | Hapus laporan lebih tua dari N hari |
| `--status=*` | semua | Filter status (`completed`, `approved`, `rejected`) |
| `--dry-run` | false | Tampilkan preview tanpa menghapus |
| `--keep-attachments` | false | Simpan file attachment, hanya hapus record |

Proses deletion:
1. Cari laporan yang memenuhi kriteria (by date + status)
2. Jika dry-run: tampilkan breakdown by status, approval_status, department
3. Jika produksi: minta konfirmasi ganda sebelum eksekusi
4. Hapus dalam chunk 100 laporan (cegah timeout)
5. Hapus komentar dan notifikasi terkait secara cascade
6. Hapus file attachment dari storage (kecuali `--keep-attachments`)

### Admin Web UI

**Route:** `GET /admin/reports/cleanup`
**Controller:** `app/Http/Controllers/Admin/ReportCleanupController.php`

- `GET /admin/reports/cleanup` — halaman dengan statistik storage
- `POST /admin/reports/cleanup/preview` — AJAX preview (return JSON breakdown)
- `POST /admin/reports/cleanup/execute` — eksekusi cleanup

---

## 14. Profile Management

**Controller:** `app/Http/Controllers/ProfileController.php`

### Update Profil

`PATCH /profile` — update nama dan email (via `ProfileUpdateRequest`)

### Upload Foto Profil

`POST /profile/picture` — upload foto profil:
- Simpan ke `storage/app/public/profile_pictures/`
- Gambar dikompresi via Intervention Image (sama seperti attachment)
- Hapus foto lama setelah upload baru berhasil

### Default Avatar

Jika user tidak punya foto profil atau file tidak ditemukan, sistem generate avatar otomatis via **UI Avatars API**:

```php
// Contoh URL yang dihasilkan
https://ui-avatars.com/api/?name=John+Doe&background=3d85c8&color=fff&size=128&rounded=true&bold=true
```

- Warna background ditentukan dari `$user->id % 8` (8 warna berbeda)
- Inisial diambil dari nama user

### Hapus Akun

`DELETE /profile` — hapus akun sendiri (dengan konfirmasi password)
