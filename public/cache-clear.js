/**
 * PWA Cache Clear Script
 * Gunakan script ini untuk membersihkan cache PWA jika ada masalah dengan icon lama yang masih ditampilkan
 * 
 * Usage: Copy paste kode di bawah ke browser console saat membuka aplikasi SiGAP
 */

(function() {
    console.log('🔄 PWA Cache Clear Tool Started...\n');

    let deletedCount = 0;

    // 1. Clear all service worker caches
    if ('caches' in window) {
        console.log('📦 Clearing service worker caches...');
        caches.keys().then(cacheNames => {
            cacheNames.forEach(cacheName => {
                console.log(`  - Deleting cache: ${cacheName}`);
                caches.delete(cacheName);
                deletedCount++;
            });
            console.log(`✅ Deleted ${cacheNames.length} cache(s)\n`);
        });
    }

    // 2. Unregister all service workers
    if ('serviceWorker' in navigator) {
        console.log('🔧 Unregistering service workers...');
        navigator.serviceWorker.getRegistrations().then(registrations => {
            registrations.forEach(registration => {
                registration.unregister();
                console.log(`  - Unregistered service worker`);
            });
            console.log(`✅ Unregistered ${registrations.length} service worker(s)\n`);
        });
    }

    // 3. Clear localStorage PWA data
    console.log('💾 Clearing PWA localStorage...');
    const pwaKeys = Object.keys(localStorage).filter(key => key.includes('pwa'));
    pwaKeys.forEach(key => {
        console.log(`  - Clearing: ${key}`);
        localStorage.removeItem(key);
    });
    console.log(`✅ Cleared ${pwaKeys.length} localStorage item(s)\n`);

    // 4. Clear IndexedDB (if used)
    console.log('🗄️ Checking IndexedDB...');
    if (window.indexedDB) {
        const dbNames = [];
        // Note: There's no direct way to list all DBs, so we'll just log
        console.log(`  - IndexedDB available. Manually clear from DevTools if needed\n`);
    }

    // 5. Force reload
    console.log('🔄 Reloading page in 2 seconds...\n');
    setTimeout(() => {
        window.location.reload(true); // true = bypass cache
    }, 2000);

    console.log('✅ Cache clear process initiated!');
    console.log('📌 After reload, the new PWA icon should appear.');
    console.log('📌 If still using old icon, try: Hard Refresh (Ctrl+Shift+R or Cmd+Shift+R)\n');
})();
