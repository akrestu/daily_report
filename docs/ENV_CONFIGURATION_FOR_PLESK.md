# 📝 ENV CONFIGURATION UNTUK PLESK DEPLOYMENT

## 🎯 CRITICAL SETTING: APP_URL

Ketika di-deploy ke Plesk, **APP_URL harus sesuai dengan domain dan path yang benar**.

---

## ⚙️ KONFIGURASI UNTUK BERBAGAI SKENARIO

### **Skenario 1: Deployed di Root Domain**

```env
APP_NAME=SiGAP
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=base64:...

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sigap_prod
DB_USERNAME=sigap_user
DB_PASSWORD=your_password
```

**Testing URL:**
```
https://yourdomain.com/
https://yourdomain.com/icons/icon-192x192.png
https://yourdomain.com/web/site.webmanifest
```

---

### **Skenario 2: Deployed di Subfolder**

```env
APP_NAME=SiGAP
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com/sigap
APP_KEY=base64:...

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sigap_prod
DB_USERNAME=sigap_user
DB_PASSWORD=your_password
```

**Testing URL:**
```
https://yourdomain.com/sigap/
https://yourdomain.com/sigap/icons/icon-192x192.png
https://yourdomain.com/sigap/web/site.webmanifest
```

---

### **Skenario 3: Deployed dengan Subdomain**

```env
APP_NAME=SiGAP
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sigap.yourdomain.com
APP_KEY=base64:...

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sigap_prod
DB_USERNAME=sigap_user
DB_PASSWORD=your_password
```

**Testing URL:**
```
https://sigap.yourdomain.com/
https://sigap.yourdomain.com/icons/icon-192x192.png
https://sigap.yourdomain.com/web/site.webmanifest
```

---

## 🔐 SECURITY SETTINGS (Production)

```env
# Essential for production
APP_ENV=production
APP_DEBUG=false

# Session security
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIES=true  # Force HTTPS

# Cache
CACHE_STORE=redis  # atau database jika redis tidak available
CACHE_ENCRYPT=true

# Database
DB_CONNECTION=mysql
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt

# Mail (if using)
MAIL_MAILER=smtp
MAIL_HOST=your_mail_server
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password

# Log
LOG_CHANNEL=stack
LOG_LEVEL=warning  # Jangan debug di production
```

---

## 🚀 DEPLOYMENT STEPS DI PLESK

### **Step 1: Upload Code**
1. Buka Plesk Control Panel
2. File Manager → Navigate to public_html (atau subfolder jika diperlukan)
3. Upload seluruh code Laravel

### **Step 2: Create .env**
1. Copy `.env.example` menjadi `.env`
2. Set configuration sesuai skenario di atas
3. **CRITICAL:** Pastikan `APP_URL` benar!

```bash
cp .env.example .env
nano .env
# Edit APP_URL dengan benar
```

### **Step 3: Generate APP_KEY**
```bash
php artisan key:generate
```

### **Step 4: Install Dependencies**
```bash
composer install --no-dev
```

### **Step 5: Build Frontend**
```bash
npm install
npm run build
```

### **Step 6: Cache Configuration**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 7: Database Migrations**
```bash
php artisan migrate --force
php artisan db:seed --force  # If needed
```

### **Step 8: Storage Link**
```bash
php artisan storage:link
```

### **Step 9: Set Permissions**
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache  # If needed
```

### **Step 10: Restart Application**
- Di Plesk: Domains → Select Domain → PHP Settings → Restart PHP

---

## ✅ VERIFICATION CHECKLIST

### **After Deployment:**

- [ ] `APP_URL` di .env **benar** (sesuai domain/path)
- [ ] `php artisan tinker` → `config('app.url')` menunjukkan URL yang benar
- [ ] Visit `https://yourdomain.com/` → Halaman load
- [ ] Visit `https://yourdomain.com/web/site.webmanifest` → JSON manifest tampil
- [ ] Visit `https://yourdomain.com/icons/icon-192x192.png` → Image tampil
- [ ] DevTools → Application → Manifest → Manifest valid ✅
- [ ] DevTools → Network → site.webmanifest → Status 200 ✅
- [ ] PWA install button work ✅

### **Manifest Validation:**

Buka browser console dan jalankan:
```javascript
fetch('/web/site.webmanifest')
    .then(r => r.json())
    .then(m => {
        console.log('Manifest:', m);
        console.log('Start URL:', m.start_url);
        console.log('First Icon:', m.icons[0].src);
    });
```

**Expected Output:**
```
Manifest: { name: "SiGAP - ...", ... }
Start URL: https://domain.com/
First Icon: https://domain.com/icons/icon-192x192.png?v=2.0
```

---

## 🐛 TROUBLESHOOTING

### **Manifest tidak valid atau icons tidak muncul:**

1. **Check APP_URL:**
   ```bash
   php artisan tinker
   > config('app.url')
   # Harus menunjukkan: https://domain.com atau https://domain.com/subfolder
   ```

2. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

3. **Restart PHP** di Plesk

4. **Hard refresh browser:**
   ```
   Ctrl + Shift + R (Windows)
   Cmd + Shift + R (Mac)
   ```

5. **Check manifest directly:**
   ```bash
   curl https://yourdomain.com/web/site.webmanifest | jq .
   ```

### **Icons showing old version:**

1. Clear service worker:
   ```javascript
   (async () => {
       (await caches.keys()).forEach(c => caches.delete(c));
       (await navigator.serviceWorker.getRegistrations()).forEach(r => r.unregister());
       location.reload(true);
   })();
   ```

2. Uninstall and reinstall PWA

---

## 📌 IMPORTANT NOTES

1. **APP_URL tanpa trailing slash:** 
   ```
   ✅ https://domain.com
   ❌ https://domain.com/
   ```

2. **APP_URL dengan subfolder:**
   ```
   ✅ https://domain.com/sigap
   ❌ https://domain.com/sigap/
   ```

3. **Always use HTTPS** di production untuk PWA

4. **config:cache** perlu di-run setiap kali update .env

5. **Restart PHP** setelah update .env

---

## 🔗 Related Files

- `routes/web.php` - PWA Manifest Route (dynamic)
- `resources/views/layouts/app.blade.php` - Uses route('pwa.manifest')
- `resources/views/layouts/guest.blade.php` - Uses route('pwa.manifest')
- `docs/PWA_DEPLOYMENT_FIX.md` - Full deployment documentation

---

**Status:** ✅ READY  
**Last Updated:** November 12, 2025
