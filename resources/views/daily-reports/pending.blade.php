{{-- Antrian Persetujuan — mockup: QueueMobile, QueueMulti, QueueDesktop --}}
@use('App\Support\Format')
@php
    $user = auth()->user();
    $monitoring = isset($monitoringReports);
    $canMonitor = $user->isAdmin() || $user->getRoleLevel() >= 4;
    $canBatchDelete = $user->isAdmin() || $user->getRoleLevel() >= 4;
    $page = $monitoring ? $monitoringReports : $reports;

    $items = $monitoring ? collect() : $reports->getCollection()->map(fn ($r) => [
        'id' => $r->id,
        'title' => $r->job_name,
        'owner' => $r->user?->name ?? '-',
        'initials' => collect(explode(' ', $r->user?->name ?? '?'))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode(''),
        'level' => $r->user?->getRoleLevel() ?? 0,
        'meta' => collect([$r->department?->name, $r->pic ? 'PIC '.$r->pic->name : null])->filter()->implode(' · '),
        'age' => $r->created_at->diffForHumans(),
        'hours' => (int) $r->created_at->diffInHours(now()),
        'sent' => Format::datetime($r->created_at),
        'date' => Format::date($r->report_date),
        'due' => Format::date($r->due_date),
        'progress' => config('sigap-ui.report_progress.'.$r->status.'.label', $r->status),
        'description' => (string) $r->description,
        'remark' => (string) $r->remark,
        'attachments' => collect([$r->attachment_path, $r->attachment_path_2, $r->attachment_path_3])->filter()->count(),
        'url' => route('daily-reports.show', $r),
    ])->values();
    $levels = $items->pluck('level')->unique()->sort()->values();
    $oldCount = $items->where('hours', '>=', 24)->count();
@endphp

<x-app-layout title="Persetujuan">
    <x-sg.page-header title="Persetujuan" :subtitle="$monitoring ? 'Semua laporan yang masih menunggu di job site Anda.' : Format::number($page->total()).' menunggu'.($oldCount ? ' · '.$oldCount.' lebih dari 24 jam' : '')" />

    @if ($canMonitor)
        <x-sg.chip-filter class="mb-3" label="Tampilan" :items="[
            ['label' => 'Perlu persetujuan saya', 'href' => route('daily-reports.pending'), 'active' => ! $monitoring],
            ['label' => 'Monitoring', 'href' => route('daily-reports.pending', ['view' => 'monitoring']), 'active' => $monitoring],
        ]" />
    @endif

    @if ($monitoring)
        <x-report.list :reports="$monitoringReports->getCollection()" show-owner caption="Laporan menunggu persetujuan"
                       empty-title="Tidak ada laporan tertunda" empty-text="Semua laporan di job site Anda sudah diproses." />
        <div class="mt-3">{{ $monitoringReports->appends(['view' => 'monitoring'])->links() }}</div>
    @elseif ($items->isEmpty())
        <x-sg.empty icon="bi-check2-all" title="Antrian kosong" text="Semua laporan tim sudah ditinjau. Kerja bagus!">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Kembali ke Beranda</a>
        </x-sg.empty>
    @else
        <div x-data="sgQueue(@js(['reports' => $items, 'approvalUrl' => route('daily-reports.approval', '__ID__')]))" @keydown.window="key($event)" @pagehide.window="flush()">
            {{-- Form satu laporan (action diisi JS) --}}
            <form x-ref="single" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="status" value="approved">
                <input type="hidden" name="redirect_back" value="1">
            </form>

            {{-- Saringan + mode pilih banyak --}}
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <div class="sg-chip-row flex-grow-1" role="group" aria-label="Saring antrian">
                    <button type="button" class="sg-chip" :class="{ active: filter === 'all' }" @click="filter = 'all'">Semua <span class="sg-chip__count">{{ $items->count() }}</span></button>
                    @if ($oldCount)
                        <button type="button" class="sg-chip" :class="{ active: filter === 'old' }" @click="filter = 'old'">&gt; 24 jam <span class="sg-chip__count">{{ $oldCount }}</span></button>
                    @endif
                    @foreach ($levels as $lv)
                        <button type="button" class="sg-chip" :class="{ active: filter === 'L{{ $lv }}' }" @click="filter = 'L{{ $lv }}'">Level {{ $lv }}</button>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline-secondary d-lg-none" @click="selecting ? stopSelecting() : selecting = true"
                        x-text="selecting ? 'Selesai' : 'Pilih'">Pilih</button>
            </div>

            <p class="small text-muted d-lg-none" x-show="!selecting">Geser kanan untuk setujui, kiri untuk tolak. Tekan lama untuk memilih banyak.</p>
            <p class="small text-muted d-none d-lg-block">Pintasan: <kbd>J</kbd>/<kbd>K</kbd> pindah · <kbd>X</kbd> pilih · <kbd>A</kbd> setujui · <kbd>R</kbd> tolak</p>

            <div class="row g-3 g-lg-4">
                {{-- Daftar --}}
                <div class="col-lg-5">
                    <div class="sg-bulk-bar border d-none d-lg-flex" style="border-radius:12px 12px 0 0">
                        <input type="checkbox" class="form-check-input m-0" :checked="allSelected" @change="toggleAll()" aria-label="Pilih semua">
                        <span x-text="selected.length ? selected.length + ' dipilih' : 'Pilih semua'"></span>
                    </div>
                    <div class="sg-queue-list">
                        <template x-for="r in visible" :key="r.id">
                            <div class="sg-swipe" :class="swipeHint(r.id)">
                            <span class="sg-swipe__bg sg-swipe__bg--approve d-lg-none" aria-hidden="true"><i class="bi bi-check-lg"></i> Setujui</span>
                            <span class="sg-swipe__bg sg-swipe__bg--reject d-lg-none" aria-hidden="true">Tolak <i class="bi bi-x-lg"></i></span>
                            <article class="sg-queue-card" :class="{ 'is-active': active === r.id, 'is-selected': isSelected(r.id) }" :style="cardStyle(r.id)"
                                     @pointerdown="pointerDown($event, r)" @pointermove="pointerMove($event)" @pointerup="pointerUp()" @pointercancel="pointerUp()"
                                     @contextmenu="selecting && $event.preventDefault()">
                                <div class="d-flex gap-2 align-items-start">
                                    <input type="checkbox" class="form-check-input mt-2 flex-none" :class="selecting ? '' : 'd-none d-lg-block'"
                                           :checked="isSelected(r.id)" @change="toggle(r.id)" :aria-label="'Pilih ' + r.title">
                                    <span class="sg-avatar flex-none" style="width:40px;height:40px;font-size:14px" x-text="r.initials"></span>
                                    <button type="button" class="sg-queue-card__main" @click="open(r)">
                                        <span class="d-flex align-items-center gap-2">
                                            <strong class="sg-clamp-1" x-text="r.owner"></strong>
                                            <span class="sg-level" :class="'sg-level--' + r.level" x-text="'L' + r.level"></span>
                                            <span class="small ms-auto flex-none" :class="r.hours >= 24 ? 'text-danger fw-semibold' : 'text-muted'" x-text="r.age"></span>
                                        </span>
                                        <span class="d-block fw-semibold mt-1 sg-clamp-2" x-text="r.title"></span>
                                        <span class="d-block small text-muted sg-clamp-1" x-text="r.meta"></span>
                                    </button>
                                </div>
                                <div class="sg-queue-card__actions d-lg-none" x-show="!selecting">
                                    <a :href="r.url" class="btn btn-outline-secondary btn-icon flex-none" :aria-label="'Detail ' + r.title"><i class="bi bi-eye" aria-hidden="true"></i></a>
                                    <button type="button" class="btn btn-reject-outline flex-fill" @click="askReject([r.id])">Tolak</button>
                                    <button type="button" class="btn btn-approve flex-fill" @click="approve(r.id)">Setujui</button>
                                </div>
                            </article>
                            </div>
                        </template>
                        <p class="text-muted small p-3 mb-0" x-show="!visible.length">Tidak ada laporan untuk saringan ini.</p>
                    </div>
                    <div class="mt-3">{{ $reports->links() }}</div>
                </div>

                {{-- Pratinjau (desktop) --}}
                <div class="col-lg-7 d-none d-lg-block">
                    <template x-if="current">
                        <section class="card p-4 sg-create-aside" aria-live="polite">
                            <div class="small text-muted" x-text="'#' + current.id + ' · Dikirim ' + current.sent"></div>
                            <h2 class="h4 fw-bold my-2" x-text="current.title"></h2>
                            <div class="d-flex gap-2 mb-3">
                                <button type="button" class="btn btn-reject-outline" @click="askReject([current.id])"><i class="bi bi-x-lg" aria-hidden="true"></i> Tolak <kbd class="ms-1">R</kbd></button>
                                <button type="button" class="btn btn-approve" @click="approve(current.id)"><i class="bi bi-check-lg" aria-hidden="true"></i> Setujui <kbd class="ms-1">A</kbd></button>
                                <a :href="current.url" class="btn btn-outline-secondary ms-auto">Buka detail</a>
                            </div>
                            <dl class="sg-meta-grid mb-3">
                                <div><dt>Pembuat</dt><dd x-text="current.owner"></dd></div>
                                <div><dt>Progres</dt><dd x-text="current.progress"></dd></div>
                                <div><dt>Menunggu</dt><dd x-text="current.age"></dd></div>
                                <div><dt>Tanggal</dt><dd x-text="current.date"></dd></div>
                                <div><dt>Due date</dt><dd x-text="current.due"></dd></div>
                                <div><dt>Lampiran</dt><dd x-text="current.attachments + ' berkas'"></dd></div>
                            </dl>
                            <h3 class="sg-section-label">Deskripsi</h3>
                            <p class="sg-prewrap" x-text="current.description || '-'"></p>
                            <template x-if="current.remark">
                                <div><h3 class="sg-section-label">Remarks</h3><p class="sg-prewrap mb-0" x-text="current.remark"></p></div>
                            </template>
                        </section>
                    </template>
                </div>
            </div>

            {{-- Bar aksi massal --}}
            <div class="sg-batch-bar" x-show="selected.length" x-cloak>
                <form method="POST" action="{{ route('daily-reports.batch-approve') }}" class="d-flex gap-2 align-items-center w-100">
                    @csrf
                    <template x-for="id in selected" :key="id"><input type="hidden" name="selected_reports[]" :value="id"></template>
                    <span class="fw-semibold me-auto" x-text="selected.length + ' dipilih'"></span>
                    @if ($canBatchDelete)
                        <button type="button" class="btn btn-outline-secondary btn-icon" data-bs-toggle="modal" data-bs-target="#queue-delete" aria-label="Hapus yang dipilih">
                            <i class="bi bi-trash3" aria-hidden="true"></i>
                        </button>
                    @endif
                    <button type="button" class="btn btn-reject-outline" @click="askReject([...selected])" x-text="'Tolak (' + selected.length + ')'"></button>
                    <button type="submit" class="btn btn-approve" x-text="'Setujui (' + selected.length + ')'"></button>
                </form>
            </div>

            {{-- Tolak: satu laporan → route approval, banyak → batch-reject --}}
            <x-sg.sheet id="queue-reject" title="Tolak laporan?">
                <form method="POST" x-data="{ reason: '' }"
                      :action="rejectIds.length === 1 ? @js(route('daily-reports.approval', '__ID__')).replace('__ID__', rejectIds[0]) : @js(route('daily-reports.batch-reject'))">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <input type="hidden" name="redirect_back" value="1">
                    <template x-for="id in rejectIds" :key="id"><input type="hidden" name="selected_reports[]" :value="id"></template>
                    <p class="small text-muted" x-text="rejectIds.length === 1 ? (reports.find(r => r.id === rejectIds[0]) || {}).title : rejectIds.length + ' laporan dipilih'"></p>
                    <div class="sg-chip-row mb-3">
                        @foreach (['Lampiran kurang', 'Deskripsi tidak jelas', 'Salah PIC/section', 'Duplikat'] as $quick)
                            <button type="button" class="sg-chip" @click="reason = reason ? reason.trimEnd() + ' {{ $quick }}:' : '{{ $quick }}: '">{{ $quick }}</button>
                        @endforeach
                    </div>
                    <label for="queue-reason" class="form-label fw-semibold">Alasan penolakan <span class="text-danger">*</span></label>
                    <textarea id="queue-reason" name="rejection_reason" class="form-control" rows="4" maxlength="500" required x-model="reason"></textarea>
                    <div class="d-flex justify-content-between small mt-1 text-muted"><span>Wajib diisi · dikirim ke pembuat laporan</span><span class="sg-num" x-text="reason.length + ' / 500'"></span></div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-reject flex-fill" :disabled="!reason.trim()">Tolak</button>
                    </div>
                </form>
            </x-sg.sheet>

            @if ($canBatchDelete)
                <x-sg.sheet id="queue-delete" title="Hapus laporan terpilih?">
                    <form method="POST" action="{{ route('daily-reports.batch-delete') }}" x-data="{ typed: '' }">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selected" :key="id"><input type="hidden" name="selected_reports[]" :value="id"></template>
                        <p>Laporan yang dipilih beserta lampirannya akan dihapus permanen.</p>
                        <label for="queue-del-typed" class="form-label fw-semibold">Ketik <code class="text-danger">HAPUS</code> untuk melanjutkan</label>
                        <input id="queue-del-typed" class="form-control" x-model="typed" autocomplete="off">
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-reject flex-fill" :disabled="typed !== 'HAPUS'" x-text="'Hapus ' + selected.length + ' laporan'"></button>
                        </div>
                    </form>
                </x-sg.sheet>
            @endif
        </div>
    @endif
</x-app-layout>
