{{-- Laporan Harian (semua) — mockup: ListMobile, ListTablet, ListDesktop --}}
@php
    $user = auth()->user();
    $canShare = $user->isAdmin() || $user->getRoleLevel() >= 3;
    $canBatchDelete = $user->isAdmin() || $user->isLevel5();

    // Pilihan section (aturan sama dengan view lama; admin melihat semua section aktif)
    if ($user->isLevel8() && $user->job_site_id) {
        $deptIds = \App\Models\User::where('job_site_id', $user->job_site_id)->whereNotNull('department_id')->distinct()->pluck('department_id');
        $sections = \App\Models\Section::whereIn('department_id', $deptIds)->where('is_active', true)->orderBy('name')->get();
    } elseif ($user->isAdmin() || $user->isLevel8()) {
        $sections = \App\Models\Section::where('is_active', true)->orderBy('name')->get();
    } elseif ($user->department_id) {
        $sections = \App\Models\Section::where('department_id', $user->department_id)->where('is_active', true)->orderBy('name')->get();
    } else {
        $sections = collect();
    }
@endphp

<x-app-layout title="Laporan Harian">
    <x-sg.page-header title="Laporan Harian" :subtitle="\App\Support\Format::number($reports->total()).' laporan '.($reportType === 'rejected' ? 'ditolak' : 'disetujui')">
        <x-slot:actions>
            <a href="{{ route('daily-reports.export-all') }}?{{ http_build_query(request()->query()) }}" class="btn btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Ekspor</a>
            @if ($canShare)
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#wa-share"><i class="bi bi-whatsapp" aria-hidden="true"></i> Ringkasan WA</button>
            @endif
            @unless ($user->isAdmin() || $user->isLevel8())
                <a href="{{ route('daily-reports.create') }}" class="btn btn-primary d-none d-lg-inline-flex"><i class="bi bi-plus-lg" aria-hidden="true"></i> Buat Laporan</a>
            @endunless
        </x-slot:actions>
    </x-sg.page-header>

    <x-sg.chip-filter class="mb-3" label="Jenis laporan" :items="[
        ['label' => 'Disetujui', 'href' => route('daily-reports.index', ['type' => 'approved']), 'active' => $reportType !== 'rejected'],
        ['label' => 'Ditolak', 'href' => route('daily-reports.index', ['type' => 'rejected']), 'active' => $reportType === 'rejected'],
    ]" />

    <x-report.filters :action="route('daily-reports.index')" :departments="$departments ?: null" :sections="$sections" :keep="['type' => $reportType]" />

    @if ($canBatchDelete)
        <form id="bulk-delete" method="POST" action="{{ route('daily-reports.batch-delete') }}" class="d-none d-lg-block"
              x-data="{ count: 0, typed: '' }" @change.window="count = document.querySelectorAll('[data-select-for=bulk-delete]:checked').length">
            @csrf
            @method('DELETE')
            <div class="sg-bulk-bar border mb-2" style="border-radius:12px" x-show="count" x-cloak>
                <span x-text="count + ' dipilih'"></span>
                <button type="button" class="btn btn-sm btn-reject-outline ms-auto" data-bs-toggle="modal" data-bs-target="#bulk-delete-confirm">
                    <i class="bi bi-trash3" aria-hidden="true"></i> Hapus
                </button>
            </div>
            <x-sg.sheet id="bulk-delete-confirm" title="Hapus laporan terpilih?">
                <p>Laporan yang dipilih beserta lampirannya akan dihapus permanen.</p>
                <label for="bulk-typed" class="form-label fw-semibold">Ketik <code class="text-danger">HAPUS</code> untuk melanjutkan</label>
                <input id="bulk-typed" class="form-control" x-model="typed" autocomplete="off">
                <x-slot:footer>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-reject" :disabled="typed !== 'HAPUS'">Hapus</button>
                </x-slot:footer>
            </x-sg.sheet>
        </form>
    @endif

    <x-report.list :reports="$reports->getCollection()" show-owner :selectable="$canBatchDelete ? 'bulk-delete' : null" caption="Daftar laporan harian"
                   :empty-title="$reportType === 'rejected' ? 'Tidak ada laporan ditolak' : 'Belum ada laporan disetujui'"
                   empty-text="Coba ubah filter atau rentang tanggal." />

    <div class="mt-3" data-sg-pager>{{ $reports->withQueryString()->links() }}</div>

    {{-- Ringkasan WhatsApp (L3+ / admin): teks dibuat server dari filter yang sedang aktif --}}
    @if ($canShare)
        <x-sg.sheet id="wa-share" title="Ringkasan WhatsApp" size="lg">
            <div x-data="{
                    format: 'detail', text: '', count: 0, limited: false, loading: false, failed: false,
                    async load() {
                        this.loading = true; this.failed = false;
                        const params = new URLSearchParams(window.location.search);
                        params.set('type', @js($reportType));
                        const today = new Date().toISOString().slice(0, 10);
                        if (!params.get('date_from')) params.set('date_from', today);
                        if (!params.get('date_to')) params.set('date_to', today);
                        params.set('format', this.format);
                        try {
                            const r = await fetch(@js(route('daily-reports.whatsapp-share')) + '?' + params, { headers: { Accept: 'application/json' } });
                            const d = await r.json();
                            this.text = d.text || ''; this.count = d.count || 0; this.limited = !!d.limited;
                        } catch (e) { this.failed = true; } finally { this.loading = false; }
                    },
                    async copy() { await navigator.clipboard.writeText(this.text); sgToast('Teks disalin'); }
                 }" x-init="$el.closest('.modal').addEventListener('shown.bs.modal', () => load())">
                <div class="btn-group mb-3" role="group" aria-label="Format pesan">
                    <input type="radio" class="btn-check" id="wa-detail" value="detail" x-model="format" @change="load()">
                    <label class="btn btn-outline-secondary" for="wa-detail">Detail</label>
                    <input type="radio" class="btn-check" id="wa-ringkas" value="ringkasan" x-model="format" @change="load()">
                    <label class="btn btn-outline-secondary" for="wa-ringkas">Ringkasan</label>
                </div>
                <p class="small text-muted">Mengikuti filter yang sedang aktif. Tanpa rentang tanggal, dipakai hari ini.</p>
                <div class="alert alert-warning py-2 small" x-show="limited" x-cloak>Menampilkan 30 dari <span x-text="count"></span> laporan. Pakai format Ringkasan atau filter lebih spesifik.</div>
                <template x-if="loading"><x-sg.skeleton :count="1" /></template>
                <p class="text-danger small" x-show="failed" x-cloak>Gagal menyiapkan pesan. Coba lagi.</p>
                <label for="wa-text" class="visually-hidden">Pratinjau pesan</label>
                <textarea id="wa-text" class="form-control font-monospace small" rows="10" readonly x-show="!loading" x-model="text"></textarea>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary flex-fill" @click="copy()" :disabled="!text"><i class="bi bi-clipboard" aria-hidden="true"></i> Salin</button>
                    <a class="btn btn-approve flex-fill" target="_blank" rel="noopener" :href="'https://api.whatsapp.com/send?text=' + encodeURIComponent(text)" :class="{ disabled: !text }">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i> Buka WhatsApp
                    </a>
                </div>
            </div>
        </x-sg.sheet>
    @endif
</x-app-layout>
