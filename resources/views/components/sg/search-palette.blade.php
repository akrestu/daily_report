{{-- Pencarian global: Ctrl K (desktop) atau tombol cari di app bar. Bottom sheet <992px, modal tengah di desktop. --}}
<div class="modal fade sg-sheet-modal sg-search" id="sg-search" tabindex="-1" aria-labelledby="sg-search-title" aria-hidden="true"
     x-data="sgSearch(@js(['url' => route('search')]))">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="sg-sheet-handle d-lg-none" aria-hidden="true"></div>
            <h2 class="visually-hidden" id="sg-search-title">Cari</h2>
            <div class="sg-search__field">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="text" inputmode="search" x-ref="input" x-model="q" @input="input()" class="form-control" placeholder="Cari laporan, tiket, pengguna…"
                       aria-label="Cari laporan, tiket, pengguna" autocomplete="off" enterkeyhint="go"
                       @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="go()">
                <span class="spinner-border spinner-border-sm text-muted" x-show="loading" x-cloak aria-hidden="true"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-2" aria-live="polite">
                <p class="text-muted small px-2 py-3 mb-0" x-show="!searched && !loading">Ketik minimal 2 huruf. Hasil hanya menampilkan data yang boleh Anda lihat.</p>
                <p class="text-muted small px-2 py-3 mb-0" x-show="searched && !groups.length && !loading" x-cloak>Tidak ada hasil untuk “<span x-text="q"></span>”.</p>
                <template x-for="group in groups" :key="group.label">
                    <section class="mb-2">
                        <h3 class="sg-section-label px-2 mt-2" x-text="group.label"></h3>
                        <template x-for="item in group.items" :key="item.url">
                            <a :href="item.url" class="sg-search-item" :class="{ 'is-active': index(item) === cursor }" @mouseenter="cursor = index(item)">
                                <i class="bi" :class="item.icon" aria-hidden="true"></i>
                                <span class="min-w-0">
                                    <span class="d-block fw-semibold sg-clamp-1" x-text="item.title"></span>
                                    <span class="d-block small text-muted sg-clamp-1" x-text="item.meta"></span>
                                </span>
                            </a>
                        </template>
                    </section>
                </template>
            </div>
            <div class="sg-search__foot d-none d-lg-flex small text-muted">
                <span><kbd>↑</kbd> <kbd>↓</kbd> pilih</span><span><kbd>Enter</kbd> buka</span><span><kbd>Esc</kbd> tutup</span>
            </div>
        </div>
    </div>
</div>
