# Deployment Checklist for Daily Report System

## Before Deployment

1. **Update Environment Settings**
   - Set `APP_ENV=production` in `.env` file
   - Set `APP_DEBUG=false` in `.env` file

2. **Build Assets for Production**
   - Run `npm run build` to compile and optimize assets
   - Verify the compiled files are created in `public/build/assets` directory

3. **Clear All Caches**
   - Run `php artisan optimize:clear` (clears all caches in one command)
   - Or run individual commands:
     - `php artisan cache:clear`
     - `php artisan config:clear`
     - `php artisan view:clear`
     - `php artisan route:clear`

4. **Generate Optimized Files**
   - Run `php artisan optimize` to generate optimized files
   - Run `php artisan event:cache` to cache events

5. **Check Permissions**
   - Ensure storage directory is writable
   - Ensure bootstrap/cache directory is writable
   - Ensure public/build directory is writable and readable

## Deployment Process

1. **Upload Files to Server**
   - Upload all files to the production server
   - Make sure to include the `public/build` directory with compiled assets

2. **Create Symbolic Links**
   - Run `php artisan storage:link` to create symbolic links for file storage

3. **Update Dependencies**
   - Run `composer install --optimize-autoloader --no-dev` to install PHP dependencies
   - Run `npm ci` to install exact versions of Node.js dependencies (faster than npm install)

4. **Apply Database Changes**
   - Run `php artisan migrate --force` to apply any pending migrations

5. **Verify File Ownership and Permissions**
   - Ensure web server user can read application files
   - Ensure web server user can write to storage and cache directories
   - Make sure public/build directory and its contents are readable by the web server

## Post-Deployment Checks

1. **Verify Asset Loading**
   - Check browser console for any 404 errors on asset loading
   - Verify that CSS and JS files are being served from the correct paths
   - Check that manifest.json is being read correctly

2. **Verify Chart.js Functionality**
   - Make sure charts are rendering correctly without errors
   - Check for any duplicate chart initialization issues

3. **Test Critical Functionality**
   - Test report creation
   - Test report approval workflow
   - Test file uploads and downloads
   - Test user authentication

4. **Check Error Logs**
   - Monitor `storage/logs/laravel.log` for any errors
   - Check web server error logs for any issues

## Troubleshooting Common Issues

1. **Asset Loading Issues**
   - If assets aren't loading, verify that the compiled files exist in `public/build/assets`
   - Check that the Vite tags in blade templates are correct
   - For production builds, ensure manifest.json is accessible and contains correct paths
   - Try rebuilding assets if they're missing or incorrect

2. **Chart.js Errors**
   - If charts aren't rendering, check for JavaScript errors in the console
   - Verify that Chart.js is being loaded before chart initialization
   - Make sure chart containers exist in the DOM when chart initialization code runs
   - Prevent duplicate chart initialization by using a flag or storing chart instances globally

3. **File Storage Issues**
   - Verify symbolic links are created correctly
   - Check directory permissions for storage

4. **Performance Issues**
   - Enable OPcache in PHP for better performance
   - Consider implementing a caching strategy for database queries
   - Use a CDN for static assets if possible

## Rollback Plan

In case of critical issues after deployment:

1. **Restore Previous Version**
   - Keep a backup of the previous version ready
   - Restore database from backup if necessary

2. **Clear Caches After Rollback**
   - Clear all caches to prevent stale data issues
   - Run `php artisan optimize:clear` after rolling back

3. **Document Issues**
   - Document the issues encountered for future reference 