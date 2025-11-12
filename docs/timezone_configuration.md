# Timezone Configuration Guide

## Overview
This document outlines the complete timezone configuration for SiGAP (Sistem Informasi Giat Aktivitas Pekerjaan) to ensure all dates and times are displayed in Indonesian timezone (Asia/Jakarta / UTC+7).

## ⏰ Current Configuration

### Application Level
- **Laravel App Timezone**: `Asia/Jakarta` (config/app.php)
- **PHP Default Timezone**: `Asia/Jakarta` (bootstrap/app.php)
- **Carbon Locale**: `id` (Indonesian)
- **Database Timezone**: `+07:00` (UTC+7)

### Configuration Files

**1. `config/app.php`**
```php
'timezone' => 'Asia/Jakarta',
```

**2. `bootstrap/app.php`**
```php
// Set default timezone for the entire application
date_default_timezone_set('Asia/Jakarta');
```

**3. `app/Providers/AppServiceProvider.php`**
```php
// Set Carbon locale to Indonesian and timezone to Asia/Jakarta
Carbon::setLocale('id');

// Ensure all Carbon instances use Asia/Jakarta timezone
if (config('app.timezone')) {
    date_default_timezone_set(config('app.timezone'));
}
```

**4. `config/database.php` (MySQL connection)**
```php
'options' => extension_loaded('pdo_mysql') ? array_filter([
    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'",
]) : [],
```

## 🕐 Time Display Examples

### Current Time Formats
- **WIB Format**: `2025-06-17 08:16:37 WIB`
- **Indonesian Date**: `Selasa, 17 Juni 2025`
- **Database Format**: `2025-06-17 08:16:37`
- **ISO Format**: `2025-06-17T08:16:37+07:00`

### Usage in Code

**Controllers:**
```php
use Carbon\Carbon;

$now = Carbon::now();                    // 2025-06-17 08:16:37 WIB
$today = Carbon::today();               // 2025-06-17 00:00:00 WIB
$formatted = $now->translatedFormat('l, d F Y'); // Selasa, 17 Juni 2025
$diffForHumans = $now->diffForHumans(); // beberapa detik yang lalu
```

**Blade Templates:**
```php
{{ $report->created_at->format('d M Y H:i') }}           // 17 Jun 2025 08:16
{{ $report->created_at->diffForHumans() }}              // 2 jam yang lalu  
{{ $report->created_at->translatedFormat('d F Y') }}    // 17 Juni 2025
{{ Carbon\Carbon::now()->format('Y-m-d H:i:s T') }}     // 2025-06-17 08:16:37 WIB
```

**Database Queries:**
```php
// All these will use Asia/Jakarta timezone automatically
$reportsToday = DailyReport::whereDate('created_at', Carbon::today())->get();
$reportsThisWeek = DailyReport::where('created_at', '>=', Carbon::now()->startOfWeek())->get();
$reportsThisMonth = DailyReport::where('created_at', '>=', Carbon::now()->startOfMonth())->get();
```

## ✅ Verification Commands

### Check Configuration
```bash
# Laravel app timezone
php artisan tinker --execute="echo config('app.timezone');"

# PHP default timezone  
php artisan tinker --execute="echo date_default_timezone_get();"

# Carbon locale
php artisan tinker --execute="echo \Carbon\Carbon::getLocale();"

# Current time in WIB
php artisan tinker --execute="echo \Carbon\Carbon::now()->format('Y-m-d H:i:s T');"

# Indonesian formatted date
php artisan tinker --execute="echo \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i:s T');"
```

### Expected Outputs
```
Asia/Jakarta
Asia/Jakarta  
id
2025-06-17 08:16:37 WIB
Selasa, 17 Juni 2025 08:16:37 WIB
```

## 🔧 Troubleshooting

### Common Issues

**1. Time showing in UTC instead of WIB**
- Clear config cache: `php artisan config:clear`
- Check if `date_default_timezone_set('Asia/Jakarta')` is called
- Verify `config('app.timezone')` returns `Asia/Jakarta`

**2. Dates showing in English instead of Indonesian**
- Check if `Carbon::setLocale('id')` is called in AppServiceProvider
- Verify Carbon locale: `Carbon::getLocale()` should return `id`

**3. Database times not matching application times**
- Check MySQL timezone: `SELECT @@session.time_zone;`
- Verify database connection options include `SET time_zone = '+07:00'`

### Debugging Commands
```bash
# Check all timezone settings at once
php artisan tinker --execute="
echo 'Laravel App: ' . config('app.timezone') . PHP_EOL;
echo 'PHP Default: ' . date_default_timezone_get() . PHP_EOL;
echo 'Carbon Locale: ' . \Carbon\Carbon::getLocale() . PHP_EOL;
echo 'Current Time: ' . \Carbon\Carbon::now()->format('Y-m-d H:i:s T') . PHP_EOL;
echo 'Indonesian: ' . \Carbon\Carbon::now()->translatedFormat('l, d F Y H:i:s T') . PHP_EOL;
"
```

## 🌍 Timezone Conversion

### Converting from other timezones
```php
// Convert UTC to WIB
$utcTime = Carbon::parse('2025-06-17 01:16:37', 'UTC');
$wibTime = $utcTime->setTimezone('Asia/Jakarta'); 
// Result: 2025-06-17 08:16:37 WIB

// Convert from timestamp
$timestamp = 1718602597;
$wibTime = Carbon::createFromTimestamp($timestamp, 'Asia/Jakarta');
// Result: 2025-06-17 08:16:37 WIB

// Parse with explicit timezone
$time = Carbon::parse('2025-06-17 08:16:37', 'Asia/Jakarta');
```

### Storing in database
```php
// All timestamps are automatically stored in Asia/Jakarta timezone
$report = new DailyReport();
$report->created_at = Carbon::now(); // Stored as: 2025-06-17 08:16:37
$report->save();

// Reading from database  
$report = DailyReport::first();
echo $report->created_at->format('Y-m-d H:i:s T'); // 2025-06-17 08:16:37 WIB
```

## 📝 Best Practices

### 1. Always use Carbon for date operations
```php
// ✅ Good
$date = Carbon::now();
$date = Carbon::today();
$date = Carbon::parse('2025-06-17');

// ❌ Avoid
$date = date('Y-m-d H:i:s');
$date = new DateTime();
```

### 2. Use consistent formatting
```php
// For display
$display = $date->translatedFormat('d F Y'); // 17 Juni 2025

// For database
$database = $date->format('Y-m-d H:i:s'); // 2025-06-17 08:16:37

// For API/JSON
$api = $date->toISOString(); // 2025-06-17T08:16:37+07:00
```

### 3. Handle user input dates
```php
// Parse user input with explicit timezone
$userDate = Carbon::createFromFormat('d/m/Y', $input, 'Asia/Jakarta');

// Or parse and set timezone
$userDate = Carbon::parse($input)->setTimezone('Asia/Jakarta');
```

## 🔄 Migration from UTC

If migrating from a UTC-based system:

### 1. Update existing timestamps
```sql
-- Convert existing UTC timestamps to WIB (add 7 hours)
UPDATE daily_reports SET created_at = DATE_ADD(created_at, INTERVAL 7 HOUR);
UPDATE daily_reports SET updated_at = DATE_ADD(updated_at, INTERVAL 7 HOUR);
```

### 2. Verify after migration
```php
// Check a few records to ensure correct conversion
$reports = DailyReport::take(5)->get();
foreach ($reports as $report) {
    echo $report->created_at->format('Y-m-d H:i:s T') . PHP_EOL;
}
```

## 📊 Performance Considerations

### Database Indexes
- Ensure indexes on datetime columns account for timezone queries
- Consider adding indexes on common date range queries

### Caching
- Cache timezone-formatted dates to avoid repeated calculations
- Use Carbon's caching features for frequently accessed dates

---

## Quick Reference

| Component | Timezone | Format Example |
|-----------|----------|----------------|
| Laravel App | Asia/Jakarta | WIB |
| Carbon | Asia/Jakarta | 2025-06-17 08:16:37 WIB |
| Database | +07:00 | 2025-06-17 08:16:37 |
| User Display | Indonesian | 17 Juni 2025, 08:16 WIB |

✅ **Status**: All timezone configurations are properly set for Indonesian localization (UTC+7) 