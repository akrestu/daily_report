# Panduan Deploy ke Plesk - SiGAP v1.1

## ⚠️ PENTING: Masalah yang Diperbaiki

Error migrasi terjadi karena:
1. ❌ Tabel `notifications` dibuat sebelum tabel `job_comments`
2. ❌ Foreign key constraint gagal karena tabel referensi belum ada

**Solusi**: File migrasi sudah direorganisasi dengan urutan yang benar.

---

## 📦 File yang Perlu Di-Upload ke Plesk

Upload file migrasi berikut ke folder `database/migrations/` di server:

### ✅ File Baru (Upload ini):
```
2025_01_20_000001_add_profile_picture_to_users_table.php
2025_01_20_000002_add_notification_preferences_to_users_table.php
2025_01_20_000003_create_job_comments_table.php         ← RENAMED (dulu: 2025_04_24_004702)
2025_01_20_000004_update_job_comments_visibility_to_public.php  ← RENAMED (dulu: 2025_04_24_042409)
2025_01_20_000005_create_notifications_table.php        ← RENAMED (dulu: 2025_01_20_000003)
2025_01_20_000006_add_indexes_to_notifications_table.php ← RENAMED (dulu: 2025_01_20_000004)
```

### ❌ File Lama (Hapus dari server jika ada):
```
2025_04_24_004702_create_job_comments_table.php
2025_04_24_042409_update_job_comments_visibility_to_public.php
2025_04_24_044917_create_notifications_table.php
2025_04_24_044918_add_indexes_to_notifications_table.php
```

---

## 🔧 Langkah-Langkah Deploy

### 1️⃣ Backup Database (WAJIB!)

Via phpMyAdmin di Plesk:
- Klik **Export**
- Pilih **Quick** atau **Custom**
- Klik **Go** untuk download backup

Atau via SSH:
```bash
mysqldump -u wahanaba_sigap -p wahanaba_sigap > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2️⃣ Drop Database Lama (Karena Database Baru)

Via phpMyAdmin:
```sql
DROP DATABASE IF EXISTS wahanaba_sigap;
CREATE DATABASE wahanaba_sigap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atau via Plesk:
- **Databases** → Pilih database → **Remove** → Buat database baru dengan nama sama

### 3️⃣ Upload File Laravel

Via FTP/File Manager Plesk:
```
1. Upload folder database/migrations/ (dengan file yang sudah direorganisasi)
2. Upload file .env (pastikan DB credentials benar)
3. Upload seluruh file Laravel jika ada perubahan
```

### 4️⃣ Set Permission (Via SSH)

```bash
cd /var/www/vhosts/domain.com/httpdocs/

# Set ownership
chown -R username:psaserv .

# Set permission
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Storage & bootstrap/cache harus writable
chmod -R 775 storage bootstrap/cache
chown -R username:psacln storage bootstrap/cache
```

### 5️⃣ Konfigurasi Environment

Edit `.env` di Plesk File Manager atau via SSH:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=wahanaba_sigap
DB_USERNAME=wahanaba_sigap
DB_PASSWORD=your_password

# Queue untuk notifikasi
QUEUE_CONNECTION=database

# Timezone
APP_TIMEZONE=Asia/Jakarta
```

### 6️⃣ Jalankan Perintah Artisan

Via SSH atau Plesk Scheduled Tasks:

```bash
# 1. Generate Application Key
/opt/plesk/php/8.3/bin/php artisan key:generate

# 2. Clear cache
/opt/plesk/php/8.3/bin/php artisan cache:clear
/opt/plesk/php/8.3/bin/php artisan config:clear
/opt/plesk/php/8.3/bin/php artisan route:clear
/opt/plesk/php/8.3/bin/php artisan view:clear

# 3. Jalankan migrasi
/opt/plesk/php/8.3/bin/php artisan migrate

# 4. Seed database (jika perlu data awal)
/opt/plesk/php/8.3/bin/php artisan db:seed

# 5. Create storage link
/opt/plesk/php/8.3/bin/php artisan storage:link

# 6. Optimize untuk production
/opt/plesk/php/8.3/bin/php artisan config:cache
/opt/plesk/php/8.3/bin/php artisan route:cache
/opt/plesk/php/8.3/bin/php artisan view:cache
```

### 7️⃣ Setup Queue Worker (Untuk Notifikasi)

Di Plesk → **Scheduled Tasks** → **Add Task**:

```bash
# Jalankan setiap 1 menit
/opt/plesk/php/8.3/bin/php /var/www/vhosts/domain.com/httpdocs/artisan queue:work --stop-when-empty
```

Atau gunakan Supervisor (recommended untuk production):

```bash
# Install supervisor
sudo yum install supervisor  # untuk CentOS/RHEL
# atau
sudo apt-get install supervisor  # untuk Ubuntu/Debian

# Create config file
sudo nano /etc/supervisor/conf.d/sigap-worker.conf
```

Isi config:
```ini
[program:sigap-worker]
process_name=%(program_name)s_%(process_num)02d
command=/opt/plesk/php/8.3/bin/php /var/www/vhosts/domain.com/httpdocs/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=username
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/vhosts/domain.com/httpdocs/storage/logs/worker.log
stopwaitsecs=3600
```

Jalankan:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sigap-worker:*
```

---

## ✅ Verifikasi Deployment

### 1. Cek Migrasi Berhasil
```bash
/opt/plesk/php/8.3/bin/php artisan migrate:status
```

Output yang benar:
```
Migration name .................................. Batch / Status
2025_01_20_000001_add_profile_picture_to_users_table ........... [1] Ran
2025_01_20_000002_add_notification_preferences_to_users_table .. [1] Ran
2025_01_20_000003_create_job_comments_table ................... [1] Ran
2025_01_20_000004_update_job_comments_visibility_to_public .... [1] Ran
2025_01_20_000005_create_notifications_table .................. [1] Ran
2025_01_20_000006_add_indexes_to_notifications_table .......... [1] Ran
```

### 2. Cek Tabel Database

Via phpMyAdmin, cek tabel berikut ada:
- ✅ `users`
- ✅ `roles`
- ✅ `departments`
- ✅ `daily_reports`
- ✅ `job_comments` ← Harus ada sebelum notifications
- ✅ `notifications` ← Referensi ke job_comments

### 3. Test Login

Akses: `https://domain-anda.com/login`

Default credentials (jika sudah seed):
- Email: `admin@example.com`
- Password: `password`

### 4. Cek Notifikasi

- Login sebagai admin
- Klik icon bell di navbar
- Pastikan tidak ada error

### 5. Cek File Upload

- Buat laporan baru dengan attachment
- Pastikan file tersimpan di `storage/app/public/attachments/`

---

## 🐛 Troubleshooting

### Error: "SQLSTATE[HY000]: General error: 1005"
**Penyebab**: Urutan migrasi salah atau tabel referensi belum ada

**Solusi**:
1. Drop database dan buat baru
2. Pastikan menggunakan file migrasi yang sudah direorganisasi
3. Jalankan `php artisan migrate` lagi

### Error: "Class 'Storage' not found"
**Solusi**:
```bash
php artisan storage:link
php artisan config:clear
```

### Error: "Permission denied" untuk storage
**Solusi**:
```bash
chmod -R 775 storage bootstrap/cache
chown -R username:psacln storage bootstrap/cache
```

### Warning: "Unable to load dynamic library 'memcached.so'"
**Solusi**: Ini hanya warning, tidak mempengaruhi aplikasi. Untuk menghilangkan:
```bash
# Edit php.ini
sudo nano /opt/plesk/php/8.3/etc/php.ini

# Comment out atau hapus baris:
; extension=memcached.so
```

### Notifikasi Tidak Muncul
**Solusi**:
1. Pastikan queue worker berjalan
2. Cek log: `tail -f storage/logs/laravel.log`
3. Jalankan manual: `php artisan queue:work`

---

## 📞 Support

Jika ada masalah saat deployment, cek:
1. **Logs**: `storage/logs/laravel.log`
2. **Server Error Log**: Di Plesk → Logs → Error Log
3. **Migration Status**: `php artisan migrate:status`

---

**Update Terakhir**: November 2025
**Versi**: 1.1.0
