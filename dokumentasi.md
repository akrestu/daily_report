# Dokumentasi Proyek Daily Report System

## 1. Gambaran Umum

Daily Report System adalah aplikasi web berbasis Laravel yang dirancang untuk memudahkan proses pembuatan, pelacakan, dan pengelolaan laporan kerja harian dalam organisasi. Aplikasi ini mendukung berbagai peran pengguna, departemen, dan memiliki alur kerja persetujuan yang komprehensif.

## 2. Fitur Utama

### Manajemen Laporan Harian
- Pembuatan laporan harian individu atau batch import
- Tampilan detail laporan dengan status progres
- Edit dan hapus laporan (berdasarkan izin)
- Filter dan pencarian laporan berdasarkan tanggal, status, departemen, dll
- Penandaan status laporan: pending, in progress, completed

### Alur Kerja Persetujuan
- Sistem persetujuan multi-level berdasarkan hierarki organisasi
- Status persetujuan: pending, approved, rejected
- Notifikasi otomatis untuk persetujuan atau penolakan
- Kemampuan untuk memberikan alasan penolakan

### Pengelolaan Pengguna
- Peran pengguna: Admin, Department Head, Leader, Staff
- Organisasi pengguna berdasarkan departemen
- Manajemen profil pengguna

### Sistem Notifikasi
- Notifikasi real-time untuk persetujuan, penolakan, dan komentar laporan
- Status dibaca/belum dibaca untuk notifikasi

### Lampiran File
- Dukungan untuk unggah file dengan optimasi gambar otomatis
- Batasan ukuran dan jenis file yang ketat

### Sistem Komentar
- Komentar terstruktur untuk diskusi pada laporan tertentu

### Dashboard
- Dashboard komprehensif dengan statistik laporan dan grafik
- Metrik produktivitas personal dan departemen
- Daftar laporan yang mendekati tenggat waktu

### Import/Export
- Integrasi Excel untuk fungsionalitas import/export
- Template kustom untuk batch import

## 3. Struktur Sistem

### Komponen Utama
1. **Framework Laravel**: Aplikasi ini dibangun menggunakan Laravel dengan arsitektur MVC
2. **Database**: MySQL/PostgreSQL dengan Eloquent ORM
3. **Frontend**: Template Blade dengan komponen Livewire untuk interaksi dinamis
4. **Otentikasi**: Sistem otentikasi bawaan Laravel dengan middleware kustom untuk kontrol akses berbasis peran
5. **Penyimpanan File**: Laravel Storage untuk manajemen file

## 4. Model Data Utama

### DailyReport
- ID
- User ID (pembuat)
- Nama pekerjaan (job_name)
- Departemen
- Tanggal laporan
- Tanggal tenggat
- Deskripsi
- Catatan (remark)
- Status (pending, in_progress, completed)
- Status persetujuan (approval_status)
- PIC (job_pic)
- Disetujui oleh (approved_by)
- Alasan penolakan
- Jalur lampiran
- Nama asli lampiran

### User
- ID
- Nama
- Email
- Departemen
- Peran (Role)

### Department
- ID
- Nama

### JobComment
- ID
- Daily Report ID
- User ID
- Konten komentar
- Timestamp

### Notification
- ID
- User ID
- Daily Report ID
- Tipe
- Pesan
- Status dibaca

## 5. Peran Pengguna dan Hak Akses

### Admin
- Akses penuh ke semua fitur dan alat manajemen
- Dapat melihat semua laporan dari semua departemen
- Dapat menyetujui/menolak laporan dari semua departemen
- Mengelola pengguna dan departemen

### Department Head
- Dapat menyetujui laporan dalam departemennya
- Melihat statistik dan laporan departemen
- Memantau kinerja anggota departemen

### Leader
- Dapat menyetujui laporan dari anggota staf dalam departemennya
- Dapat melihat laporan tim dan statistik

### Staff
- Membuat dan mengirimkan laporan untuk persetujuan
- Mengedit laporan mereka sendiri (sebelum disetujui)
- Melihat laporan yang ditetapkan kepada mereka

## 6. Alur Kerja

### Pembuatan Laporan
1. Pengguna mengisi formulir laporan harian dengan informasi yang diperlukan
2. Pengguna dapat melampirkan file (gambar, dokumen)
3. Laporan disimpan dengan status "pending" dan "approval_status: pending"
4. Notifikasi dikirim ke approver (PIC)

### Proses Persetujuan
1. Approver menerima notifikasi laporan yang memerlukan persetujuan
2. Approver dapat:
   - Menyetujui laporan (status berubah menjadi "approved")
   - Menolak laporan dengan alasan (status berubah menjadi "rejected")
3. Notifikasi dikirim ke pembuat laporan tentang hasil persetujuan

### Pengelolaan Laporan
- Pemfilteran berdasarkan status, departemen, tanggal, dll.
- Export data laporan ke Excel
- Import data dari template Excel
- Melihat statistik dan tren laporan

## 7. Keamanan

1. **Otentikasi**: Menggunakan Laravel Sanctum untuk otentikasi aman
2. **Otorisasi**: Kontrol akses berbasis peran melalui middleware dan kebijakan
3. **Validasi File**: Validasi ketat untuk unggahan file dengan batasan ukuran dan tipe
4. **Perlindungan CSRF**: Perlindungan CSRF bawaan Laravel
5. **Validasi Input**: Validasi permintaan untuk semua pengiriman formulir

## 8. Optimasi Kinerja

1. **Pengolahan Gambar**: Menggunakan Intervention Image untuk optimasi gambar
2. **Optimasi Query**: Eager loading relasi untuk mencegah masalah query N+1
3. **Paginasi**: Implementasi paginasi untuk set data besar

## 9. Instalasi dan Konfigurasi

### Persyaratan
- PHP 8.1 atau lebih tinggi
- Composer
- MySQL/PostgreSQL
- Node.js dan NPM

### Langkah Setup
1. Kloning repositori
2. Jalankan `composer install`
3. Jalankan `npm install && npm run dev`
4. Salin `.env.example` ke `.env` dan konfigurasikan pengaturan database
5. Jalankan `php artisan key:generate`
6. Jalankan `php artisan migrate --seed`
7. Jalankan `php artisan storage:link`
8. Mulai server dengan `php artisan serve`

## 10. Panduan Penggunaan

### Login
1. Buka aplikasi di browser
2. Masukkan email dan password
3. Klik tombol "Login"

### Dashboard
- Ringkasan laporan (pending, approved, rejected)
- Aktivitas terbaru
- Akses cepat ke fungsi umum
- Indikator notifikasi

### Membuat Laporan Baru
1. Klik "Create New Report" di dashboard atau menu
2. Isi formulir dengan informasi yang diperlukan
3. Unggah lampiran jika diperlukan
4. Klik "Submit"

### Melihat dan Mengelola Laporan
1. Akses menu laporan sesuai kategori (semua, milik saya, ditugaskan)
2. Gunakan filter untuk menyaring hasil
3. Klik pada laporan untuk melihat detail
4. Gunakan tombol aksi untuk persetujuan, penolakan, edit, atau hapus (berdasarkan izin)

### Menyetujui atau Menolak Laporan
1. Akses daftar laporan yang memerlukan persetujuan
2. Tinjau detail laporan
3. Pilih untuk menyetujui atau menolak dengan alasan
4. Konfirmasi tindakan

### Export dan Import Data
1. Gunakan tombol export di halaman daftar laporan
2. Untuk import, gunakan fitur import dan unggah file template yang diisi

## 11. Pemecahan Masalah

- Masalah umum dan solusinya dapat ditemukan di dokumentasi troubleshooting
- Sistem logging terintegrasi untuk membantu mendiagnosis masalah
- Validasi error yang jelas untuk masalah input pengguna

## 12. Dukungan dan Kontak

- Dokumentasi dapat diakses di folder `docs/`
- Panduan teknis: `docs/technical_documentation.md`
- Panduan pengguna: `docs/user_guide.md`

## 13. Diagram Alur Aplikasi

```
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│                 │      │                 │      │                 │
│   User / Staff  ├─────►│   Create/Edit   ├─────►│   PIC/Leader    │
│                 │      │     Report      │      │                 │
└─────────────────┘      └─────────────────┘      └────────┬────────┘
                                                          │
                                                          │
┌─────────────────┐      ┌─────────────────┐      ┌───────▼────────┐
│                 │      │                 │      │                │
│    Notifikasi   │◄─────┤   Department    │◄─────┤    Approval    │
│                 │      │      Head       │      │    Process     │
└─────────────────┘      └─────────────────┘      └────────────────┘
```

## 14. Struktur Folder Proyek

- **app/** - Logika utama aplikasi
  - **Console/** - Perintah artisan
  - **Exports/** - Kelas untuk fungsi export
  - **Http/** - Controllers, Middleware
  - **Imports/** - Kelas untuk fungsi import
  - **Livewire/** - Komponen Livewire
  - **Models/** - Model data
  - **Observers/** - Observer untuk event model
  - **Policies/** - Kebijakan otorisasi

- **resources/** - Aset frontend
  - **views/** - Template Blade
  
- **public/** - File yang dapat diakses publik
  
- **database/** - Migrasi dan seeders database

- **docs/** - Dokumentasi proyek

## 15. Changelog dan Versi

### Versi 1.0.0 (28 April 2025)
- Rilis awal aplikasi
- Semua fitur utama telah diimplementasikan
- Dashboard untuk semua jenis pengguna
- Sistem manajemen laporan lengkap
- Import/export data
