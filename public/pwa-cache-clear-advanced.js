/**
 * COMPREHENSIVE PWA ICON CACHE CLEARING SCRIPT
 * 
 * This script performs aggressive cache clearing for PWA icons.
 * Use this if icons are still showing old versions after updates.
 * 
 * Steps:
 * 1. Open browser DevTools (F12)
 * 2. Go to Console tab
 * 3. Copy-paste this entire script
 * 4. Press Enter
 * 5. Refresh the page (Ctrl+Shift+R for hard refresh)
 * 
 * Expected output:
 * - Multiple "Cache cleared" messages
 * - Service workers unregistered
 * - Page reload
 */

(async function clearPWACache() {
    console.log('='.repeat(60));
    console.log('🔄 PWA ICON CACHE CLEARING SCRIPT');
    console.log('='.repeat(60));
    
    let totalDeleted = 0;
    let totalUnregistered = 0;
    
    // ===== STEP 1: Clear all Service Worker Caches =====
    console.log('\n📦 STEP 1: Clearing Service Worker Caches...');
    if ('caches' in window) {
        try {
            const cacheNames = await caches.keys();
            console.log('Found caches:', cacheNames);
            
            for (const cacheName of cacheNames) {
                const deleted = await caches.delete(cacheName);
                if (deleted) {
                    console.log(`  ✅ Deleted cache: ${cacheName}`);
                    totalDeleted++;
                } else {
                    console.log(`  ❌ Failed to delete cache: ${cacheName}`);
                }
            }
        } catch (error) {
            console.error('Error clearing caches:', error);
        }
    } else {
        console.warn('Caches API not available');
    }
    
    // ===== STEP 2: Unregister Service Workers =====
    console.log('\n🔧 STEP 2: Unregistering Service Workers...');
    if ('serviceWorker' in navigator) {
        try {
            const registrations = await navigator.serviceWorker.getRegistrations();
            console.log('Found service workers:', registrations.length);
            
            for (const registration of registrations) {
                const unregistered = await registration.unregister();
                if (unregistered) {
                    console.log(`  ✅ Unregistered service worker`);
                    totalUnregistered++;
                } else {
                    console.log(`  ❌ Failed to unregister service worker`);
                }
            }
        } catch (error) {
            console.error('Error unregistering service workers:', error);
        }
    } else {
        console.warn('Service Worker API not available');
    }
    
    // ===== STEP 3: Clear localStorage =====
    console.log('\n💾 STEP 3: Clearing localStorage PWA Data...');
    try {
        const pwaKeys = ['pwa-installed', 'pwa-icon-version', 'theme'];
        pwaKeys.forEach(key => {
            if (localStorage.getItem(key)) {
                localStorage.removeItem(key);
                console.log(`  ✅ Removed: ${key}`);
            }
        });
    } catch (error) {
        console.error('Error clearing localStorage:', error);
    }
    
    // ===== STEP 4: Clear IndexedDB =====
    console.log('\n🗄️ STEP 4: Clearing IndexedDB...');
    try {
        if ('indexedDB' in window) {
            // List all databases (if possible)
            const databases = await indexedDB.databases();
            console.log('Found IndexedDB databases:', databases.length);
            
            for (const db of databases) {
                indexedDB.deleteDatabase(db.name);
                console.log(`  ✅ Scheduled deletion of database: ${db.name}`);
            }
        } else {
            console.warn('IndexedDB not available');
        }
    } catch (error) {
        console.error('Error clearing IndexedDB:', error);
    }
    
    // ===== STEP 5: Summary =====
    console.log('\n' + '='.repeat(60));
    console.log('📊 SUMMARY:');
    console.log(`  • Caches deleted: ${totalDeleted}`);
    console.log(`  • Service workers unregistered: ${totalUnregistered}`);
    console.log('='.repeat(60));
    
    // ===== STEP 6: Force reload =====
    console.log('\n🔄 STEP 6: Hard Refresh Page in 2 seconds...');
    console.log('💡 TIP: Icons should now show the updated version!');
    console.log('');
    
    setTimeout(() => {
        console.log('✅ Reloading page with cache bypass...');
        window.location.href = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 't=' + Date.now();
    }, 2000);
})();
