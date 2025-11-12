{{-- PWA Install Prompt Component --}}

<!-- Install Banner (Top of page) -->
<div id="pwaInstallBanner" class="pwa-install-banner" style="display: none;">
    <div class="container-fluid">
        <div class="row align-items-center py-2">
            <div class="col-auto">
                <img src="{{ asset('icons/icon-72x72.png') }}" alt="SiGAP" class="pwa-banner-icon">
            </div>
            <div class="col">
                <h6 class="mb-0 fw-bold text-white">Install SiGAP</h6>
                <small class="text-white-50">Install aplikasi untuk akses lebih cepat dan dapat digunakan offline</small>
            </div>
            <div class="col-auto">
                <button class="btn btn-light btn-sm me-2" id="installPwaBtn">
                    <i class="fas fa-download me-1"></i> Install
                </button>
                <button class="btn btn-outline-light btn-sm" id="dismissBannerBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Install Button (Bottom Right) -->
<button id="pwaFloatingBtn" class="pwa-floating-btn" style="display: none;" title="Install SiGAP">
    <i class="fas fa-download"></i>
    <span class="pwa-floating-text">Install App</span>
</button>

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
/* PWA Install Banner */
.pwa-install-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1030;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.pwa-banner-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

/* PWA Floating Button */
.pwa-floating-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 56px;
    height: 56px;
    border-radius: 28px;
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 20px rgba(13, 110, 253, 0.4);
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 1025;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.pwa-floating-btn:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 25px rgba(13, 110, 253, 0.6);
    width: 140px;
}

.pwa-floating-btn i {
    font-size: 20px;
    transition: all 0.3s ease;
}

.pwa-floating-text {
    opacity: 0;
    margin-left: 0;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    transition: all 0.3s ease;
    max-width: 0;
    overflow: hidden;
}

.pwa-floating-btn:hover .pwa-floating-text {
    opacity: 1;
    margin-left: 8px;
    max-width: 100px;
}

.pwa-floating-btn:active {
    transform: translateY(0) scale(0.95);
}

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

/* Mobile Responsive */
@media (max-width: 767.98px) {
    .pwa-install-banner .col-auto:first-child {
        display: none;
    }

    .pwa-install-banner small {
        display: none;
    }

    .pwa-floating-btn {
        bottom: 80px; /* Avoid conflict with mobile bottom nav */
        right: 16px;
        width: 48px;
        height: 48px;
    }

    .pwa-floating-btn:hover {
        width: 48px;
    }

    .pwa-floating-btn i {
        font-size: 18px;
    }

    .pwa-floating-text {
        display: none;
    }
}

/* Adjust content when banner is shown */
body.pwa-banner-shown {
    padding-top: 70px;
}

body.pwa-banner-shown .content-wrapper {
    padding-top: 0;
}

@media (max-width: 991.98px) {
    body.pwa-banner-shown {
        padding-top: 60px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let deferredPrompt;
    const installBanner = document.getElementById('pwaInstallBanner');
    const floatingBtn = document.getElementById('pwaFloatingBtn');
    const installBtn = document.getElementById('installPwaBtn');
    const dismissBtn = document.getElementById('dismissBannerBtn');
    const installModal = new bootstrap.Modal(document.getElementById('pwaInstallModal'));
    const instructionsModal = new bootstrap.Modal(document.getElementById('pwaInstructionsModal'));

    // Check if already installed
    function isInstalled() {
        // Check if running in standalone mode (installed PWA)
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true ||
               localStorage.getItem('pwa-installed') === 'true';
    }

    // Check if banner was dismissed
    function isBannerDismissed() {
        const dismissed = localStorage.getItem('pwa-banner-dismissed');
        if (!dismissed) return false;

        // Show banner again after 7 days
        const dismissedDate = new Date(dismissed);
        const daysSinceDismissed = (new Date() - dismissedDate) / (1000 * 60 * 60 * 24);
        return daysSinceDismissed < 7;
    }

    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('PWA: beforeinstallprompt event fired');

        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();

        // Stash the event so it can be triggered later
        deferredPrompt = e;

        // Don't show UI if already installed or banner dismissed
        if (isInstalled() || isBannerDismissed()) {
            return;
        }

        // Show install banner and floating button
        setTimeout(() => {
            installBanner.style.display = 'block';
            document.body.classList.add('pwa-banner-shown');

            // Also show floating button after 3 seconds
            setTimeout(() => {
                floatingBtn.style.display = 'flex';
            }, 3000);
        }, 2000); // Show after 2 seconds
    });

    // Handle install button click
    async function handleInstallClick() {
        if (!deferredPrompt) {
            // If no prompt available, show manual instructions
            console.log('PWA: No install prompt available, showing instructions');
            instructionsModal.show();
            return;
        }

        // Hide the banner and button
        installBanner.style.display = 'none';
        floatingBtn.style.display = 'none';
        document.body.classList.remove('pwa-banner-shown');

        // Show the install prompt
        deferredPrompt.prompt();

        // Wait for the user to respond to the prompt
        const { outcome } = await deferredPrompt.userChoice;

        console.log(`PWA: User response to install prompt: ${outcome}`);

        if (outcome === 'accepted') {
            // User accepted the install
            localStorage.setItem('pwa-installed', 'true');

            // Show success modal
            setTimeout(() => {
                installModal.show();
            }, 500);
        }

        // Clear the deferredPrompt
        deferredPrompt = null;
    }

    // Install button clicks
    if (installBtn) {
        installBtn.addEventListener('click', handleInstallClick);
    }

    if (floatingBtn) {
        floatingBtn.addEventListener('click', handleInstallClick);
    }

    // Dismiss banner
    if (dismissBtn) {
        dismissBtn.addEventListener('click', () => {
            installBanner.style.display = 'none';
            document.body.classList.remove('pwa-banner-shown');
            localStorage.setItem('pwa-banner-dismissed', new Date().toISOString());

            // Keep floating button visible
            setTimeout(() => {
                floatingBtn.style.display = 'flex';
            }, 1000);
        });
    }

    // Listen for app installed event
    window.addEventListener('appinstalled', (evt) => {
        console.log('PWA: App was installed successfully');

        // Hide all install prompts
        installBanner.style.display = 'none';
        floatingBtn.style.display = 'none';
        document.body.classList.remove('pwa-banner-shown');

        // Mark as installed
        localStorage.setItem('pwa-installed', 'true');

        // Show success modal
        setTimeout(() => {
            installModal.show();
        }, 500);
    });

    // For iOS - show instructions if detected
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    if (isIOS && !isInstalled() && !isBannerDismissed()) {
        // iOS doesn't support beforeinstallprompt, show banner with instructions
        setTimeout(() => {
            installBanner.style.display = 'block';
            document.body.classList.add('pwa-banner-shown');
            floatingBtn.style.display = 'flex';

            // Override install button to show instructions
            if (installBtn) {
                installBtn.addEventListener('click', () => {
                    instructionsModal.show();
                });
            }
        }, 2000);
    }

    // If already installed, don't show any prompts
    if (isInstalled()) {
        console.log('PWA: App is already installed');
        installBanner.style.display = 'none';
        floatingBtn.style.display = 'none';
        document.body.classList.remove('pwa-banner-shown');
    }
});
</script>
