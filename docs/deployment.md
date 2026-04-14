# SiGAP — Panduan Deployment Produksi

> Untuk setup development lokal, lihat [development_setup.md](development_setup.md)

---

## Daftar Isi

1. [Persyaratan Server](#1-persyaratan-server)
2. [Konfigurasi .env Produksi](#2-konfigurasi-env-produksi)
3. [Konfigurasi Timezone](#3-konfigurasi-timezone)
4. [Pre-Deployment Checklist](#4-pre-deployment-checklist)
5. [Langkah Deployment](#5-langkah-deployment)
6. [Konfigurasi Plesk](#6-konfigurasi-plesk)
7. [Konfigurasi Nginx](#7-konfigurasi-nginx)
8. [Queue Worker di Produksi](#8-queue-worker-di-produksi)
9. [Scheduled Tasks (Cron)](#9-scheduled-tasks-cron)
10. [PWA Deployment](#10-pwa-deployment)
11. [Post-Deployment Verifikasi](#11-post-deployment-verifikasi)
12. [Optimasi Performa](#12-optimasi-performa)

---

## 1. Persyaratan Server

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| PHP | 8.2 | 8.3 |
| MySQL | 8.0 | 8.0+ / MariaDB 10.6+ |
| Web Server | Apache 2.4 / Nginx 1.18 | Nginx |
| Memory (PHP) | 128MB | 256MB |
| Storage | 1GB | 5GB+ |

**PHP Extensions yang wajib aktif:**
- `pdo`, `pdo_mysql`
- `mbstring`
- `gd` (untuk kompresi gambar upload)
- `xml`, `zip`, `bcmath`, `fileinfo`
- `opcache` (produksi — sangat disarankan)

---

## 2. Konfigurasi .env Produksi

```env
# === APP ===
APP_NAME="SiGAP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Jakarta

# === DATABASE ===
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sigap_prod
DB_USERNAME=sigap_user
DB_PASSWORD=strong_password_here
DB_TIMEZONE=+07:00

# === QUEUE ===
QUEUE_CONNECTION=database

# === SESSION ===
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# === CACHE ===
CACHE_STORE=database

# === MAIL ===
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="SiGAP"

# === LOG ===
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

### Konfigurasi untuk Subfolder / Subdomain

| Skenario | APP_URL |
|----------|---------|
| Root domain | `https://yourdomain.com` |
| Subfolder | `https://yourdomain.com/sigap` |
| Subdomain | `https://sigap.yourdomain.com` |

---

## 3. Konfigurasi Timezone

SiGAP menggunakan konfigurasi timezone berlapis untuk Indonesia (WIB/UTC+7):

| File | Setting | Efek |
|------|---------|------|
| `bootstrap/app.php` | `date_default_timezone_set('Asia/Jakarta')` | Timezone default PHP runtime |
| `config/app.php` | `'timezone' => 'Asia/Jakarta'` | Timezone aplikasi Laravel |
| `config/database.php` | `'timezone' => '+07:00'` | Timezone sesi MySQL |
| `app/Providers/AppServiceProvider.php` | `Carbon::setLocale('id')` | Format tanggal Bahasa Indonesia |

> **Jangan ubah ketiga setting ini.** Semua timestamp disimpan UTC oleh Laravel, ditampilkan dalam WIB secara otomatis.

---

## 4. Pre-Deployment Checklist

Jalankan di lokal sebelum upload ke server:

```bash
# 1. Install dependencies tanpa dev packages
composer install --no-dev --optimize-autoloader

# 2. Build frontend assets untuk produksi
npm run build

# 3. Pastikan semua test lulus
composer run test

# 4. Clear semua cache lama
php artisan optimize:clear
```

---

## 5. Langkah Deployment

```bash
# === Di Server ===

# 1. Upload/pull kode ke server
git pull origin main

# 2. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 3. Set environment
cp .env.example .env  # Jika pertama kali
php artisan key:generate

# 4. Jalankan migrasi database
php artisan migrate --force

# 5. Buat symlink storage
php artisan storage:link

# 6. Set permission direktori
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # sesuaikan user web server

# 7. Cache semua untuk performa optimal
composer run optimize
# Atau satu per satu:
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache
# php artisan event:cache

# 8. Restart queue worker (penting setelah deploy)
php artisan queue:restart
```

---

## 6. Konfigurasi Plesk

### Document Root

Set **Document Root** Plesk ke direktori `public/` dari project:

```
/var/www/vhosts/yourdomain.com/sigap/public
```

### PHP Handler

Gunakan **PHP-FPM** untuk performa terbaik. Set PHP version ke 8.2+.

### File .htaccess

File `public/.htaccess` sudah tersedia dan menangani:
- URL rewriting ke `index.php`
- HTTPS redirect
- Kompresi gzip
- Browser caching headers

### Cron Job di Plesk

Tambahkan scheduled task di Plesk → **Scheduled Tasks**:

```
* * * * *  /usr/bin/php /var/www/vhosts/yourdomain.com/sigap/artisan schedule:run >> /dev/null 2>&1
```

### Queue Worker via Plesk

Tambahkan **Long-running Task** atau gunakan supervisor (lihat [Queue Worker](#8-queue-worker-di-produksi)).

---

## 7. Konfigurasi Nginx

Tambahkan server block berikut:

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    root /var/www/sigap/public;
    index index.php;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # Gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known) {
        deny all;
    }
}

# HTTP redirect ke HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$host$request_uri;
}
```

---

## 8. Queue Worker di Produksi

Queue worker harus selalu berjalan di background. Gunakan **Supervisor** (direkomendasikan):

### Instalasi Supervisor (Linux)

```bash
sudo apt-get install supervisor
```

### Konfigurasi `/etc/supervisor/conf.d/sigap-worker.conf`

```ini
[program:sigap-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sigap/artisan queue:work database --tries=1 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/sigap/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Aktifkan
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sigap-worker:*
```

### Restart Worker Setelah Deploy

```bash
php artisan queue:restart
# atau
sudo supervisorctl restart sigap-worker:*
```

---

## 9. Scheduled Tasks (Cron)

Laravel Scheduler berjalan via cron setiap menit. Task yang terdaftar di `routes/console.php`:

| Task | Jadwal | Perintah |
|------|--------|---------|
| Cleanup laporan lama | Monthly | `reports:cleanup --days=730` |
| Cleanup notifikasi | Daily | `notifications:cleanup --days=30` |

Pastikan cron berikut aktif di server:

```cron
* * * * * cd /var/www/sigap && php artisan schedule:run >> /dev/null 2>&1
```

---

## 10. PWA Deployment

### File PWA yang Ada

| File | Lokasi | Fungsi |
|------|--------|--------|
| `site.webmanifest` | `public/` | Manifest PWA (static) |
| `sw.js` | `public/` | Service worker (Workbox 7) |
| `offline.html` | `public/` | Halaman fallback offline |
| Icons | `public/icons/` | PNG: 48, 72, 96, 144, 180, 192, 512px |

### Cache Version

Cache version saat ini: **`v3.0.0`** di `public/sw.js`.

Saat merilis update besar, increment versi:
```javascript
// public/sw.js — baris pertama
const CACHE_VERSION = 'v3.1.0'; // ubah dari v3.0.0
```

Pengguna yang menggunakan PWA akan mendapatkan update saat mereka membuka aplikasi dan service worker mendeteksi versi baru.

### Verifikasi PWA

1. Buka aplikasi di Chrome
2. DevTools → Application → Service Workers: pastikan `sw.js` terdaftar
3. DevTools → Application → Manifest: verifikasi icon dan `start_url`
4. DevTools → Application → Storage → Cache: lihat cache `sigap-v3.0.0`

### Fallback Routes untuk Icon

Jika `.htaccess` tidak bekerja, Laravel menyediakan routes fallback:
- `GET /icons/{filename}` — serve PWA icons (1 year cache)
- `GET /screenshots/{filename}` — serve screenshots

---

## 11. Post-Deployment Verifikasi

```bash
# 1. Health check endpoint
curl https://yourdomain.com/up
# Expected response: 200 OK

# 2. Cek symlink storage
ls -la public/storage
# Harus menunjuk ke ../storage/app/public

# 3. Cek queue worker berjalan
php artisan queue:monitor

# 4. Cek log error
php artisan pail --level=error
# atau
tail -f storage/logs/laravel.log

# 5. Verifikasi konfigurasi
php artisan config:show app
```

**Checklist Manual:**
- [ ] Login dengan `admin@example.com` berhasil
- [ ] Buat laporan baru berhasil
- [ ] Upload file attachment berjalan
- [ ] Approval laporan berjalan dan notifikasi diterima
- [ ] Dashboard menampilkan grafik Chart.js dengan benar
- [ ] Org Chart dapat diakses
- [ ] PWA dapat diinstall (ada prompt di browser mobile)

---

## 12. Optimasi Performa

### Caching (wajib di produksi)

```bash
# Cache semua sekaligus
composer run optimize

# Atau manual
php artisan config:cache    # Config dicompile jadi satu file
php artisan route:cache     # Routes dicompile (tidak bisa ada closure di routes)
php artisan view:cache      # Blade template di-precompile
php artisan event:cache     # Event listener di-cache
```

### OPcache (PHP)

Aktifkan OPcache di `php.ini` untuk performa PHP terbaik:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=10000
opcache.revalidate_freq=0  ; 0 = tidak revalidate di produksi
```

### Database Indexes

Indeks kinerja sudah ditambahkan via migration `2025_11_13_145552_add_performance_indexes_to_tables.php`:

- `daily_reports`: composite index `(user_id, status)`, `(department_id, approval_status)`, `(report_date, status)`
- `notifications`: composite index `(user_id, is_read, created_at)`
- `job_comments`: index `(daily_report_id)`

### Clear Cache Sebelum Deploy Baru

```bash
composer run optimize:clear
# Membersihkan: config, route, view, event cache
```
