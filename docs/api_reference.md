# SiGAP — API & Route Reference

> SiGAP menggunakan session-based authentication (bukan token API). Semua request harus menyertakan CSRF token.

---

## Daftar Isi

1. [Authentication](#1-authentication)
2. [Ringkasan Route Groups](#2-ringkasan-route-groups)
3. [AJAX / JSON Endpoints](#3-ajax--json-endpoints)
4. [Form POST Payloads](#4-form-post-payloads)
5. [JSON Response Schemas](#5-json-response-schemas)
6. [Route File Accessor (File Attachment)](#6-route-file-accessor-file-attachment)
7. [Static Asset Routes (PWA)](#7-static-asset-routes-pwa)
8. [Resource Routes (CRUD)](#8-resource-routes-crud)
9. [Admin Routes](#9-admin-routes)

---

## 1. Authentication

SiGAP menggunakan **Laravel Breeze** dengan session-based authentication.

- **Login:** `POST /login` (form dengan `email`, `password`, `remember`)
- **Logout:** `POST /logout`
- **Middleware:** semua route memerlukan `auth` middleware
- **CSRF Protection:** semua `POST`, `PATCH`, `PUT`, `DELETE` wajib menyertakan:
  - Form: field `_token` (otomatis dengan Blade `@csrf`)
  - AJAX: header `X-XSRF-TOKEN` (otomatis oleh Axios dari cookie)

---

## 2. Ringkasan Route Groups

| Prefix | Middleware | Controller/Action |
|--------|-----------|-------------------|
| `/` (public) | — | Redirect, static assets |
| `/login`, `/logout`, dll | guest | Laravel Breeze auth |
| `/dashboard` | `auth`, `verified` | DashboardController |
| `/` (main) | `auth` | Reports, Profile, Notifications, dll |
| `/admin/*` | `auth`, `admin.only` | Admin controllers |

**Middleware `admin.only`** didefinisikan di `app/Http/Middleware/AdminOnly.php` dan di-alias di `app/Providers/MiddlewareServiceProvider.php`.

---

## 3. AJAX / JSON Endpoints

Endpoint yang mengembalikan JSON response (digunakan untuk AJAX/fetch di frontend):

### Notifikasi

| Method | URI | Auth | Response |
|--------|-----|------|----------|
| `GET` | `/notifications` | auth | `{notifications: [...], unread_count: N}` |
| `POST` | `/notifications/mark-as-read` | auth | `{success: true, unread_count: N}` |
| `POST` | `/notifications/mark-all-as-read` | auth | `{success: true, unread_count: 0}` |
| `POST` | `/notifications/clear-all` | auth | `{success: true}` |
| `GET` | `/notifications/preferences` | auth | `{preferences: {...}}` |
| `POST` | `/notifications/preferences` | auth | `{success: true}` |

### Komentar

| Method | URI | Auth | Response |
|--------|-----|------|----------|
| `GET` | `/daily-reports/{reportId}/comments` | auth | `{success: true, comments: [...]}` |
| `POST` | `/daily-reports/{reportId}/comments` | auth | `{success: true, comment: {...}}` |
| `DELETE` | `/comments/{commentId}` | auth | `{success: true, message: "..."}` |

### Sections (Cascade Dropdown)

| Method | URI | Auth | Response |
|--------|-----|------|----------|
| `GET` | `/sections/by-department?department_id=N` | auth | `[{id, name, code}, ...]` |
| `GET` | `/admin/sections/by-department?department_id=N` | auth + admin | `[{id, name, code}, ...]` |

### Approval Laporan

| Method | URI | Auth | Response |
|--------|-----|------|----------|
| `POST` | `/daily-reports/{dailyReport}/approval` | auth | Redirect (atau JSON jika AJAX) |

### Batch Operations

| Method | URI | Auth | Response |
|--------|-----|------|----------|
| `POST` | `/daily-reports/batch-approve` | auth | Redirect dengan flash message |
| `POST` | `/daily-reports/batch-reject` | auth | Redirect dengan flash message |
| `DELETE` | `/daily-reports/batch-delete` | auth | Redirect dengan flash message |

### Admin — Report Cleanup

| Method | URI | Auth | Response |
|--------|-----|------|----------|
| `POST` | `/admin/reports/cleanup/preview` | admin | `{count, breakdown: {...}}` |
| `POST` | `/admin/reports/cleanup/execute` | admin | `{success, deleted_count, freed_space}` |

### Dashboard

| Method | URI | Auth | Response |
|--------|-----|------|----------|
| `POST` | `/dashboard/toggle-sidebar` | auth | `{success: true}` |

---

## 4. Form POST Payloads

### Approval Laporan

```http
POST /daily-reports/{id}/approval
Content-Type: application/x-www-form-urlencoded

status=approved&_token=csrf_token
```

```http
POST /daily-reports/{id}/approval
Content-Type: application/x-www-form-urlencoded

status=rejected&rejection_reason=Deskripsi+kurang+detail&_token=csrf_token
```

| Field | Type | Required | Nilai |
|-------|------|----------|-------|
| `status` | string | Ya | `approved` atau `rejected` |
| `rejection_reason` | string | Jika rejected | Alasan penolakan |

### Buat Komentar

```http
POST /daily-reports/{reportId}/comments
Content-Type: application/json

{"comment": "Isi komentar"}
```

| Field | Type | Required | Keterangan |
|-------|------|----------|------------|
| `comment` | string | Ya | Isi komentar |

### Batch Approve/Reject

```http
POST /daily-reports/batch-approve
Content-Type: application/x-www-form-urlencoded

ids[]=1&ids[]=2&ids[]=3&_token=csrf_token
```

```http
POST /daily-reports/batch-reject
Content-Type: application/x-www-form-urlencoded

ids[]=1&ids[]=2&rejection_reason=Alasan&_token=csrf_token
```

| Field | Type | Required | Keterangan |
|-------|------|----------|------------|
| `ids[]` | array | Ya | Max 100 ID |
| `rejection_reason` | string | Jika reject | Alasan penolakan |

### Update Preferensi Notifikasi

```http
POST /notifications/preferences
Content-Type: application/json

{
    "job_approved": true,
    "job_rejected": true,
    "pending_approval": false,
    "new_comment": true,
    "email_notifications": false
}
```

### Upload Foto Profil

```http
POST /profile/picture
Content-Type: multipart/form-data

picture=<file>&_token=csrf_token
```

### Import Laporan / User

```http
POST /daily-reports/import
Content-Type: multipart/form-data

file=<excel_file>&_token=csrf_token
```

---

## 5. JSON Response Schemas

### Notification Object

```json
{
    "id": 1,
    "user_id": 5,
    "daily_report_id": 10,
    "comment_id": null,
    "type": "job_approved",
    "message": "Laporan 'Maintenance Server' Anda telah disetujui",
    "is_read": false,
    "created_at": "2026-04-14T08:30:00.000000Z",
    "daily_report": {
        "id": 10,
        "job_name": "Maintenance Server",
        "user_id": 5
    },
    "comment": null
}
```

**Type values:** `job_approved` | `job_rejected` | `pending_approval` | `new_comment`

### Comment Object

```json
{
    "id": 1,
    "comment": "Sudah dikerjakan, menunggu approval",
    "visibility": "public",
    "created_at": "2 jam yang lalu",
    "formatted_date": "14 April 2026 10:30",
    "user": {
        "id": 5,
        "name": "John Doe",
        "profile_picture": "https://ui-avatars.com/api/?name=John+Doe&..."
    },
    "is_owner": true
}
```

### Notification Preferences Object

```json
{
    "preferences": {
        "job_approved": true,
        "job_rejected": true,
        "pending_approval": true,
        "new_comment": true,
        "email_notifications": false
    }
}
```

### Section Dropdown Object

```json
[
    {"id": 1, "name": "Network", "code": "NET"},
    {"id": 2, "name": "Hardware", "code": "HW"}
]
```

### Report Cleanup Preview

```json
{
    "count": 45,
    "breakdown": {
        "by_status": {
            "completed": 30,
            "approved": 10,
            "rejected": 5
        },
        "by_department": {
            "IT": 20,
            "HR": 15,
            "Finance": 10
        },
        "oldest_date": "2023-01-15",
        "estimated_size": "125.5 MB"
    }
}
```

---

## 6. Route File Accessor (File Attachment)

```http
GET /storage/attachments/{filename}
Authorization: Session (middleware: auth)
```

**Proses:**
1. Sanitasi: `basename($filename)` untuk cegah path traversal
2. Cek file ada di `storage/app/public/attachments/`
3. Cek izin user (salah satu harus benar):
   - User adalah admin
   - User adalah pemilik laporan yang punya attachment ini
   - User adalah PIC laporan
   - User ada di departemen yang sama dengan pemilik laporan
4. Tentukan cara serve:

| MIME Type | Cara Serve |
|-----------|------------|
| `image/jpeg`, `image/png`, `image/gif` | Inline (tampil di browser) |
| `application/pdf` | Inline |
| `text/plain` | Inline |
| Semua lainnya | Attachment (force download) |

**Security Headers yang ditambahkan:**
```
X-Content-Type-Options: nosniff
Content-Security-Policy: default-src 'none'
```

---

## 7. Static Asset Routes (PWA)

```http
GET /icons/{filename}
Cache-Control: public, max-age=31536000 (1 tahun)
```

```http
GET /screenshots/{filename}
Cache-Control: public, max-age=31536000 (1 tahun)
```

Route ini berfungsi sebagai fallback jika `.htaccess` tidak melayani file statik dengan benar (misal di beberapa shared hosting).

**Icon yang tersedia** di `public/icons/`:
- `icon-48x48.png`, `icon-72x72.png`, `icon-96x96.png`
- `icon-144x144.png`, `icon-180x180.png`, `icon-192x192.png`
- `icon-512x512.png`

**Manifest PWA** tersedia di:
```
GET /site.webmanifest
GET /manifest.json
```

---

## 8. Resource Routes (CRUD)

### Daily Reports

```
GET     /daily-reports              index   - DailyReportController@index
GET     /daily-reports/create       create  - DailyReportController@create
POST    /daily-reports              store   - DailyReportController@store
GET     /daily-reports/{id}         show    - DailyReportController@show
GET     /daily-reports/{id}/edit    edit    - DailyReportController@edit
PUT     /daily-reports/{id}         update  - DailyReportController@update
DELETE  /daily-reports/{id}         destroy - DailyReportController@destroy
```

### Halaman Khusus Laporan

```
GET     /daily-reports/pending          - Laporan menunggu approval
GET     /daily-reports/my-jobs          - Laporan milik saya
GET     /daily-reports/assigned-jobs    - Laporan di mana saya PIC
POST    /daily-reports/store-multiple   - Buat banyak laporan sekaligus
```

---

## 9. Admin Routes

Semua route `/admin/*` memerlukan middleware `admin.only`.

### Dashboard & Reports View

```
GET     /admin/dashboard
GET     /admin/reports                  - DailyReportController@index (full access)
GET     /admin/reports/{id}             - DailyReportController@show
GET     /admin/settings
```

### User Management (`/admin/users`)

```
GET     /admin/users              index
GET     /admin/users/create       create
POST    /admin/users              store
GET     /admin/users/{id}         show
GET     /admin/users/{id}/edit    edit
PUT     /admin/users/{id}         update
DELETE  /admin/users/{id}         destroy
DELETE  /admin/users/batch-delete batchDelete
GET     /admin/users/export       export
GET     /admin/users/import       showImport
POST    /admin/users/import       import
GET     /admin/users/export-template exportTemplate
```

### Department Management (`/admin/departments`)

```
GET     /admin/departments              index
GET     /admin/departments/create       create
POST    /admin/departments              store
GET     /admin/departments/{id}         show
GET     /admin/departments/{id}/edit    edit
PUT     /admin/departments/{id}         update
DELETE  /admin/departments/{id}         destroy
DELETE  /admin/departments/batch-delete batchDelete
```

### Job Site Management (`/admin/job-sites`)

```
GET     /admin/job-sites              index
GET     /admin/job-sites/create       create
POST    /admin/job-sites              store
GET     /admin/job-sites/{id}         show
GET     /admin/job-sites/{id}/edit    edit
PUT     /admin/job-sites/{id}         update
DELETE  /admin/job-sites/{id}         destroy
DELETE  /admin/job-sites/batch-delete batchDelete
```

### Section Management (`/admin/sections`)

```
GET     /admin/sections              index
GET     /admin/sections/create       create
POST    /admin/sections              store
GET     /admin/sections/{id}         show
GET     /admin/sections/{id}/edit    edit
PUT     /admin/sections/{id}         update
DELETE  /admin/sections/{id}         destroy
DELETE  /admin/sections/batch-delete batchDelete
GET     /admin/sections/by-department  getByDepartment (JSON)
```

### Report Cleanup

```
GET     /admin/reports/cleanup          index
POST    /admin/reports/cleanup/preview  preview (JSON)
POST    /admin/reports/cleanup/execute  execute (JSON)
```

---

## Appendix: Debug Routes

> Hanya untuk development. Hapus atau amankan di produksi.

```
GET     /debug/roles                 DebugController@debugRoles
GET     /debug/comments/{reportId}   Inline closure (JSON)
GET     /test-create-user            TestController@testCreateUser
GET     /test-chart                  View test chart
```
