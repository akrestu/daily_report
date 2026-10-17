{{-- Admin · Pembersihan Laporan — pratinjau dulu (preview), baru jalankan (execute) setelah konfirmasi ketik HAPUS --}}
@use('App\Support\Format')
<x-app-layout title="Pembersihan Laporan">
    <div x-data="{
            days: 365,
            statuses: [],
            keep: false,
            busy: false,
            preview: null,
            error: '',
            typed: '',
            labels: { pending: 'Belum mulai', in_progress: 'Dalam proses', completed: 'Selesai', approved: 'Disetujui', rejected: 'Ditolak' },
            body() {
                const data = new FormData();
                data.append('days', this.days);
                this.statuses.forEach((s) => data.append('statuses[]', s));
                if (this.keep) data.append('keep_attachments', '1');
                return data;
            },
            async send(url) {
                this.busy = true; this.error = '';
                try {
                    const res = await fetch(url, { method: 'POST', body: this.body(), headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                    const json = await res.json();
                    if (!res.ok || json.success === false) throw new Error(json.message || Object.values(json.errors || {})[0]?.[0] || 'Gagal memproses.');
                    return json;
                } catch (e) { this.error = e.message; return null; } finally { this.busy = false; }
            },
            async runPreview() { const json = await this.send(@js(route('admin.reports.cleanup.preview'))); if (json) this.preview = json; },
            async runExecute() {
                const json = await this.send(@js(route('admin.reports.cleanup.execute')));
                window.bootstrap.Modal.getOrCreateInstance(document.getElementById('cleanup-confirm')).hide();
                if (json) { window.sgToast(json.message); setTimeout(() => location.reload(), 1200); }
            },
         }">

        <x-sg.page-header title="Pembersihan Laporan" subtitle="Hapus laporan lama untuk menghemat penyimpanan. Selalu lihat pratinjau sebelum menjalankan." />

        <div class="sg-kpi-grid mb-3">
            <x-sg.kpi label="Total laporan" :value="Format::number($stats['total_reports'])" />
            <x-sg.kpi label="Lebih dari 1 tahun" :value="Format::number($stats['older_than_365_days'])" />
            <x-sg.kpi label="Dengan lampiran" :value="Format::number($stats['with_attachments'])" />
            <x-sg.kpi label="Penyimpanan terpakai" :value="$stats['storage_size']" />
        </div>

        <div class="row g-3 g-lg-4">
            <div class="col-lg-6">
                <section class="card p-3 p-lg-4" aria-labelledby="cl-config">
                    <h2 id="cl-config" class="h6 fw-bold mb-3">Kriteria</h2>
                    <div class="mb-3">
                        <label for="cl-days" class="form-label fw-semibold">Hapus laporan yang lebih lama dari</label>
                        <div class="input-group" style="max-width:240px">
                            <input type="number" id="cl-days" class="form-control" min="1" max="3650" x-model.number="days" @input="preview = null">
                            <span class="input-group-text">hari</span>
                        </div>
                    </div>
                    <fieldset class="mb-3">
                        <legend class="form-label fw-semibold fs-6">Status laporan</legend>
                        <div class="sg-chip-row">
                            <template x-for="(label, key) in labels" :key="key">
                                <label class="sg-chip" :class="{ active: statuses.includes(key) }">
                                    <input type="checkbox" class="visually-hidden" :value="key" x-model="statuses" @change="preview = null"><span x-text="label"></span>
                                </label>
                            </template>
                        </div>
                        <div class="form-text">Kosongkan untuk semua status.</div>
                    </fieldset>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="cl-keep" x-model="keep" @change="preview = null">
                        <label class="form-check-label" for="cl-keep">Simpan file lampiran (hanya data laporan yang dihapus)</label>
                    </div>
                    <button type="button" class="btn btn-primary w-100" @click="runPreview()" :disabled="busy || !days">
                        <span x-text="busy ? 'Menghitung…' : 'Lihat pratinjau'">Lihat pratinjau</span>
                    </button>
                    <div class="alert alert-danger small mt-3 mb-0" x-show="error" x-text="error" role="alert"></div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="card p-3 p-lg-4" aria-labelledby="cl-preview" aria-live="polite">
                    <h2 id="cl-preview" class="h6 fw-bold mb-3">Pratinjau</h2>
                    <template x-if="!preview">
                        <x-sg.empty icon="bi-search" title="Belum ada pratinjau" text="Atur kriteria lalu tekan Lihat pratinjau." />
                    </template>
                    <template x-if="preview">
                        <div>
                            <p class="small text-muted mb-2" x-text="'Laporan dibuat sebelum ' + preview.cutoff_date"></p>
                            <dl class="sg-meta-grid mb-3">
                                <div><dt>Akan dihapus</dt><dd class="fs-4 fw-bold" x-text="preview.breakdown.total.toLocaleString('id-ID') + ' laporan'"></dd></div>
                                <div><dt>Dengan lampiran</dt><dd x-text="preview.breakdown.with_attachments.toLocaleString('id-ID')"></dd></div>
                                <div><dt>Ukuran lampiran</dt><dd x-text="preview.breakdown.total_size"></dd></div>
                            </dl>
                            <h3 class="sg-section-label">Per status</h3>
                            <ul class="list-unstyled small mb-3">
                                <template x-for="[status, count] in Object.entries(preview.breakdown.by_status)" :key="status">
                                    <li class="d-flex justify-content-between border-bottom py-1"><span x-text="labels[status] || status"></span><strong class="sg-num" x-text="count"></strong></li>
                                </template>
                            </ul>
                            <button type="button" class="btn btn-reject w-100" :disabled="busy || !preview.breakdown.total"
                                    @click="typed = ''; window.bootstrap.Modal.getOrCreateInstance(document.getElementById('cleanup-confirm')).show()"
                                    x-text="preview.breakdown.total ? 'Hapus ' + preview.breakdown.total.toLocaleString('id-ID') + ' laporan' : 'Tidak ada yang dihapus'"></button>
                        </div>
                    </template>
                </section>
            </div>
        </div>

        <x-sg.sheet id="cleanup-confirm" title="Jalankan pembersihan?">
            <p>Laporan yang sesuai kriteria dihapus permanen<span x-show="!keep"> beserta lampirannya</span>. Tindakan ini tidak bisa dibatalkan.</p>
            <label for="cl-typed" class="form-label fw-semibold">Ketik <code class="text-danger">HAPUS</code> untuk melanjutkan</label>
            <input id="cl-typed" class="form-control" x-model="typed" autocomplete="off" autocapitalize="off" spellcheck="false">
            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-reject flex-fill" :disabled="typed !== 'HAPUS' || busy" @click="runExecute()" x-text="busy ? 'Menghapus…' : 'Hapus permanen'"></button>
            </div>
        </x-sg.sheet>
    </div>
</x-app-layout>
