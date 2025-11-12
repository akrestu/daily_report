<x-app-layout>
    <x-slot name="header">
        Install Aplikasi SiGAP
    </x-slot>

    <div class="container-fluid px-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Hero Section -->
                <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-6 d-flex align-items-center justify-content-center p-5" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                                <div class="text-center text-white">
                                    <img src="{{ asset('icons/icon-192x192.png') }}" alt="SiGAP" class="mb-4" style="width: 128px; height: 128px; border-radius: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                                    <h3 class="fw-bold mb-2">SiGAP PWA</h3>
                                    <p class="mb-0 opacity-75">Progressive Web Application</p>
                                </div>
                            </div>
                            <div class="col-md-6 p-5">
                                <h4 class="fw-bold mb-4">Kenapa Install SiGAP?</h4>
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Akses Lebih Cepat</strong><br>
                                        <small class="text-muted ms-4">Buka aplikasi langsung dari home screen</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Mode Offline</strong><br>
                                        <small class="text-muted ms-4">Tetap dapat melihat data yang sudah di-cache</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Hemat Storage</strong><br>
                                        <small class="text-muted ms-4">Ukuran lebih kecil dibanding aplikasi native</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong>Auto Update</strong><br>
                                        <small class="text-muted ms-4">Selalu mendapat versi terbaru otomatis</small>
                                    </li>
                                </ul>

                                <div class="d-grid gap-2 mt-4">
                                    <button class="btn btn-primary btn-lg" id="installPwaPageBtn">
                                        <i class="fas fa-download me-2"></i>
                                        Install Sekarang
                                    </button>
                                    <button class="btn btn-outline-secondary" id="showInstructionsBtn">
                                        <i class="fas fa-question-circle me-2"></i>
                                        Lihat Panduan Install
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon bg-primary bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fas fa-bolt text-primary fa-2x"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Performa Tinggi</h5>
                                <p class="text-muted small mb-0">Loading lebih cepat dengan teknologi caching modern</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon bg-success bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fas fa-wifi text-success fa-2x"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Offline Ready</h5>
                                <p class="text-muted small mb-0">Tetap dapat mengakses data saat tidak ada koneksi</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="feature-icon bg-info bg-opacity-10 rounded-circle p-3 d-inline-flex mb-3">
                                    <i class="fas fa-mobile-alt text-info fa-2x"></i>
                                </div>
                                <h5 class="fw-bold mb-2">Responsive</h5>
                                <p class="text-muted small mb-0">Tampilan sempurna di semua perangkat</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Requirements -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Persyaratan Sistem
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">
                                    <i class="fab fa-android text-success me-2"></i>
                                    Android
                                </h6>
                                <ul class="small text-muted">
                                    <li>Chrome 89 atau lebih baru</li>
                                    <li>Edge 89 atau lebih baru</li>
                                    <li>Firefox 90 atau lebih baru</li>
                                    <li>Samsung Internet 14 atau lebih baru</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">
                                    <i class="fab fa-apple text-dark me-2"></i>
                                    iOS / iPadOS
                                </h6>
                                <ul class="small text-muted">
                                    <li>Safari 14.5 atau lebih baru</li>
                                    <li>iOS 14.5+ atau iPadOS 14.5+</li>
                                    <li>Chrome di iOS (via Add to Home Screen)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-desktop text-primary me-2"></i>
                                    Desktop
                                </h6>
                                <ul class="small text-muted">
                                    <li>Windows: Chrome 89+, Edge 89+</li>
                                    <li>macOS: Chrome 89+, Edge 89+, Safari 14.1+</li>
                                    <li>Linux: Chrome 89+, Edge 89+</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            Pertanyaan Umum (FAQ)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Apa perbedaan PWA dengan aplikasi biasa?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        PWA (Progressive Web App) adalah aplikasi web yang dapat diinstall dan berfungsi seperti aplikasi native. Perbedaannya adalah PWA lebih ringan, tidak memerlukan download dari store, dan selalu mendapat update terbaru secara otomatis.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Apakah SiGAP PWA menggunakan kuota internet?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        Setelah terinstall, SiGAP akan menyimpan data secara cache sehingga halaman yang sudah pernah dibuka dapat diakses tanpa koneksi internet. Data baru tetap memerlukan koneksi, tetapi penggunaan kuota lebih efisien berkat sistem caching.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Bagaimana cara uninstall PWA?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        <strong>Android:</strong> Tekan dan tahan icon aplikasi, lalu pilih "Uninstall" atau "App info" > "Uninstall".<br>
                                        <strong>iOS:</strong> Tekan dan tahan icon aplikasi, pilih "Remove App" > "Delete App".<br>
                                        <strong>Desktop:</strong> Buka aplikasi, klik menu (⋮) > "Uninstall SiGAP".
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        Apakah PWA aman?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body text-muted">
                                        Ya, sangat aman! PWA menggunakan protokol HTTPS yang terenkripsi, sama seperti saat Anda mengakses website banking. Semua data login dan aktivitas Anda dilindungi dengan enkripsi.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Handle install button on this page
        document.getElementById('installPwaPageBtn')?.addEventListener('click', function() {
            // Trigger the floating button click (which has the install logic)
            const floatingBtn = document.getElementById('pwaFloatingBtn');
            if (floatingBtn) {
                floatingBtn.click();
            } else {
                // Show instructions if install not available
                const instructionsModal = new bootstrap.Modal(document.getElementById('pwaInstructionsModal'));
                instructionsModal.show();
            }
        });

        // Show instructions modal
        document.getElementById('showInstructionsBtn')?.addEventListener('click', function() {
            const instructionsModal = new bootstrap.Modal(document.getElementById('pwaInstructionsModal'));
            instructionsModal.show();
        });
    </script>
    @endpush
</x-app-layout>
