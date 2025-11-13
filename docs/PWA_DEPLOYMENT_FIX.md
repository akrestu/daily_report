# 🎯 PWA MANIFEST DEPLOYMENT FIX

## 🔴 MASALAH YANG DITEMUKAN

Ketika deploy ke Plesk, manifest tidak valid dan icon tidak muncul karena:

1. **Static manifest file** (`public/web/site.webmanifest`) menggunakan hardcoded paths dengan `/`
2. Di Plesk, aplikasi mungkin di-deploy di **subfolder** bukan root
3. Icon URLs dan `start_url` menjadi invalid karena path tidak sesuai

**Contoh:**
```
Local:  http://localhost/icons/icon-192x192.png ✅
Plesk:  https://domain.com/subfolder/icons/icon-192x192.png ❌
        (manifest reference: /icons/... - WRONG!)
```

---

## ✅ SOLUSI: DYNAMIC MANIFEST ROUTE

### **Perubahan yang Diterapkan:**

#### **1. Route Dinamis untuk Manifest** (NEW)
**File:** `routes/web.php`

```php
Route::get('/web/site.webmanifest', function () {
    $appUrl = rtrim(config('app.url'), '/');
    
    // Generate manifest dengan APP_URL yang benar
    $manifest = [
        "name" => "SiGAP - Sistem Informasi Giat Aktivitas Pekerjaan",
        "short_name" => "SiGAP",
        // ... icons dengan $appUrl . "/icons/..." 
        "start_url" => $appUrl . "/",
        "scope" => $appUrl . "/",
        // ...
    ];
    
    return response()->json($manifest)
        ->header('Content-Type', 'application/manifest+json')
        ->header('Cache-Control', 'public, max-age=3600');
})->name('pwa.manifest');
```

**Keuntungan:**
- ✅ Menggunakan `config('app.url')` yang benar untuk environment
- ✅ Berfungsi di localhost, Plesk, VPS, atau domain apapun
- ✅ Icon URLs dan start_url selalu valid
- ✅ Cache-Control header untuk optimasi

#### **2. Update HTML Layouts**
**Files:**
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`

```blade
SEBELUM:
<link rel="manifest" href="{{ asset('web/site.webmanifest?v=2.0') }}">

SESUDAH:
<link rel="manifest" href="{{ route('pwa.manifest') }}">
```

**Alasan:** Menggunakan route yang generate manifest dinamis dengan APP_URL yang benar.

---

## 🚀 DEPLOYMENT CONFIGURATION

### **UNTUK PLESK DEPLOYMENT:**

Pastikan `.env` atau environment di Plesk sudah di-set dengan benar:

```env
# .env (di server Plesk)
APP_URL=https://yourdomain.com

# ATAU jika di subfolder:
APP_URL=https://yourdomain.com/subfolder
```

**Contoh Plesk configurations:**

#### **Jika di root domain:**
```env
APP_URL=https://yourdomain.com
```
→ Icon akan di-reference: `https://yourdomain.com/icons/icon-192x192.png` ✅

#### **Jika di subfolder (misal /sigap):**
```env
APP_URL=https://yourdomain.com/sigap
```
→ Icon akan di-reference: `https://yourdomain.com/sigap/icons/icon-192x192.png` ✅

---

## 🧪 TESTING MANIFEST

### **Di Browser Console:**
```javascript
fetch('/web/site.webmanifest')
    .then(r => r.json())
    .then(m => console.log(m));
```

**Expected Output:**
```json
{
    "name": "SiGAP - ...",
    "icons": [
        {
            "src": "https://domain.com/icons/icon-192x192.png?v=2.0"  // ✅ VALID URL
        }
    ],
    "start_url": "https://domain.com/",  // ✅ VALID URL
    "scope": "https://domain.com/"      // ✅ VALID URL
}
```

### **Via DevTools - Application Tab:**
1. Buka DevTools (`F12`)
2. Tab: `Application`
3. Expand: `Manifest` (di sebelah kiri)
4. Lihat:
   - ✅ Icons URLs harus lengkap (termasuk domain)
   - ✅ start_url harus valid
   - ✅ scope harus valid
   - ✅ No errors atau warnings

---

## 📋 DEPLOYMENT CHECKLIST

- [ ] Upload code ke Plesk (termasuk routes/web.php dengan manifest route)
- [ ] Di Plesk, set `.env` dengan `APP_URL=https://yourdomain.com` (CORRECT!)
- [ ] Run `php artisan config:cache` (untuk cache APP_URL)
- [ ] Restart atau clear cache di Plesk
- [ ] Hard refresh browser: `Ctrl+Shift+R` (Windows) atau `Cmd+Shift+R` (Mac)
- [ ] Check DevTools → Application → Manifest (harus valid)
- [ ] Check DevTools → Network → site.webmanifest (harus show full URLs)

---

## 🔍 DEBUGGING

### **Jika Manifest Masih Tidak Valid:**

1. **Check APP_URL:**
   ```bash
   php artisan tinker
   > config('app.url')
   # Should return: https://yourdomain.com
   ```

2. **Check Manifest Route:**
   ```bash
   # Via browser atau curl
   curl https://yourdomain.com/web/site.webmanifest
   
   # Harus return JSON dengan full URLs
   ```

3. **Check Icons Accessible:**
   ```bash
   curl https://yourdomain.com/icons/icon-192x192.png
   # Harus return image file (200 OK)
   ```

4. **Check .env loaded:**
   ```bash
   php artisan config:cache
   php artisan config:clear
   ```

---

## 📊 BEFORE vs AFTER

### **SEBELUM (Static File):**
```json
{
    "start_url": "/",
    "icons": [{ "src": "/icons/icon-192x192.png" }]
}
```
❌ Tidak bekerja jika domain/path berbeda

### **SESUDAH (Dynamic Route):**
```json
{
    "start_url": "https://domain.com/",
    "icons": [{ "src": "https://domain.com/icons/icon-192x192.png" }]
}
```
✅ Bekerja di mana saja!

---

## 🎯 SUMMARY

| Issue | Solution | Result |
|-------|----------|--------|
| Hardcoded `/` paths | Dynamic route dengan APP_URL | ✅ Works everywhere |
| Invalid manifest | Generated from config | ✅ Always valid |
| Icon not found | Full URLs with domain | ✅ Icons appear |
| Scope invalid | Dynamic scope generation | ✅ Correct scope |

---

**Status:** ✅ FIXED  
**Date:** November 12, 2025  
**Root Cause:** APP_URL tidak sesuai dengan deployment path  
**Solution:** Dynamic manifest route menggunakan config('app.url')
