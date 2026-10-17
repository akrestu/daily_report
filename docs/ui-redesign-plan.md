# Rencana Implementasi Redesign UI/UX SiGAP

Sumber desain: `design/` (DESIGN_SYSTEM.md, tokens.css/json, 58 artboard di `mockups/dc-source/`, aset di `assets/`).
Target: seluruh UI Blade yang ada dimigrasikan ke design system "Calm Enterprise, Data-Dense" (hitam + kuning, mobile-first), **tanpa mengubah logika bisnis, route, atau controller** kecuali disebut eksplisit.

---

## 1. Kondisi saat ini (hasil audit)

| Aspek | Sekarang | Target |
|---|---|---|
| Layout | `layouts/app.blade.php` (963 baris, banyak inline `<style>`/`<script>`), `navigation.blade.php`, `guest`, `public`; 38 view memakai `<x-app-layout>`, 4 memakai `@extends('layouts.public')` | Shell baru: top app bar + bottom nav (mobile), rail (tablet), sidebar + topbar (desktop) |
| CSS | Bootstrap 5.3.2 via CDN + `public/css/custom.css` + `dashboard.css` + `resources/css/app.css` (redefinisi variabel Bootstrap, Tailwind 4 terpasang) | Bootstrap 5.3 + `tokens.css` (`.sg-*`) lewat Vite; Tailwind dihapus dari jalur halaman |
| Font | Poppins | Plus Jakarta Sans (heading) + Inter (body) |
| Ikon | Font Awesome 6 (dipakai ±58 view) | Bootstrap Icons |
| Warna | Gradien biru #0d6efd → ungu #6610f2 | Primary #111111, aksen kuning #F5D334, status semantik |
| Interaksi | Vanilla JS inline + Alpine (sebagian) | Alpine.js 3 terpusat per komponen |
| PWA | `public/sw.js`, manifest ada | Ditambah draf offline (IndexedDB) untuk Buat Laporan |
| Halaman terbesar | `daily-reports/index` (1.337 baris), `show` (933), `profile/edit` (900), `pending` (868) | Dipecah ke partial/komponen Blade |

---

## 2. Prinsip eksekusi

1. **Bertahap & selalu bisa dirilis.** Setiap fase menghasilkan aplikasi yang utuh; tidak ada "big bang".
2. **Fondasi dulu, layar kemudian.** Token, layout, dan komponen Blade selesai sebelum layar dimigrasikan, supaya layar cukup *merakit* komponen.
3. **Komponen Blade anonim** (`resources/views/components/sg/*`) sebagai satu-satunya tempat markup badge, kartu, sheet, dsb. Mockup `.dc.html` diterjemahkan ke komponen ini, bukan disalin inline style-nya.
4. **Logika tetap di server.** Status, label, warna diambil dari satu sumber (`config/sigap-ui.php` dari `tokens.json`) agar Blade, Excel export, dan notifikasi konsisten.
5. **Fitur baru yang belum ada di backend** (swipe approve dengan Urungkan, shortcut J/K, draf offline, Ctrl K search) dikerjakan di fase terpisah setelah paritas visual tercapai.
6. Setiap layar selesai = cocok dengan mockup mobile + desktop (+ tablet bila ada), state empty/skeleton/error lengkap, dan test Feature lama tetap hijau.

---

## 3. Fase

### Fase 0 — Persiapan (½ hari)
- Gabungkan aturan penting `design/CLAUDE.md` ke `CLAUDE.md` root (bagian "UI Design System"), **jangan menimpa** CLAUDE.md root yang sudah berisi aturan backend. Perbarui juga bagian "UI/UX Design" yang masih menyebut gradien biru-ungu.
- Pindahkan aset: `design/assets/*` → `public/images/brand/` (kompres `login-mining-photo.jpg` ke WebP ≤ 200 KB).
- Buat branch `feature/ui-redesign`.
- Ambil screenshot "sebelum" tiap layar utama (mobile 390 + desktop 1440) sebagai baseline regresi.

### Fase 1 — Fondasi aset & token (1–2 hari)
- `npm i bootstrap-icons @fontsource/inter @fontsource/plus-jakarta-sans` (font self-host lebih cepat dari Google Fonts di jaringan lapangan).
- `resources/css/app.css` ditulis ulang: `@import bootstrap` → `@import design/tokens.css` (salin ke `resources/css/tokens.css`) → `resources/css/sigap/*.css` (komponen tambahan). Hapus redefinisi variabel lama & direktif Tailwind.
- Pindahkan Bootstrap JS, Chart.js, Alpine ke bundle Vite (`resources/js/app.js`); hapus CDN Bootstrap/Font Awesome/Poppins dari layout (Font Awesome tetap dimuat sementara sampai Fase 5 selesai, lalu dicabut).
- Sederhanakan blok fallback manifest Vite di `app.blade.php` menjadi `@vite([...])` biasa. Pastikan `npm run build` menghasilkan `public/build` untuk deploy Plesk (tanpa SSH, build dilakukan lokal lalu di-upload).
- `config/sigap-ui.php`: peta status laporan, status tiket, prioritas, level, SLA → label + kelas `.sg-*` (dari `tokens.json`). Ganti `Ticket::STATUSES['badge']` (masih `bg-primary` dsb.) agar membaca dari config ini.
- Helper format Indonesia (`app/Support/Format.php` atau macro Carbon): `17 Okt 2026, 14:30`, `1.248`, `91,4%`.
- Tema: `<html data-bs-theme>` + script kecil di `<head>` (anti-flash) membaca preferensi tersimpan / `prefers-color-scheme`. Kolom preferensi tema disimpan di `users.notification_preferences` atau kolom baru `ui_theme`.

**Selesai bila:** halaman lama masih tampil (boleh terlihat campuran), build sukses, tidak ada request CDN selain yang disengaja.

### Fase 2 — Library komponen Blade (2–3 hari)
Rujukan: `Main.dc.html` (halaman Design System). Buat di `resources/views/components/sg/`:

| Komponen | Isi |
|---|---|
| `badge-status`, `badge-progress`, `badge-priority`, `badge-level`, `badge-sla` | Dot + label; menerima model/nilai mentah |
| `card`, `list-card` | Kartu list mobile (badge + waktu, judul 2 baris, meta, chevron, `stretched-link`) |
| `kpi` | Angka `.sg-num` + label + delta |
| `table` | Wrapper tabel padat desktop + slot header/bulk bar |
| `sheet` | Bottom sheet di mobile / modal tengah di desktop (satu API, Alpine) |
| `sticky-actions` | Bar aksi bawah dengan safe-area |
| `chip-filter`, `search` | Chip wrap + tombol "Filter" → sheet |
| `tabs` | `nav-underline` sticky |
| `timeline` | Dipakai approval laporan, riwayat tiket, lacak tiket |
| `empty`, `skeleton`, `toast` (+ "Urungkan"), `confirm-danger` (ketik ulang) | State wajib |
| `uploader` | Kamera/galeri, maks 3, thumbnail, progress, retry, kompres klien |
| `avatar`, `page-header`, `brand-panel` | |

Buat halaman internal `/_ui` (hanya `local` + admin) yang merender semua komponen dalam light/dark untuk QA visual.

### Fase 3 — App shell & navigasi (2–3 hari)
Rujukan: `BottomNav.dc.html`, `Sidebar.dc.html`, rail di file tablet.
- `layouts/app.blade.php` baru (tipis): head, shell, `@stack`, toast container. Script inline lama dipindah ke modul JS (`resources/js/modules/notifications.js`, `sidebar.js`, dll.).
- Mobile: top app bar + bottom nav (Beranda, Laporan, FAB Buat, Tiket, Lainnya → sheet).
- Tablet: navigation rail 80px. Desktop: sidebar 248px collapsible (endpoint `dashboard/toggle-sidebar` dipakai ulang) + topbar (breadcrumb, pencarian, notifikasi dropdown, avatar + badge level).
- Visibilitas menu dari peran: "Persetujuan" hanya approver (`canApprove`), "Pengaturan Admin" hanya admin, "Buat Laporan"/FAB disembunyikan untuk Level 8.
- `layouts/guest` (login) dan `layouts/public` (tiket publik) diselaraskan.
- Banner offline & install PWA (`components/pwa-install`) dipindah ke shell.

**Selesai bila:** semua 42 halaman tampil di shell baru tanpa error, navigasi sesuai peran di 3 breakpoint.

### Fase 4 — Layar prioritas tinggi (alur inti mobile) (5–7 hari)
Urutan mengikuti alur prototipe (`Prototype.dc.html`): Login → Beranda → Buat → Approval → Detail.

| # | Layar | View | Mockup |
|---|---|---|---|
| 1 | Login | `auth/login` | LoginMobile, LoginDesktop |
| 2 | Beranda (3 varian peran) | `dashboard/*` (staff, leader, department-head, admin → dipetakan ke Karyawan / Atasan / Admin) | Dash*, DashApprover*, DashAdmin* |
| 3 | Buat Laporan | `daily-reports/create`, `edit` | CreateMobile, CreatePicSheet, CreateDesktop |
| 4 | Detail Laporan | `daily-reports/show` | DetailMobile, DetailReject, DetailViewer, DetailDesktop |
| 5 | Antrian Persetujuan | `daily-reports/pending` | QueueMobile, QueueMulti, QueueDesktop |
| 6 | Daftar Laporan | `daily-reports/index`, `user-jobs`, `assigned-jobs` | List*, ListTablet |
| 7 | Notifikasi | `notifications/all` + dropdown | NotifMobile, NotifDesktop |

Catatan teknis:
- Livewire `DailyReportForm`/`DailyReportList` (bila masih dipakai) dipertahankan; hanya markup yang diganti.
- PIC sheet: query atasan yang berhak sudah ada di backend; tambahkan endpoint JSON pencarian bila belum ada.
- Tolak wajib alasan: validasi server sudah ada (`rejection_reason`); tambah chip alasan cepat di UI.
- Grafik Beranda: Chart.js dengan warna token (bar "hari ini" kuning).

### Fase 5 — Layar sekunder (5–7 hari)
| Layar | View | Mockup |
|---|---|---|
| Rencana Kerja | `job-plans/*` | PlanMobile, PlanDesktop |
| Tiket publik (stepper 3 langkah, NIK) | `tickets/public-form`, `public-success`, `public-track`, `public-detail` | TicketFormStep1/Mobile/Desktop, TicketSuccess, TicketTrack |
| Tiket agent & Tiket Saya | `tickets/index`, `mine`, `create` | TicketListMobile/Desktop |
| Detail tiket | `tickets/show` + partials (sla-card, approval-card, timeline, comments) | TicketDetail*, TicketActionSheet |
| CSAT | `tickets/partials/requester-actions` | TicketCsatMobile |
| KPI tiket | `tickets/dashboard` | TicketKpi* |
| Struktur Organisasi | `organization/chart` | OrgMobile, OrgDesktop |
| Profil (+ toggle tema) | `profile/edit` | ProfileMobile, ProfileDesktop |
| Admin | `admin/users/*`, `departments/*`, `sections`, `job-sites`, `employees/*`, `ticket-categories`, `sla`, `reports/cleanup`, `settings` | AdminMobile, AdminUsers/Master/Sla/CleanupDesktop |
| Import Excel | `daily-reports/import`, `admin/users/import` | (pola AdminUsersDesktop) |
| Cetak persetujuan tiket | `tickets/approval-print` | Tetap gaya cetak; hanya logo & tipografi |

Akhir fase: cabut Font Awesome, `custom.css`, `dashboard.css`, Poppins, Tailwind; grep memastikan tidak ada `fa-`, `#0d6efd`, `#6610f2`, `linear-gradient` biru-ungu tersisa. Email notifikasi (`resources/views/emails` bila ada) disesuaikan logo + warna.

### Fase 6 — Peningkatan interaksi (opsional, 4–6 hari)
Dikerjakan setelah paritas visual; masing-masing bisa dirilis terpisah.
1. **Swipe approve/reject + toast "Urungkan"** di antrian mobile (Urungkan = tunda request 5 detik di klien, bukan rollback server).
2. **Mode pilih banyak** (tekan lama) memakai endpoint `batch-approve`/`batch-reject` yang ada.
3. **Shortcut desktop** J/K/X/A/R di antrian; **Ctrl K** pencarian global (endpoint pencarian baru: laporan, tiket, user).
4. **Draf offline Buat Laporan**: IndexedDB + antrian kirim di service worker (`public/sw.js`), banner "Kirim saat online". Perlu idempotency key di `store`/`store-multiple` agar tidak dobel.
5. Kompres foto di klien sebelum upload (sudah ada resize server via Intervention, ini menghemat kuota).
6. Pull-to-refresh, infinite scroll daftar laporan (pakai `simplePaginate` JSON).

### Fase 7 — QA & rilis (2–3 hari)
- Checklist per layar: 390 / 768 / 1440, light / dark, tanpa scroll horizontal di mobile, target sentuh ≥ 44px, input 16px, focus ring, `aria-label` tombol ikon, kontras AA (Lighthouse a11y ≥ 95), `prefers-reduced-motion`.
- Uji di perangkat nyata: Android kelas menengah (Chrome) + iPhone (Safari, PWA terpasang).
- Performa mobile: Lighthouse Performance ≥ 85 di Fast 3G; CSS+JS awal < 200 KB gzip.
- Semua test `php artisan test` hijau; tambah test Feature ringan untuk visibilitas menu per peran.
- Rilis bertahap ke produksi Plesk per fase (build lokal → upload `public/build`), dengan bump versi cache service worker agar aset lama tidak tertahan.
- Update `docs/user_guide.md` (screenshot baru) dan CLAUDE.md (versi 2.0.0).

---

## 4. Estimasi total

| Fase | Durasi |
|---|---|
| 0–3 Fondasi + shell | 6–9 hari |
| 4 Layar inti | 5–7 hari |
| 5 Layar sekunder | 5–7 hari |
| 6 Interaksi lanjutan (opsional) | 4–6 hari |
| 7 QA & rilis | 2–3 hari |
| **Total** | **±18–26 hari kerja** (tanpa Fase 6: ±14–20) |

## 5. Risiko & mitigasi

| Risiko | Mitigasi |
|---|---|
| Halaman besar (index 1.337 baris) berisi JS inline yang saling bergantung | Pecah ke partial + modul JS dulu tanpa ubah tampilan, baru restyle |
| Konflik CSS lama vs token baru selama transisi | Token di-scope lewat kelas `.sg-*`; CSS lama dicabut di akhir Fase 5 |
| Produksi Plesk tanpa SSH/Node | Build di lokal, commit/upload `public/build`; checklist deploy diperbarui |
| Cache service worker menyajikan aset lama | Versi cache di `sw.js` dinaikkan tiap rilis |
| Peta dashboard lama (staff/leader/department-head) ≠ 3 varian desain | Petakan berdasarkan `getRoleLevel()` + `canApprove()`, bukan nama role legacy |
| Mockup `.dc.html` tidak bisa dibuka langsung | Gunakan sebagai spesifikasi; canvas Design "SiGAP — Mockup UI" untuk versi klik |

## 6. Keputusan (dikonfirmasi 17 Okt 2026)
1. **Font self-host** via `@fontsource` (subset latin) — tidak ada request ke Google Fonts.
2. **Tailwind dihapus total** (paket di-uninstall; desain sepenuhnya Bootstrap + `.sg-*`).
3. **Fase 6 masuk rilis pertama** — tidak lagi opsional; estimasi total ±18–26 hari kerja.
4. **Tema disimpan di `localStorage`** (`sg-theme`: `light` | `dark` | `system`), diterapkan oleh `partials/theme-init` sebelum render. Tidak ada kolom database baru.

## 7. Progres
- [x] Fase 0 — branch `feature/ui-redesign`, aset → `public/images/brand/` (foto login WebP 52 KB), aturan desain digabung ke CLAUDE.md.
- [x] Fase 1 — bundle Vite (Bootstrap 5.3 + Bootstrap Icons + font self-host + `tokens.css`), Tailwind & CDN Bootstrap CSS/Poppins dicabut, `config/sigap-ui.php`, `App\Support\Format`, theme init, `public/build` di-commit untuk Plesk.
  - Bootstrap JS & Chart.js masih dari CDN sampai Fase 3 (script inline layout lama memanggil `bootstrap.*` saat parse; dipindah ke modul di Fase 3).
  - Font Awesome CDN tetap sampai akhir Fase 5.
  - Perbaikan: `build.manifest: true` di `vite.config.js` membuat `@vite` gagal (manifest di `.vite/`); dihapus.
- [x] Fase 2 — 21 komponen `resources/views/components/sg/*` + `resources/css/sigap/components.css` + modul JS `resources/js/sigap/` (uploader, toast), katalog `/_ui` (admin/local) + test. Dicek visual di 390 & 1440 px, light & dark.
  - Sheet = satu markup `.modal.sg-sheet-modal` (bottom sheet <992px, modal tengah ≥992px), bukan offcanvas terpisah.
  - Script layout lama yang memaksa tema light dihapus.
  - Diketahui (diselesaikan Fase 3): latar & judul halaman di shell lama belum ikut dark mode; tombol menu bulat lama menutupi sticky bar.
- [x] Fase 3 — shell baru di `layouts/app.blade.php`: sidebar 248px (bisa diciutkan, `localStorage sg-sidebar`), rail 80px tablet, app bar + bottom nav + FAB + sheet "Lainnya" (mobile), topbar desktop (breadcrumb, dropdown notifikasi, menu akun + tema). Menu dari `App\Support\Navigation` (aturan peran sama dengan navigasi lama) + `NavigationTest`.
  - Notifikasi dipindah ke Alpine store (`resources/js/sigap/notifications.js`), polling 60 dtk hanya saat tab terlihat.
  - Bootstrap JS & Chart.js self-host di `public/vendor` (disalin `scripts/copy-vendor.mjs` saat `npm run build`), dimuat sebagai script klasik. Bundle JS 380 KB → 95 KB.
  - Bug lama diperbaiki: Bootstrap termuat dua kali (`resources/js/bootstrap.js` + CDN) sehingga dropdown bisa terbuka-tertutup sendiri; Livewire (tanpa komponen) memuat Alpine kedua — `@livewireScripts` dihapus dari layout.
  - Layar tugas (punya `x-sg.sticky-actions`) otomatis menyembunyikan bottom nav di mobile; prop `back` di `<x-app-layout>` menampilkan tombol kembali.
  - `layouts/navigation.blade.php` dihapus. View lama masih memakai slot `header` (ditampilkan di `.sg-legacy-header`; judulnya dipakai app bar & breadcrumb).
- [x] Fase 5 — layar sekunder
  - [x] Lapis 1 (semua layar): Font Awesome → Bootstrap Icons (43 view) dan CDN dicabut; `bridge.css` menyelaraskan view lama; layout publik baru.
  - [x] Tiket: daftar (`x-ticket.list`), detail (aksi tim IT di sheet mobile), komentar internal, riwayat, badge SLA; form publik stepper 3 langkah; halaman sukses; dashboard KPI.
  - [x] Rencana Kerja: daftar, Rencana untuk Saya, detail; komponen `x-plan.list`, `x-sg.wa-share`.
  - [x] Profil (tema, notifikasi auto-save; perbaikan error bag `updatePassword`/`userDeletion`) & Struktur Organisasi.
  - [x] Admin, edit/impor laporan, buat tiket internal, instal PWA: judul & tombol kembali di app bar (prop `title`/`back`, slot `header` lama sudah tidak dipakai di mana pun), teks UI diterjemahkan ke Bahasa Indonesia, tabel lama otomatis jadi kartu di <576px (`stackTables()` + `.sg-stack`), gaya lewat `bridge.css`.
    - Tata letak halaman admin tidak ditulis ulang satu per satu (dipakai terutama di desktop); bisa dipoles bertahap bila perlu.
- [x] Fase 4 — layar inti (101 test lolos; QA visual 390/1440 tiap layar)
  - [x] Login (`auth/login`, `layouts/guest` ditulis ulang; field tetap `user_id`). "Lupa kata sandi" → teks "Hubungi admin" (belum ada route reset). "Ingat saya" tetap default mati.
  - [x] Beranda (`dashboard.blade.php` satu view untuk semua peran; partial `dashboard/*` lama dihapus). Data tambahan lewat `App\Support\DashboardSummary` (status laporan hari ini, tugas dari atasan, tiket aktif). Controller tidak diubah.
    - Belum ada datanya (tidak ditampilkan): kepatuhan lapor harian & performa tim per anggota di mockup atasan; "menunggu persetujuan" dibatasi 5 (controller `limit(5)`) → ditampilkan "5+".
  - [x] Buat Laporan (`daily-reports/create`): Alpine `sgReportForm`, entri berkunci tetap (`reports[key]`), sheet PIC dengan pencarian, uploader mode per-field (`attachment`/`_2`/`_3`), old input & error per entri dipulihkan. Test `CreateReportFormTest`.
    - Ditunda ke Fase 6: draf otomatis/offline. "Urungkan" hapus entri memulihkan teks, lampiran perlu dipilih ulang.
  - [x] Detail Laporan (`daily-reports/show`): 2 status beda bentuk, pembuat/approver, lampiran + penampil layar penuh, tab Deskripsi→Remarks→Komentar (Alpine `sgComments`, endpoint lama), timeline, sheet tolak dengan alasan cepat, sticky Tolak/Setujui, bagikan via `wa.me`. Test `ReportDetailTest`.
    - Keamanan: komentar lama disisipkan via `innerHTML` (XSS); kini `x-text`.
  - [x] Antrian Persetujuan (`daily-reports/pending`): kartu + aksi per kartu (mobile), mode Pilih → batch bar, split view + pintasan J/K/X/A/R (desktop), tab Monitoring (Admin & L4+ sesuai controller), hapus massal ketik "HAPUS". Test `ApprovalQueueTest`.
    - Bug lama diperbaiki: modal approval per item mengirim `action` padahal controller mewajibkan `status`.
    - Catatan: `batchReject` menelan error validasi jadi flash `error` (perilaku controller, tidak diubah).
    - Ditunda ke Fase 6: swipe kartu & tekan-lama.
  - [x] Daftar Laporan (`index`, `user-jobs`, `assigned-jobs`): komponen bersama `x-report.filters` (cari + sheet filter / toolbar desktop) dan `x-report.list` (kartu / tabel, `selectable` untuk hapus massal), tab Disetujui/Ditolak, ringkasan WhatsApp (L3+/admin, endpoint lama). Test `ReportListTest`.
    - Aksi "Update Status" di daftar lama dihapus: route `POST /daily-reports/{id}/status` tidak pernah ada (fitur sudah rusak).
    - Infinite scroll menyusul di Fase 6; sementara pagination Bootstrap.
  - [x] Notifikasi (`notifications/all`): grup Hari ini/Kemarin/Lebih lama, ikon per tipe, ketuk = tandai dibaca (`keepalive`) + buka tujuan, tandai semua / hapus semua. Test `NotificationPageTest`.
  - Belum disentuh (masuk Fase 5): `daily-reports/edit`, `daily-reports/import`.
- [x] Fase 6 — interaksi lanjutan (107 test lolos; diuji di browser 390 px dengan DB SQLite sementara)
  - Antrian Persetujuan: geser kanan = setujui (request ditunda 5 dtk + toast "Urungkan", dikirim segera saat halaman ditinggal), geser kiri = sheet tolak, tekan lama = mode pilih banyak (batch bar memakai `batch-approve`/`batch-reject` lama). Route approval kini membalas JSON bila diminta. Pintasan J/K/X/A/R sudah ada sejak Fase 4.
  - Pencarian global: `GET /search` (`SearchController`, throttle 60/menit) — laporan & tiket disaring lewat policy `view`, pengguna hanya untuk admin. Palet `x-sg.search-palette`: Ctrl K / tombol cari di topbar, ikon cari di app bar mobile. Test `GlobalSearchTest`.
  - Buat Laporan: draf teks otomatis (`localStorage sg-report-draft:{userId}`, banner Pulihkan/Buang); kirim lewat fetch, bila jaringan gagal (offline atau sinyal putus) laporan masuk antrian IndexedDB `sigap-outbox` (termasuk lampiran) dan dikirim otomatis saat online/halaman dibuka. `storeMultiple` menerima `idempotency_key` (kunci `Cache::add` 7 hari, dilepas bila gagal simpan) + balasan JSON. Test di `CreateReportFormTest`.
    - Menyimpang dari rencana: antrian dikirim dari halaman, bukan Background Sync service worker (token CSRF diambil dari halaman aktif; lebih sederhana dan jalan di iOS).
    - Lampiran tidak ikut draf otomatis (hanya teks); lampiran ikut bila laporan masuk antrian offline.
  - Foto JPEG/WebP ≥300 KB dikompres di klien (sisi terpanjang 1920 px, kualitas 0,8) sebelum cek ukuran; HEIC/PNG dikirim apa adanya.
  - Daftar laporan (`index`, `user-jobs`, `assigned-jobs`): infinite scroll di mobile memakai link `rel=next` pagination yang ada (tanpa endpoint JSON baru); desktop tetap pagination. Tarik untuk muat ulang hanya di PWA terpasang.
  - `sw.js` v4.0.0: halaman HTML tidak lagi masuk `api-cache` 5 menit (form Buat Laporan bisa dibuka offline dari cache `pages`).
  - Ditemukan, belum diperbaiki (di luar lingkup): `storeMultiple` tidak memeriksa PIC yang berhak (beda dengan `store`), cukup `exists:users,id`.
