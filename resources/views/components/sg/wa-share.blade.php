{{--
    Sheet ringkasan WhatsApp: teks dibuat server (JSON { text, count, limited }) dari filter URL yang sedang aktif.
    :url    endpoint (daily-reports.whatsapp-share / job-plans.whatsapp-share)
    :extra  parameter tambahan, mis. ['type' => 'approved']
    Buka dengan data-bs-target="#{id}".
--}}
@props(['id' => 'wa-share', 'url', 'extra' => [], 'unit' => 'laporan'])
<x-sg.sheet :id="$id" title="Ringkasan WhatsApp" size="lg">
    <div x-data="{
            format: 'detail', text: '', count: 0, limited: false, loading: false, failed: false,
            async load() {
                this.loading = true; this.failed = false;
                const params = new URLSearchParams(window.location.search);
                Object.entries(@js($extra)).forEach(([k, v]) => params.set(k, v));
                const today = new Date().toISOString().slice(0, 10);
                if (!params.get('date_from')) params.set('date_from', today);
                if (!params.get('date_to')) params.set('date_to', today);
                params.set('format', this.format);
                try {
                    const r = await fetch(@js($url) + '?' + params, { headers: { Accept: 'application/json' } });
                    if (!r.ok) throw new Error(r.status);
                    const d = await r.json();
                    this.text = d.text || ''; this.count = d.count || 0; this.limited = !!d.limited;
                } catch (e) { this.failed = true; } finally { this.loading = false; }
            },
            async copy() { await navigator.clipboard.writeText(this.text); sgToast('Teks disalin'); }
         }" x-init="$el.closest('.modal').addEventListener('shown.bs.modal', () => load())">
        <div class="btn-group mb-3" role="group" aria-label="Format pesan">
            <input type="radio" class="btn-check" id="{{ $id }}-detail" value="detail" x-model="format" @change="load()">
            <label class="btn btn-outline-secondary" for="{{ $id }}-detail">Detail</label>
            <input type="radio" class="btn-check" id="{{ $id }}-ringkas" value="ringkasan" x-model="format" @change="load()">
            <label class="btn btn-outline-secondary" for="{{ $id }}-ringkas">Ringkasan</label>
        </div>
        <p class="small text-muted">Mengikuti filter yang sedang aktif. Tanpa rentang tanggal, dipakai hari ini.</p>
        <div class="alert alert-warning py-2 small" x-show="limited" x-cloak>Menampilkan 30 dari <span x-text="count"></span> {{ $unit }}. Pakai format Ringkasan atau filter lebih spesifik.</div>
        <template x-if="loading"><x-sg.skeleton :count="1" /></template>
        <p class="text-danger small" x-show="failed" x-cloak>Gagal menyiapkan pesan. Coba lagi.</p>
        <label for="{{ $id }}-text" class="visually-hidden">Pratinjau pesan</label>
        <textarea id="{{ $id }}-text" class="form-control font-monospace small" rows="10" readonly x-show="!loading" x-model="text"></textarea>
        <div class="d-flex gap-2 mt-3">
            <button type="button" class="btn btn-outline-secondary flex-fill" @click="copy()" :disabled="!text"><i class="bi bi-clipboard" aria-hidden="true"></i> Salin</button>
            <a class="btn btn-approve flex-fill" target="_blank" rel="noopener" :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent(text)" :class="{ disabled: !text }">
                <i class="bi bi-whatsapp" aria-hidden="true"></i> Buka WhatsApp
            </a>
        </div>
    </div>
</x-sg.sheet>
