# SiGAP — Panduan Pengguna

> Panduan untuk semua pengguna berdasarkan role masing-masing.

---

## Daftar Isi

1. [Login & Awal Penggunaan](#1-login--awal-penggunaan)
2. [Dashboard](#2-dashboard)
3. [Panduan per Role](#3-panduan-per-role)
4. [Membuat Laporan Harian](#4-membuat-laporan-harian)
5. [Melihat & Mengelola Laporan](#5-melihat--mengelola-laporan)
6. [Approval Laporan](#6-approval-laporan)
7. [Komentar pada Laporan](#7-komentar-pada-laporan)
8. [Notifikasi](#8-notifikasi)
9. [Organization Chart](#9-organization-chart)
10. [Import & Export Excel](#10-import--export-excel)
11. [Profil & Pengaturan](#11-profil--pengaturan)

---

## 1. Login & Awal Penggunaan

1. Buka browser dan akses URL aplikasi SiGAP
2. Masukkan **Email** dan **Password**
3. Klik tombol **Login**

Setelah login, Anda akan diarahkan ke **Dashboard** yang menampilkan ringkasan aktivitas sesuai role Anda.

### Pengaturan Awal yang Disarankan

Setelah pertama kali login:
1. Update profil Anda: klik nama/avatar di pojok kanan atas → **Profile**
2. Atur foto profil (opsional)
3. Atur preferensi notifikasi sesuai kebutuhan

---

## 2. Dashboard

Dashboard menampilkan informasi berbeda berdasarkan role:

| Informasi | Level 1-4 | Level 5-7 | Level 8 | Admin |
|-----------|-----------|-----------|---------|-------|
| Statistik laporan pribadi | ✓ | ✓ | ✓ | ✓ |
| Statistik departemen | — | ✓ | — | ✓ |
| Statistik seluruh sistem | — | — | — | ✓ |
| Laporan mendesak (due date) | ✓ | ✓ | ✓ | ✓ |
| Chart tren laporan | ✓ | ✓ | ✓ | ✓ |
| Top performers | — | ✓ | ✓ | ✓ |

---

## 3. Panduan per Role

### Level 1 — Pelaksana / Field Worker

**Bisa:**
- Membuat laporan harian dan menugaskan PIC ke Level 2
- Melihat laporan milik sendiri
- Melihat laporan yang sudah completed/approved di departemen (referensi)
- Mengedit/menghapus laporan sendiri yang masih `pending`
- Menambahkan komentar pada laporan yang bisa dilihat

**Tidak bisa:**
- Meng-approve laporan siapapun
- Menjadi PIC (Level 1 tidak bisa jadi PIC)
- Melihat laporan milik Level 2 ke atas

---

### Level 2 — Supervisor / Koordinator

**Bisa:**
- Membuat laporan dan menugaskan PIC ke Level 3
- Meng-approve laporan dari Level 1
- Melihat semua laporan Level 1 di departemen yang sama
- Batch approve/reject laporan Level 1

**Tidak bisa:**
- Meng-approve laporan Level 2 ke atas
- Melihat laporan Level 3 ke atas

---

### Level 3 — Senior Supervisor

**Bisa:**
- Membuat laporan dan menugaskan PIC ke Level 4
- Meng-approve laporan dari Level 2
- Melihat semua laporan Level 1 dan Level 2 di departemen

---

### Level 4 — Manager Tingkat Pertama

**Bisa:**
- Membuat laporan dan menugaskan PIC ke Level 5
- Meng-approve laporan dari Level 3
- Melihat semua laporan dalam departemen

---

### Level 5 — Manager Senior / Kepala Seksi

**Bisa:**
- Membuat laporan dan menugaskan PIC ke Level 6
- Meng-approve laporan dari Level 4
- Melihat semua laporan dalam departemen
- **Batch delete** laporan dalam departemen

---

### Level 6 — Manager Departemen

**Bisa:**
- Membuat laporan dan menugaskan PIC ke Level 7 atau Level 8
- Meng-approve laporan dari Level 5
- Melihat semua laporan dalam departemen
- Batch delete laporan dalam departemen

---

### Level 7 — Kepala Departemen

**Bisa:**
- Membuat laporan dan menugaskan PIC ke Level 8
- Meng-approve laporan dari Level 6
- Melihat semua laporan dalam departemen
- Batch delete laporan dalam departemen

---

### Level 8 — Direktur / Site Manager

**Bisa:**
- Meng-approve laporan dari Level 6 DAN Level 7
- Melihat semua laporan dalam job site yang sama (lintas departemen)
- Batch delete laporan dalam job site yang sama

**Tidak bisa:**
- **Membuat laporan baru** (Level 8 adalah role monitoring/approval saja)

> Level 8 memiliki visibilitas lintas departemen selama berada di job site yang sama.

---

### Admin — Administrator Sistem

**Bisa:**
- Semua yang bisa dilakukan Level 8
- Meng-approve laporan siapapun
- Mengelola user, departemen, job site, seksi
- Mengakses panel admin (`/admin`)
- Melihat dan menghapus semua laporan di sistem
- Melakukan cleanup laporan lama

---

## 4. Membuat Laporan Harian

**Catatan:** Level 8 tidak dapat membuat laporan.

### Langkah-langkah

1. Klik **Laporan Harian** di menu sidebar
2. Klik tombol **Buat Laporan Baru**
3. Isi form laporan:

| Field | Wajib | Keterangan |
|-------|-------|------------|
| Nama Pekerjaan | ✓ | Judul singkat pekerjaan |
| Departemen | ✓ | Departemen Anda |
| Job Site | — | Lokasi pekerjaan (opsional) |
| Seksi | — | Seksi dalam departemen (opsional) |
| Tanggal Laporan | ✓ | Tanggal pekerjaan |
| Tanggal Target | ✓ | Deadline (tidak boleh sebelum tanggal laporan) |
| PIC | ✓ | Person In Charge (atasan Anda) |
| Status | ✓ | `Pending`, `In Progress`, atau `Completed` |
| Deskripsi | ✓ | Penjelasan detail pekerjaan |
| Catatan | — | Catatan tambahan (opsional) |
| Attachment 1-3 | — | File pendukung (maks 5MB per file) |

4. Klik **Simpan**

### Format Attachment yang Didukung

- Gambar: `.jpg`, `.jpeg`, `.png`, `.gif`, `.svg`
- Dokumen: `.pdf`, `.doc`, `.docx`
- Spreadsheet: `.xls`, `.xlsx`
- Maks ukuran: **5 MB per file**
- Gambar otomatis dikompresi saat upload

### Membuat Banyak Laporan Sekaligus

Klik **Tambah Laporan Lagi** untuk menambahkan form laporan kedua, ketiga, dst. Semua akan disimpan sekaligus saat klik **Simpan Semua**.

---

## 5. Melihat & Mengelola Laporan

### Tampilan Daftar Laporan

Tersedia beberapa tampilan daftar:

| Menu | Isi |
|------|-----|
| **Semua Laporan** | Semua laporan yang bisa Anda lihat (sesuai role) |
| **Laporan Pending** | Laporan yang menunggu approval dari Anda |
| **Laporan Saya** | Laporan yang Anda buat |
| **Laporan Ditugaskan** | Laporan di mana Anda adalah PIC |

### Filter & Pencarian

Di halaman daftar laporan, Anda bisa:
- **Cari**: berdasarkan nama pekerjaan, deskripsi, atau catatan
- **Filter Status**: pending, in_progress, completed, rejected
- **Filter Departemen**: pilih departemen tertentu
- **Filter Tanggal**: rentang tanggal laporan

### Detail Laporan

Klik nama laporan untuk melihat detail. Halaman detail memiliki tab:
1. **Deskripsi** — informasi lengkap laporan
2. **Catatan** — remark tambahan
3. **Komentar** — diskusi dan komunikasi

### Edit Laporan

Laporan hanya bisa diedit jika:
- Anda adalah pemilik laporan, DAN
- Status approval masih `pending`

### Hapus Laporan

- Level 1–4: hanya bisa hapus laporan sendiri yang masih `pending`
- Level 5–7: bisa hapus laporan dalam departemen sendiri
- Level 8: bisa hapus laporan dalam job site yang sama
- Admin: bisa hapus semua laporan

---

## 6. Approval Laporan

### Melihat Laporan yang Perlu Diapprove

Klik **Laporan Pending** di sidebar untuk melihat laporan yang menunggu approval dari Anda.

> Anda hanya akan melihat laporan dari pengguna yang berada **satu level di bawah Anda**.
> Level 8 melihat laporan dari Level 6 dan Level 7.

### Meng-Approve Laporan

**Cara 1 — Dari halaman detail:**
1. Buka laporan yang ingin diapprove
2. Klik tombol **Setujui** (hijau) atau **Tolak** (merah)
3. Jika menolak, isi alasan penolakan
4. Konfirmasi

**Cara 2 — Batch Approve:**
1. Di halaman daftar laporan, centang laporan yang ingin diapprove
2. Klik **Setujui Terpilih** atau **Tolak Terpilih**
3. Untuk penolakan massal, isi alasan yang sama untuk semua

### Status Setelah Approval

| Aksi | approval_status | status |
|------|-----------------|--------|
| Disetujui | `approved` | `completed` |
| Ditolak | `rejected` | Tidak berubah |

Pemilik laporan akan menerima notifikasi otomatis.

---

## 7. Komentar pada Laporan

### Menambahkan Komentar

1. Buka halaman detail laporan
2. Klik tab **Komentar**
3. Ketik komentar di kolom yang tersedia
4. Klik **Kirim**

Semua komentar bersifat **publik** — dapat dilihat oleh siapapun yang bisa melihat laporan tersebut.

### Menghapus Komentar

Hanya pemilik komentar atau admin yang bisa menghapus komentar.

### Notifikasi Komentar

Saat Anda menambahkan komentar, notifikasi akan dikirim ke:
- Pemilik laporan (jika bukan Anda)
- PIC laporan (jika berbeda dengan pemilik dan Anda)
- Pengguna yang pernah berkomentar di laporan yang sama (jika belum dinotifikasi)

---

## 8. Notifikasi

### Melihat Notifikasi

Klik ikon **lonceng** di navbar untuk melihat notifikasi terbaru. Badge merah menunjukkan jumlah notifikasi yang belum dibaca.

Klik **Lihat Semua** untuk halaman notifikasi lengkap.

### Tipe Notifikasi

| Tipe | Kapan Diterima |
|------|----------------|
| **Laporan Disetujui** | Laporan Anda disetujui atasan |
| **Laporan Ditolak** | Laporan Anda ditolak atasan |
| **Menunggu Approval** | Ada laporan baru yang perlu Anda approve |
| **Komentar Baru** | Ada komentar baru di laporan terkait |

### Mengatur Preferensi Notifikasi

1. Klik nama/avatar Anda di navbar
2. Pilih **Preferensi Notifikasi**
3. Aktifkan/nonaktifkan tipe notifikasi yang diinginkan
4. Klik **Simpan**

Notifikasi email saat ini dalam pengembangan (default: nonaktif).

### Mengelola Notifikasi

- **Tandai Dibaca**: klik satu notifikasi atau "Tandai Semua Dibaca"
- **Hapus Semua**: klik "Hapus Semua Notifikasi"

---

## 9. Organization Chart

Klik **Org Chart** di sidebar untuk melihat struktur organisasi.

Chart menampilkan:
- Hierarki Level 1 sampai Level 7 dalam departemen Anda
- Nama dan role setiap anggota tim
- Badge warna berbeda per level

> Level 8 dan Admin tidak ditampilkan dalam org chart (bersifat lintas departemen / administratif).

---

## 10. Import & Export Excel

### Export Laporan

**Export laporan Anda:**
1. Buka halaman **Semua Laporan**
2. Atur filter jika diperlukan
3. Klik **Export** untuk export laporan yang ter-filter
4. Klik **Export Semua** untuk semua laporan

**Format export:** Excel (`.xlsx`) dengan heading dan styling.

### Import Laporan dari Excel

1. Download template terlebih dahulu: klik **Download Template**
2. Isi template sesuai instruksi pada kolom header
3. Klik **Import**, pilih file Excel
4. Klik **Upload**

**Panduan mengisi template:**

| Kolom | Keterangan |
|-------|------------|
| `job_name` | Nama pekerjaan (wajib) |
| `department` | Nama departemen persis seperti di sistem |
| `job_site` | Nama job site (opsional) |
| `section` | Nama seksi (opsional) |
| `status` | `pending`, `in_progress`, atau `completed` |
| `report_date` | Format: DD/MM/YYYY |
| `due_date` | Format: DD/MM/YYYY, harus >= report_date |
| `description` | Deskripsi pekerjaan (wajib) |
| `remark` | Catatan tambahan (opsional) |
| `user_id` | User ID dari PIC (lihat daftar user di admin) |

---

## 11. Profil & Pengaturan

### Mengupdate Profil

1. Klik nama/avatar Anda di navbar
2. Pilih **Profil**
3. Update nama atau email
4. Klik **Simpan**

### Upload Foto Profil

1. Di halaman profil, klik area foto profil
2. Pilih file gambar (JPG, PNG, dll)
3. Klik **Upload Foto**

Jika tidak ada foto profil, sistem akan menampilkan avatar default berdasarkan inisial nama Anda.

### Mengubah Password

1. Di halaman profil, scroll ke bagian **Ubah Password**
2. Isi password lama dan password baru (minimal 8 karakter)
3. Konfirmasi password baru
4. Klik **Simpan**

### Menghapus Akun

1. Di halaman profil, scroll ke bagian **Hapus Akun**
2. Klik **Hapus Akun**
3. Masukkan password untuk konfirmasi
4. Data Anda akan dihapus permanen
