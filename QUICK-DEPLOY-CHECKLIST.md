# ✅ Quick Deploy Checklist - Plesk

## Persiapan (Di Komputer Lokal)

- [ ] Backup database lokal (jika perlu)
- [ ] Test migrasi lokal: `php artisan migrate:fresh`
- [ ] Pastikan tidak ada error
- [ ] Zip folder project: `SiGAP.zip`

---

## Deploy ke Plesk (30 Menit)

### 📤 **STEP 1: Upload Files** (5 menit)

Via FTP atau File Manager Plesk:

- [ ] Upload folder `database/migrations/` (semua file)
- [ ] Upload file `.env` (cek DB credentials)
- [ ] Upload file `composer.json` dan `composer.lock`
- [ ] Upload folder `app/`, `routes/`, `resources/`, `public/`

**Files penting di migrations:**
```
✅ 2025_01_20_000003_create_job_comments_table.php
✅ 2025_01_20_000005_create_notifications_table.php
```

---

### 🗄️ **STEP 2: Database Setup** (5 menit)

Via phpMyAdmin atau Plesk:

- [ ] **Backup database lama** (jika ada data penting)
- [ ] **Drop database**: `DROP DATABASE wahanaba_sigap;`
- [ ] **Buat database baru**:
  ```sql
  CREATE DATABASE wahanaba_sigap
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
  ```

---

### ⚙️ **STEP 3: Environment Configuration** (3 menit)

Edit `.env` di server:

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_URL=https://yourdomain.com`
- [ ] Cek `DB_DATABASE=wahanaba_sigap`
- [ ] Cek `DB_USERNAME` dan `DB_PASSWORD`
- [ ] Set `QUEUE_CONNECTION=database`

---

### 🔐 **STEP 4: Permissions** (2 menit)

Via SSH atau File Manager:

- [ ] Set folder `storage/` permission: **775**
- [ ] Set folder `bootstrap/cache/` permission: **775**
- [ ] Set owner (jika SSH):
  ```bash
  chown -R username:psacln storage bootstrap/cache
  ```

---

### 🚀 **STEP 5: Artisan Commands** (10 menit)

Via SSH atau Plesk Terminal:

**5.1 Generate Key:**
```bash
/opt/plesk/php/8.3/bin/php artisan key:generate
```
- [ ] Key generated successfully

**5.2 Clear Caches:**
```bash
/opt/plesk/php/8.3/bin/php artisan cache:clear
/opt/plesk/php/8.3/bin/php artisan config:clear
```
- [ ] Caches cleared

**5.3 Run Migration:** ⭐ **PENTING**
```bash
/opt/plesk/php/8.3/bin/php artisan migrate
```
- [ ] All migrations ran successfully
- [ ] No errors about foreign key constraints

**5.4 Seed Database:**
```bash
/opt/plesk/php/8.3/bin/php artisan db:seed
```
- [ ] Seeding completed

**5.5 Storage Link:**
```bash
/opt/plesk/php/8.3/bin/php artisan storage:link
```
- [ ] Storage linked

**5.6 Optimize for Production:**
```bash
/opt/plesk/php/8.3/bin/php artisan config:cache
/opt/plesk/php/8.3/bin/php artisan route:cache
/opt/plesk/php/8.3/bin/php artisan view:cache
```
- [ ] All caches created

---

### ⏰ **STEP 6: Queue Worker** (5 menit)

**Option A: Cron Job (Simple)**

Di Plesk → Scheduled Tasks → Add Task:
- Run: **Every minute**
- Command:
  ```bash
  /opt/plesk/php/8.3/bin/php /var/www/vhosts/yourdomain.com/httpdocs/artisan queue:work --stop-when-empty
  ```
- [ ] Cron job created

**Option B: Supervisor (Recommended)**
- [ ] Install supervisor
- [ ] Create config file
- [ ] Start worker
- [ ] Check status: `supervisorctl status`

---

## ✅ Testing & Verification (5 menit)

### 🧪 **Test 1: Database Check**

Via phpMyAdmin, cek tabel ada:
- [ ] `users`
- [ ] `departments`
- [ ] `roles`
- [ ] `daily_reports`
- [ ] `job_comments` ← **Penting!**
- [ ] `notifications` ← **Penting!**

### 🧪 **Test 2: Migration Status**

```bash
/opt/plesk/php/8.3/bin/php artisan migrate:status
```

Pastikan semua migrasi status: **Ran** (tidak ada Pending)
- [ ] All migrations show "Ran"

### 🧪 **Test 3: Login Test**

1. Buka: `https://yourdomain.com/login`
2. Login dengan:
   - Email: `admin@example.com`
   - Password: `password`
3. [ ] Login berhasil
4. [ ] Dashboard muncul tanpa error

### 🧪 **Test 4: Notification Test**

1. Login sebagai admin
2. Klik icon **bell** (🔔) di navbar
3. [ ] Dropdown notifikasi muncul
4. [ ] Tidak ada error

### 🧪 **Test 5: Create Report Test**

1. Menu **Laporan** → **Buat Laporan Baru**
2. Isi form dan upload attachment
3. Submit laporan
4. [ ] Laporan tersimpan
5. [ ] File attachment terupload
6. [ ] Notifikasi terkirim (cek sebagai approver)

### 🧪 **Test 6: File Upload Test**

1. Cek folder: `storage/app/public/attachments/`
2. [ ] File attachment ada di folder tersebut

---

## 🚨 Troubleshooting Quick Fix

### ❌ Error: "Foreign key constraint incorrectly formed"

**Penyebab**: Urutan migrasi salah

**Fix**:
1. Drop database: `DROP DATABASE wahanaba_sigap;`
2. Buat baru: `CREATE DATABASE wahanaba_sigap;`
3. Pastikan file migrations sudah benar (cek STEP 1)
4. Jalankan migrate lagi: `php artisan migrate`

### ❌ Error: "Table doesn't exist"

**Fix**:
```bash
php artisan migrate:status  # Cek status
php artisan migrate --force  # Force migrate jika pending
```

### ❌ Error: "Permission denied"

**Fix**:
```bash
chmod -R 775 storage bootstrap/cache
chown -R username:psacln storage bootstrap/cache
```

### ❌ Notifikasi Tidak Muncul

**Fix**:
1. Cek queue running: `ps aux | grep queue`
2. Manual run: `php artisan queue:work`
3. Cek log: `tail -f storage/logs/laravel.log`

### ⚠️ Warning: "Unable to load memcached.so"

**Fix**: Ignore (tidak mempengaruhi aplikasi) atau:
```bash
sudo nano /opt/plesk/php/8.3/etc/php.ini
# Comment out: ; extension=memcached.so
```

---

## 📋 Post-Deployment

- [ ] Test semua fitur utama
- [ ] Backup database production
- [ ] Setup automatic backup (daily/weekly)
- [ ] Monitor error logs: `storage/logs/laravel.log`
- [ ] Setup monitoring (optional): UptimeRobot, Pingdom

---

## 🎉 Deployment Complete!

**Selamat!** Aplikasi SiGAP v1.1 sudah live di production.

**Default Admin Login:**
- URL: https://yourdomain.com/login
- Email: admin@example.com
- Password: password

⚠️ **PENTING**: Segera ganti password default setelah login pertama!

---

**Last Updated**: November 2025
