# SiGAP - Troubleshooting Guide

This guide covers common issues you might encounter with SiGAP (Sistem Informasi Giat Aktivitas Pekerjaan) and their solutions.

## Asset Loading Issues

### Issue: Error Loading Resources from Vite Server (port 5173)

**Error Message:**
```
GET http://[::1]:5173/resources/css/app.css net::ERR_CONNECTION_REFUSED
GET http://[::1]:5173/@vite/client net::ERR_CONNECTION_REFUSED
GET http://[::1]:5173/resources/js/app.js net::ERR_CONNECTION_REFUSED
```

**Solution:**
1. **Start the Vite Development Server:**
   ```bash
   npm run dev
   ```
   This will start the Vite development server on port 5173.

2. **OR Use Production Build:**
   ```bash
   npm run build
   ```
   Then modify your `.env` file with `APP_ENV=production`

3. **Clear Caches:**
   ```bash
   php artisan view:clear
   php artisan cache:clear
   php artisan config:clear
   ```
   
### Issue: Assets Not Loading in Production Mode

**Error Message:**
```
GET http://example.com/build/assets/app.css net::ERR_ABORTED 404 (Not Found)
GET http://example.com/build/assets/app.js net::ERR_ABORTED 404 (Not Found)
```

**Solution:**
1. **Verify the Build Files Exist:**
   Check that the files exist in `public/build/assets/` directory
   
2. **Rebuild Assets:**
   ```bash
   npm run build
   ```
   
3. **Check File Permissions:**
   Ensure web server has read access to the `public/build` directory

4. **Verify Manifest File:**
   Check that `public/build/manifest.json` exists and contains the correct paths

## Chart.js Errors

### Issue: Chart Canvas Already in Use

**Error Message:**
```
Error initializing trend chart: Error: Canvas is already in use. Chart with ID '0' must be destroyed before the canvas with ID 'reportTrendChart' can be reused.
```

**Solution:**
1. **Ensure Single Initialization:**
   Use a flag to prevent multiple chart initializations:
   ```javascript
   if (window.chartInitialized) return;
   window.chartInitialized = true;
   ```
   
2. **Destroy Existing Charts:**
   Before creating a new chart, destroy any existing ones:
   ```javascript
   const existingChart = Chart.getChart(canvasElement);
   if (existingChart) {
       existingChart.destroy();
   }
   ```
   
3. **Clear Browser Cache:**
   Hard-refresh your browser (Ctrl+F5 or Command+Shift+R)

### Issue: Chart Elements Not Found

**Error Message:**
```
statusChart canvas element not found
departmentChart canvas element not found
```

**Solution:**
1. **Check Element IDs:**
   Ensure the HTML elements with these IDs exist in your templates
   
2. **Delay Chart Initialization:**
   Wait for DOM to fully load before initializing charts:
   ```javascript
   window.addEventListener('DOMContentLoaded', function() {
       setTimeout(initializeCharts, 300);
   });
   ```

## Alpine.js Errors

### Issue: Multiple Instances of Alpine Running

**Error Message:**
```
Detected multiple instances of Alpine running
```

**Solution:**
1. **Add Alpine Initialization Flag:**
   ```javascript
   <script>
       window.alpineInitialized = false;
   </script>
   ```
   
2. **Check for Duplicate Alpine.js Imports:**
   Ensure Alpine.js is only imported once in your application
   
3. **Use Livewire Event Handlers:**
   ```javascript
   document.addEventListener('livewire:initialized', function() {
       if (!window.alpineInitialized) {
           window.alpineInitialized = true;
       }
   });
   ```

## Database Connection Issues

### Issue: Could Not Connect to Database

**Error Message:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solution:**
1. **Check Database Credentials:**
   Verify credentials in `.env` file
   
2. **Ensure Database Server is Running:**
   Check that MySQL/PostgreSQL service is active
   
3. **Check Host and Port:**
   Verify `DB_HOST` and `DB_PORT` in `.env` file

## File Upload Issues

### Issue: Unable to Upload Files

**Error Message:**
```
Post Content Too Large
```

**Solution:**
1. **Check PHP Configuration:**
   Increase limits in php.ini:
   ```
   upload_max_filesize = 10M
   post_max_size = 10M
   ```
   
2. **Check Storage Permissions:**
   Ensure `storage` directory is writable by web server
   
3. **Verify Symbolic Links:**
   Run `php artisan storage:link` to create symbolic links

## Performance Issues

### Issue: Slow Dashboard Loading

**Solution:**
1. **Optimize Database Queries:**
   Check for N+1 query issues and add proper indexing
   
2. **Enable Caching:**
   Implement Redis or Memcached for query caching
   
3. **Optimize Asset Loading:**
   Use production builds to minimize and combine assets
   
4. **Enable OPcache:**
   Enable OPcache in php.ini for better PHP performance

## Cache-Related Issues

### Issue: Changes Not Reflecting After Update

**Solution:**
1. **Clear All Caches:**
   ```bash
   php artisan optimize:clear
   ```
   This clears all Laravel caches at once
   
2. **Clear Browser Cache:**
   Hard-refresh your browser (Ctrl+F5 or Command+Shift+R)

## 🖼️ Image Processing Issues

### Error: "GD PHP extension must be installed to use this driver"

This error occurs when the GD extension is not installed or enabled in PHP, which is required for image processing and compression.

#### **Solution 1: Enable GD Extension in XAMPP**

1. **Open php.ini file**:
   - Location: `C:\xampp\php\php.ini`
   - Or through XAMPP Control Panel → Apache → Config → PHP (php.ini)

2. **Find and uncomment the GD extension**:
   ```ini
   # Find this line (use Ctrl+F):
   ;extension=gd
   
   # Remove the semicolon to enable it:
   extension=gd
   ```

3. **Save the file and restart Apache** in XAMPP Control Panel

4. **Verify the extension is loaded**:
   ```bash
   php -m | findstr -i gd
   ```

#### **Solution 2: Alternative PHP Installations**

For other PHP installations:

**Windows (using Chocolatey):**
```bash
choco install php --params="/InstallDir:C:\php /EnableGd"
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get install php-gd
sudo systemctl restart apache2
```

**macOS (using Homebrew):**
```bash
brew install php
# GD is typically included by default
```

#### **Verification Commands**

After enabling GD extension:

```bash
# Check if GD is loaded
php -m | grep -i gd

# Check GD version and supported formats
php -r "print_r(gd_info());"

# Check specific function availability
php -r "echo function_exists('imagecreatetruecolor') ? 'GD Available' : 'GD Not Available';"
```

#### **Fallback Behavior**

The system now includes automatic fallback when GD is not available:

- **With GD**: Images are compressed and resized (max 1920px, 60% quality)
- **Without GD**: Images are stored as-is without processing
- **Warning logged**: Check `storage/logs/laravel.log` for fallback notifications

#### **Production Recommendations**

For production environments:

1. **Always enable GD extension** for optimal performance
2. **Monitor logs** for fallback warnings:
   ```bash
   tail -f storage/logs/laravel.log | grep "Image processing failed"
   ```
3. **Consider alternative image drivers** if GD is not available:
   ```php
   // In config/image.php (if needed)
   'driver' => env('IMAGE_DRIVER', 'imagick'), // Alternative: imagick
   ```

## 📁 File Upload Issues

### Large File Upload Problems

If experiencing issues with large file uploads:

1. **Check PHP configuration** in `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   memory_limit = 256M
   ```

2. **Check web server limits** (Apache/Nginx)

3. **Verify Laravel configuration** in `config/filesystems.php`

### Permission Issues

If files cannot be stored:

1. **Check storage permissions**:
   ```bash
   # Windows (PowerShell as Administrator)
   icacls "storage" /grant "IIS_IUSRS:(F)" /t
   
   # Linux/Mac
   chmod -R 755 storage/app/public
   chown -R www-data:www-data storage/app/public
   ```

2. **Create symbolic link**:
   ```bash
   php artisan storage:link
   ```

## 🔧 Development Environment Issues

### XAMPP Setup Problems

Common XAMPP issues and solutions:

1. **Port conflicts** (Apache/MySQL already running)
2. **Permission issues** with htdocs directory
3. **PHP version compatibility** with Laravel 12

### Node.js and NPM Issues

For frontend compilation problems:

```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Verify Node.js version (Laravel 12 requires Node 18+)
node --version
```

## 🗄️ Database Issues

### Migration Problems

Common database migration issues:

```bash
# Reset migrations (CAUTION: This will delete all data)
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status

# Rollback specific migration
php artisan migrate:rollback --step=1
```

### Connection Issues

Database connection problems:

1. **Check `.env` configuration**
2. **Verify database server is running**
3. **Test connection manually**:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

## 📧 Email and Notification Issues

### Email Configuration

For email sending problems:

1. **Check mail configuration** in `.env`
2. **Test email sending**:
   ```bash
   php artisan tinker
   Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
   ```

### Notification System Issues

If notifications are not working:

1. **Check queue configuration**
2. **Run queue worker**:
   ```bash
   php artisan queue:work
   ```
3. **Check notification preferences** in user settings

## 🔍 Performance Issues

### Slow Query Detection

The system automatically logs slow queries (>500ms). Check logs:

```bash
tail -f storage/logs/laravel.log | grep "Slow query"
```

### Optimization Commands

Regular maintenance commands:

```bash
# Clear all caches
composer run optimize:clear

# Optimize for production
composer run optimize

# Clear specific caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🔐 Authentication Issues

### User Login Problems

Common authentication issues:

1. **Check user credentials**
2. **Verify user is active** and has proper role
3. **Check session configuration**

### Permission Denied Errors

For authorization issues:

1. **Check user roles** and permissions
2. **Verify middleware** is properly configured
3. **Check policy definitions**

## 🧪 Testing and Debugging

### Debug Mode

Enable debug mode for development:

```env
APP_DEBUG=true
APP_ENV=local
```

### Log Monitoring

Monitor application logs:

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Search for specific errors
grep -i "error" storage/logs/laravel.log

# Check slow queries
grep "Slow query" storage/logs/laravel.log
```

### Common Error Messages

**"Class not found"**:
```bash
composer dump-autoload
```

**"Route not found"**:
```bash
php artisan route:clear
php artisan route:cache
```

**"View not found"**:
```bash
php artisan view:clear
```

## 🆘 Getting Help

### Debug Information Collection

When reporting issues, include:

1. **PHP version**: `php --version`
2. **Laravel version**: `php artisan --version`
3. **Composer version**: `composer --version`
4. **Node.js version**: `node --version`
5. **Error logs**: Check `storage/logs/laravel.log`
6. **Browser console errors** (for frontend issues)

### Log Analysis

Key areas to check:

- Application logs: `storage/logs/laravel.log`
- Web server logs: XAMPP logs directory
- Database logs: Check database server logs
- PHP error logs: Check PHP error log location

---

## Quick Reference Commands

```bash
# Development
composer run dev              # Start all dev servers
composer run test            # Run tests

# Production
composer run optimize        # Cache everything
composer run optimize:clear  # Clear all caches

# Troubleshooting
php artisan config:clear     # Clear config cache
php artisan route:clear      # Clear route cache
php artisan view:clear       # Clear view cache
php artisan cache:clear      # Clear application cache

# File permissions (Linux/Mac)
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Database
php artisan migrate          # Run migrations
php artisan migrate:fresh --seed  # Fresh start with sample data
```

## How to Report Issues

If you encounter issues not covered in this guide:

1. **Check Laravel Logs:**
   Look at `storage/logs/laravel.log` for detailed error information
   
2. **Check Browser Console:**
   Open browser's developer tools (F12) and look at the Console tab
   
3. **Provide Details When Reporting:**
   - Laravel version
   - PHP version
   - Browser and version
   - Complete error message
   - Steps to reproduce 