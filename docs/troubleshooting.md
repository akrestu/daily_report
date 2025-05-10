# Daily Report System - Troubleshooting Guide

This guide covers common issues you might encounter with the Daily Report System and their solutions.

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