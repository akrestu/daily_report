{{-- PWA Install Prompt Component --}}

{{-- Install Banner and Floating Button removed - users can install via browser menu --}}

<!-- Install Success Modal -->
<div class="modal fade" id="pwaInstallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div class="pwa-success-icon mx-auto mb-3">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <h5 class="fw-bold">Instalasi Berhasil!</h5>
                    <p class="text-muted mb-0">SiGAP telah terinstall di perangkat Anda</p>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i> OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Install Instructions Modal (for unsupported browsers) -->
<div class="modal fade" id="pwaInstructionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Cara Install SiGAP
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="install-instructions">
                    <!-- Android Chrome -->
                    <div class="instruction-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="instruction-icon me-3">
                                <i class="fab fa-chrome text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Android (Chrome)</h6>
                                <ol class="small mb-0 ps-3">
                                    <li>Tap menu <i class="fas fa-ellipsis-v"></i> di pojok kanan atas</li>
                                    <li>Pilih "Add to Home screen" atau "Install app"</li>
                                    <li>Tap "Add" atau "Install"</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- iOS Safari -->
                    <div class="instruction-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="instruction-icon me-3">
                                <i class="fab fa-safari text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">iOS (Safari)</h6>
                                <ol class="small mb-0 ps-3">
                                    <li>Tap tombol Share <i class="fas fa-share"></i> di bawah</li>
                                    <li>Scroll dan pilih "Add to Home Screen"</li>
                                    <li>Tap "Add"</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Chrome -->
                    <div class="instruction-item">
                        <div class="d-flex align-items-start">
                            <div class="instruction-icon me-3">
                                <i class="fas fa-desktop text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Desktop (Chrome/Edge)</h6>
                                <ol class="small mb-0 ps-3">
                                    <li>Klik icon install <i class="fas fa-plus-circle"></i> di address bar</li>
                                    <li>Atau klik menu <i class="fas fa-ellipsis-v"></i> > "Install SiGAP"</li>
                                    <li>Klik "Install"</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4 mb-0">
                    <i class="fas fa-lightbulb me-2"></i>
                    <small>Setelah terinstall, Anda dapat membuka SiGAP seperti aplikasi native dan menggunakannya secara offline!</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Success Icon */
.pwa-success-icon {
    width: 80px;
    height: 80px;
    background: rgba(25, 135, 84, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pwa-success-icon i {
    font-size: 48px;
}

/* Install Instructions */
.instruction-icon {
    width: 40px;
    height: 40px;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.instruction-icon i {
    font-size: 20px;
}

.instruction-item ol {
    color: #6c757d;
    line-height: 1.8;
}

/* No additional mobile styles needed */
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // PWA installation can still be done via browser menu
    // This script manages service worker updates and installation

    const installModal = new bootstrap.Modal(document.getElementById('pwaInstallModal'));

    // Register service worker with update handling
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' }).then(function(registration) {
            console.log('PWA: Service Worker registered successfully');

            // Send message to clear old caches
            if (registration.active) {
                registration.active.postMessage({ type: 'CLEAR_OLD_CACHES' });
                console.log('PWA: Sent message to clear old caches');
            }

            // Check for updates every 30 seconds (frequent checks to detect icon changes)
            setInterval(() => {
                registration.update();
                console.log('PWA: Checking for service worker updates...');
            }, 30000);

            // Handle service worker updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;

                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        console.log('PWA: New service worker available');

                        // Tell the new service worker to skip waiting
                        newWorker.postMessage({ type: 'SKIP_WAITING' });

                        // Reload the page to activate new service worker
                        setTimeout(() => {
                            console.log('PWA: Reloading to activate new service worker');
                            window.location.reload();
                        }, 1000);
                    }
                });
            });
        }).catch(function(error) {
            console.log('PWA: Service Worker registration failed:', error);
        });

        // When service worker controller changes, reload the page
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('PWA: Service worker controller changed, reloading...');
            window.location.reload();
        });

        // Clean up old caches on page load
        if ('caches' in window) {
            caches.keys().then(cacheNames => {
                let deletedCount = 0;
                cacheNames.forEach(cacheName => {
                    // Delete caches with old version names
                    if (cacheName.includes('v1') || cacheName.includes('1.1') || cacheName.includes('1.0')) {
                        console.log('PWA: Deleting old cache:', cacheName);
                        caches.delete(cacheName);
                        deletedCount++;
                    }
                    // Also delete 'pwa-icons' to force fresh icons
                    if (cacheName === 'pwa-icons') {
                        console.log('PWA: Deleting pwa-icons cache to refresh icons');
                        caches.delete(cacheName);
                        deletedCount++;
                    }
                });
                if (deletedCount > 0) {
                    console.log('PWA: Deleted ' + deletedCount + ' old cache(s)');
                }
            });
        }
    }

    // Listen for app installed event
    window.addEventListener('appinstalled', (evt) => {
        console.log('PWA: App was installed successfully');

        // Mark as installed
        localStorage.setItem('pwa-installed', 'true');

        // Show success modal
        setTimeout(() => {
            installModal.show();
        }, 500);
    });

    // Prevent the default browser install prompt banner
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('PWA: beforeinstallprompt event fired - users can install via browser menu');
        // Don't prevent default - let browser show its native install UI
    });
});
</script>
