{{-- Detail Laporan — mockup: DetailMobile, DetailReject, DetailViewer, DetailDesktop --}}
@use('App\Support\Format')
@php
    $viewer = auth()->user();
    $isPending = $report->approval_status === 'pending';
    $canApprove = $isPending && ($viewer->can('approve-reports') || $viewer->id === $report->job_pic);
    $isOwner = $viewer->id === $report->user_id && ! $report->approved_by;

    $attachments = collect([
        [$report->attachment_path, $report->attachment_original_name],
        [$report->attachment_path_2, $report->attachment_original_name_2],
        [$report->attachment_path_3, $report->attachment_original_name_3],
    ])->filter(fn ($a) => $a[0])->map(function ($a) {
        $name = $a[1] ?: basename($a[0]);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return [
            'name' => $name,
            'url' => route('attachments.show', basename($a[0])),
            'image' => in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']),
            'ext' => strtoupper($ext),
        ];
    })->values();

    $decider = $isPending ? $report->pic : ($report->approver ?? $report->pic);
    $timeline = [[
        'title' => 'Laporan dikirim',
        'meta' => ($report->user?->name ?? '-').' · '.Format::datetime($report->created_at),
        'tone' => 'approved', 'icon' => 'bi-send',
    ]];
    $timeline[] = match ($report->approval_status) {
        'approved' => ['title' => 'Disetujui oleh '.($decider?->name ?? '-'), 'meta' => Format::datetime($report->updated_at), 'tone' => 'approved', 'icon' => 'bi-check-lg'],
        'rejected' => ['title' => 'Ditolak oleh '.($decider?->name ?? '-'), 'meta' => Format::datetime($report->updated_at), 'tone' => 'rejected', 'icon' => 'bi-x-lg', 'note' => $report->rejection_reason],
        default => ['title' => 'Menunggu keputusan '.($decider?->name ?? 'PIC'), 'meta' => $decider ? ($decider->isAdmin() ? 'Admin' : 'Level '.$decider->getRoleLevel()) : null, 'tone' => 'pending', 'icon' => 'bi-hourglass-split'],
    };

    $shareText = "Laporan: {$report->job_name}\n".route('daily-reports.show', $report);
@endphp

<x-app-layout title="Detail Laporan" :back="url()->previous() !== url()->current() ? url()->previous() : route('daily-reports.index')">
    <div class="row g-3 g-lg-4">
        <div class="col-lg-8">
            {{-- Ringkasan --}}
            <section class="card p-3 p-lg-4 mb-3" aria-labelledby="report-title">
                <div class="small text-muted mb-1">#{{ $report->id }} · Dikirim {{ Format::datetime($report->created_at) }}</div>
                <h1 id="report-title" class="h4 fw-bold mb-3">{{ $report->job_name }}</h1>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div @class(['sg-status-card', 'sg-status-card--'.config('sigap-ui.report_approval.'.$report->approval_status.'.tone', 'closed')])>
                            <div class="sg-status-card__label">Status Approval</div>
                            <x-sg.badge-status :value="$report->approval_status" />
                            @if ($isPending)
                                <div class="small text-muted mt-1">sejak {{ $report->created_at->diffForHumans() }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sg-status-card">
                            <div class="sg-status-card__label">Progres Pekerjaan</div>
                            <x-sg.badge-progress :value="$report->status" />
                            @if ($report->due_date)
                                @php($late = $report->due_date->isPast() && $report->status !== 'completed')
                                <div @class(['small mt-1', 'text-danger fw-semibold' => $late, 'text-muted' => ! $late])>
                                    Due {{ Format::dayMonth($report->due_date) }}{{ $late ? ' · terlambat' : '' }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="sg-people">
                    <div class="sg-person">
                        <x-sg.avatar :user="$report->user" :size="40" />
                        <div class="min-w-0">
                            <div class="small text-muted">Pembuat</div>
                            <div class="fw-semibold d-flex align-items-center gap-2"><span class="sg-clamp-1">{{ $report->user?->name ?? '-' }}</span>
                                @if ($report->user)<x-sg.badge-level :user="$report->user" short />@endif
                            </div>
                            <div class="small text-muted sg-clamp-1">{{ collect([$report->section?->name, $report->jobSite?->name])->filter()->implode(' · ') ?: $report->department?->name }}</div>
                        </div>
                    </div>
                    @if ($decider)
                        <div class="sg-person">
                            <x-sg.avatar :user="$decider" :size="40" />
                            <div class="min-w-0">
                                <div class="small text-muted">{{ $isPending ? 'Approver (PIC)' : 'Diputuskan oleh' }}</div>
                                <div class="fw-semibold d-flex align-items-center gap-2"><span class="sg-clamp-1">{{ $decider->name }}</span><x-sg.badge-level :user="$decider" short /></div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i> Bagikan via WhatsApp
                    </a>
                    @if ($isOwner)
                        <a href="{{ route('daily-reports.edit', $report) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</a>
                        <button type="button" class="btn btn-reject-outline" data-bs-toggle="modal" data-bs-target="#delete-report"><i class="bi bi-trash3" aria-hidden="true"></i> Hapus</button>
                    @endif
                    @if ($canApprove)
                        <div class="d-none d-lg-flex gap-2 ms-auto">
                            <button type="button" class="btn btn-reject-outline" data-bs-toggle="modal" data-bs-target="#reject-report"><i class="bi bi-x-lg" aria-hidden="true"></i> Tolak</button>
                            <button type="submit" form="approve-report" class="btn btn-approve"><i class="bi bi-check-lg" aria-hidden="true"></i> Setujui</button>
                        </div>
                    @endif
                </div>
            </section>

            @if ($report->approval_status === 'rejected' && $report->rejection_reason)
                <div class="alert alert-danger d-flex gap-2 mb-3" role="note">
                    <i class="bi bi-x-octagon fs-5" aria-hidden="true"></i>
                    <div><strong>Alasan penolakan</strong><div class="sg-prewrap">{{ $report->rejection_reason }}</div></div>
                </div>
            @endif

            {{-- Lampiran --}}
            @if ($attachments->isNotEmpty())
                <section class="card p-3 p-lg-4 mb-3" aria-labelledby="att-title" x-data="{ current: null }">
                    <h2 id="att-title" class="h6 fw-bold mb-1">Lampiran ({{ $attachments->count() }})</h2>
                    <p class="small text-muted mb-2">Ketuk foto untuk memperbesar.</p>
                    <div class="sg-uploader__grid">
                        @foreach ($attachments as $i => $file)
                            @if ($file['image'])
                                <button type="button" class="sg-uploader__item p-0" @click="current = {{ $i }}" data-bs-toggle="modal" data-bs-target="#viewer"
                                        aria-label="Perbesar {{ $file['name'] }}">
                                    <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy">
                                </button>
                            @else
                                <a href="{{ $file['url'] }}" target="_blank" rel="noopener" class="sg-uploader__item text-decoration-none">
                                    <span class="sg-uploader__file"><i class="bi bi-file-earmark-text" aria-hidden="true"></i>{{ $file['ext'] }}<span class="sg-clamp-1 w-100">{{ $file['name'] }}</span></span>
                                </a>
                            @endif
                        @endforeach
                    </div>

                    {{-- Penampil foto layar penuh --}}
                    <div class="modal fade" id="viewer" tabindex="-1" aria-label="Penampil lampiran" aria-hidden="true">
                        <div class="modal-dialog modal-fullscreen">
                            <div class="modal-content bg-black text-white">
                                <div class="d-flex align-items-center gap-2 p-2">
                                    <span class="flex-grow-1 small sg-clamp-1 ps-2" x-text="current !== null ? @js($attachments->pluck('name')).at(current) : ''"></span>
                                    <a class="sg-icon-btn text-white" :href="current !== null ? @js($attachments->pluck('url')).at(current) : '#'" target="_blank" rel="noopener" aria-label="Buka file asli"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
                                    <button type="button" class="sg-icon-btn text-white" data-bs-dismiss="modal" aria-label="Tutup"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                                </div>
                                <div class="flex-grow-1 d-flex align-items-center justify-content-center p-2">
                                    <img :src="current !== null ? @js($attachments->pluck('url')).at(current) : ''" alt="" class="img-fluid" style="max-height:85vh">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Tab: Deskripsi → Remarks → Komentar --}}
            <section class="card mb-3" x-data="sgComments({ reportId: {{ $report->id }} })" x-init="load()">
                <x-sg.tabs prefix="rpt" :items="['desc' => 'Deskripsi', 'remarks' => 'Remarks', 'comments' => 'Komentar']" />
                <div class="tab-content p-3 p-lg-4">
                    <div class="tab-pane fade show active" id="rpt-desc" role="tabpanel" aria-labelledby="rpt-desc-tab">
                        <p class="sg-prewrap mb-3">{{ trim($report->description) ?: 'Tidak ada deskripsi.' }}</p>
                        <dl class="sg-meta-grid mb-0">
                            <div><dt>Tanggal</dt><dd>{{ Format::date($report->report_date) }}</dd></div>
                            <div><dt>Due date</dt><dd>{{ Format::date($report->due_date) }}</dd></div>
                            <div><dt>Section</dt><dd>{{ $report->section?->name ?? '-' }}</dd></div>
                            <div><dt>Job site</dt><dd>{{ $report->jobSite?->name ?? '-' }}</dd></div>
                            <div><dt>Departemen</dt><dd>{{ $report->department?->name ?? '-' }}</dd></div>
                            <div><dt>Dikirim</dt><dd>{{ Format::datetime($report->created_at) }}</dd></div>
                        </dl>
                    </div>
                    <div class="tab-pane fade" id="rpt-remarks" role="tabpanel" aria-labelledby="rpt-remarks-tab">
                        @if (trim((string) $report->remark))
                            <p class="sg-prewrap mb-0">{{ $report->remark }}</p>
                        @else
                            <x-sg.empty icon="bi-chat-square-text" title="Belum ada remark" class="py-3" />
                        @endif
                    </div>
                    <div class="tab-pane fade" id="rpt-comments" role="tabpanel" aria-labelledby="rpt-comments-tab">
                        <form class="sg-comment-form mb-3" @submit.prevent="send()">
                            <label for="comment-text" class="visually-hidden">Tulis komentar</label>
                            <textarea id="comment-text" class="form-control" rows="2" maxlength="1000" placeholder="Tulis komentar…" x-model="text"
                                      @keydown.ctrl.enter="send()"></textarea>
                            <button type="submit" class="btn btn-primary btn-icon flex-none" :disabled="!text.trim() || sending" aria-label="Kirim komentar">
                                <i class="bi bi-send" aria-hidden="true"></i>
                            </button>
                        </form>
                        <template x-if="!loaded"><x-sg.skeleton :count="2" /></template>
                        <template x-if="loaded && failed">
                            <div class="text-center py-3"><p class="text-muted">Komentar gagal dimuat.</p><button type="button" class="btn btn-outline-secondary" @click="load()">Coba lagi</button></div>
                        </template>
                        <template x-if="loaded && !failed && !items.length">
                            <x-sg.empty icon="bi-chat-left-text" title="Belum ada komentar" text="Mulai diskusi tentang laporan ini." class="py-3" />
                        </template>
                        <template x-for="c in items" :key="c.id">
                            <article class="sg-comment" :id="'comment-' + c.id">
                                <span class="sg-avatar" style="width:36px;height:36px;font-size:13px">
                                    <template x-if="c.user.profile_picture"><img :src="c.user.profile_picture" alt=""></template>
                                    <template x-if="!c.user.profile_picture"><span x-text="initials(c.user.name)"></span></template>
                                </span>
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <strong class="sg-clamp-1" x-text="c.user.name"></strong>
                                        <span class="small text-muted flex-none" x-text="c.created_at"></span>
                                        <button type="button" class="sg-icon-btn ms-auto text-danger" style="width:32px;height:32px;font-size:15px" x-show="c.is_owner"
                                                @click="destroy(c)" aria-label="Hapus komentar"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                    </div>
                                    <p class="sg-comment__body sg-prewrap mb-0" x-text="c.comment"></p>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>
            </section>
        </div>

        <aside class="col-lg-4">
            <section class="card p-3 p-lg-4 sg-create-aside" aria-labelledby="tl-title">
                <h2 id="tl-title" class="h6 fw-bold mb-3">Timeline persetujuan</h2>
                <x-sg.timeline :items="$timeline" />
            </section>
        </aside>
    </div>

    {{-- Aksi approver (mobile: sticky bar) --}}
    @if ($canApprove)
        <form id="approve-report" action="{{ route('daily-reports.approval', $report) }}" method="POST" class="d-none">
            @csrf
            <input type="hidden" name="status" value="approved">
            <input type="hidden" name="redirect_back" value="1">
        </form>

        <x-sg.sticky-actions>
            <button type="button" class="btn btn-reject-outline flex-fill" data-bs-toggle="modal" data-bs-target="#reject-report"><i class="bi bi-x-lg" aria-hidden="true"></i> Tolak</button>
            <button type="submit" form="approve-report" class="btn btn-approve" style="flex:2"><i class="bi bi-check-lg" aria-hidden="true"></i> Setujui</button>
        </x-sg.sticky-actions>

        <x-sg.sheet id="reject-report" title="Tolak laporan?">
            <form action="{{ route('daily-reports.approval', $report) }}" method="POST" id="reject-form"
                  x-data="{ reason: @js(old('rejection_reason', '')), add(t) { this.reason = this.reason ? this.reason.replace(/\s*$/, '') + ' ' + t + ':' : t + ': '; $refs.reason.focus(); } }">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <input type="hidden" name="redirect_back" value="1">
                <p class="small text-muted">{{ $report->job_name }} · {{ $report->user?->name }}</p>
                <p class="sg-section-label mb-2">Alasan cepat</p>
                <div class="sg-chip-row mb-3">
                    @foreach (['Lampiran kurang', 'Deskripsi tidak jelas', 'Salah PIC/section', 'Duplikat'] as $quick)
                        <button type="button" class="sg-chip" @click="add(@js($quick))">{{ $quick }}</button>
                    @endforeach
                </div>
                <label for="rejection_reason" class="form-label fw-semibold">Alasan penolakan <span class="text-danger">*</span></label>
                <textarea id="rejection_reason" name="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror" rows="4" maxlength="500" required
                          x-ref="reason" x-model="reason" aria-describedby="reason-help"></textarea>
                <div class="d-flex justify-content-between small mt-1">
                    <span id="reason-help" class="text-muted">Wajib diisi · dikirim ke pembuat laporan</span>
                    <span class="text-muted sg-num" x-text="reason.length + ' / 500'"></span>
                </div>
                @error('rejection_reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-reject flex-fill" :disabled="!reason.trim()">Tolak Laporan</button>
                </div>
            </form>
        </x-sg.sheet>

        @error('rejection_reason')
            @push('scripts')
                <script>bootstrap.Modal.getOrCreateInstance(document.getElementById('reject-report')).show();</script>
            @endpush
        @enderror
    @endif

    @if ($isOwner)
        <x-sg.confirm-danger id="delete-report" title="Hapus laporan ini?" :action="route('daily-reports.destroy', $report)"
                             text="Laporan dan lampirannya akan dihapus permanen." button="Hapus laporan" />
    @endif
</x-app-layout>
