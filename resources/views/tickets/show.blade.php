{{-- Detail Tiket — mockup: TicketDetailMobile, TicketActionSheet, TicketDetailTablet, TicketDetailDesktop --}}
@php
    $isStaff = $canViewInternal;
    $canAssign = auth()->user()->can('assign', App\Models\Ticket::class) && \App\Services\TicketWorkflow::canBeAssigned($ticket);
    $hasActions = $canAssign || count($transitions) > 0;
    $backUrl = auth()->user()->can('viewAny', App\Models\Ticket::class) ? route('tickets.index') : route('tickets.mine');
@endphp

<x-app-layout title="Detail Tiket" :back="$backUrl">
    <div class="row g-3 g-lg-4">
        <div class="col-lg-8">
            {{-- Ringkasan --}}
            <section class="card p-3 p-lg-4 mb-3" aria-labelledby="ticket-title">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="fw-bold sg-num">{{ $ticket->ticket_number }}</span>
                    <x-sg.badge-status type="ticket_status" :value="$ticket->status" />
                    <x-sg.badge-priority :value="$ticket->priority" code />
                    @if ($ticket->reopen_count > 0)
                        <span class="sg-badge sg-badge--rejected" title="Jumlah dibuka kembali oleh pemohon">Dibuka ulang {{ $ticket->reopen_count }}×</span>
                    @endif
                    @if ($isStaff)<span class="ms-lg-auto">@include('tickets.partials.sla-badge')</span>@endif
                </div>
                <h1 id="ticket-title" class="h4 fw-bold mb-1">{{ $ticket->title }}</h1>
                <p class="small text-muted mb-3">
                    {{ $ticket->type_label }} · {{ $ticket->category->name ?? '-' }} · {{ \App\Support\Format::datetime($ticket->created_at) }}{{ $ticket->jobSite ? ' · '.$ticket->jobSite->name : '' }}
                </p>
                <p class="sg-prewrap p-3 rounded mb-3" style="background:var(--sg-surface-2)">{{ $ticket->description }}</p>

                @if ($ticket->resolution_note && $ticket->status !== App\Models\Ticket::STATUS_RESOLVED)
                    <div class="sg-status-card sg-status-card--approved mb-3">
                        <div class="sg-status-card__label">Catatan penyelesaian</div>
                        <div class="sg-prewrap">{{ $ticket->resolution_note }}</div>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    @if ($ticket->attachment_path)
                        <a href="{{ route('tickets.attachment', $ticket) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                            <i class="bi bi-paperclip" aria-hidden="true"></i> {{ $ticket->attachment_original_name ?? 'Lihat lampiran' }}
                        </a>
                    @endif
                    @if ($isStaff && $ticket->jobPlan)
                        <a href="{{ route('job-plans.show', $ticket->job_plan_id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-link-45deg" aria-hidden="true"></i> Job Plan · {{ $ticket->jobPlan->assignee->name ?? '-' }}
                            ({{ $ticket->jobPlan->status === 'converted' ? 'dikonversi' : 'aktif' }})
                        </a>
                    @endif
                    @can('delete', $ticket)
                        <button type="button" class="btn btn-reject-outline ms-auto" data-bs-toggle="modal" data-bs-target="#delete-ticket">
                            <i class="bi bi-trash3" aria-hidden="true"></i> Hapus
                        </button>
                    @endcan
                </div>
            </section>

            @if ($ticket->approval_status)
                @include('tickets.partials.approval-card', [
                    'printUrl' => route('tickets.approval.form', $ticket),
                    'proofUrl' => route('tickets.approval.proof', $ticket),
                    'uploadUrl' => auth()->user()->can('respond', $ticket) ? route('tickets.approval.upload', $ticket) : null,
                    'verifyUrl' => auth()->user()->can('verifyApproval', $ticket) ? route('tickets.approval.verify', $ticket) : null,
                ])
            @endif

            @can('respond', $ticket)
                @include('tickets.partials.requester-actions', ['action' => route('tickets.respond', $ticket), 'csatAction' => route('tickets.csat', $ticket)])
            @endcan

            {{-- Komentar --}}
            <section class="card p-3 p-lg-4 mb-3" aria-labelledby="comments-title">
                <h2 id="comments-title" class="h6 fw-bold mb-1">Komentar</h2>
                @if ($isStaff)
                    <p class="small text-muted">Balasan biasa terlihat oleh pemohon. Centang "Catatan internal" untuk tim IT saja.</p>
                @endif
                @include('tickets.partials.comments', ['comments' => $ticket->comments])

                @can('addComment', $ticket)
                    @if ($ticket->isActive())
                        <form action="{{ route('tickets.comment', $ticket) }}" method="POST" class="mt-3" x-data="{ internal: false }">
                            @csrf
                            <label for="ticket-comment" class="visually-hidden">Tulis komentar</label>
                            <textarea id="ticket-comment" name="comment" rows="3" required class="form-control @error('comment') is-invalid @enderror"
                                      :class="{ 'sg-internal-note': internal }"
                                      placeholder="{{ $isStaff ? 'Balas pemohon atau tulis catatan internal…' : 'Tulis komentar untuk tim IT…' }}">{{ old('comment') }}</textarea>
                            @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                @if ($isStaff)
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_internal" value="1" id="is_internal" x-model="internal">
                                        <label class="form-check-label" for="is_internal"><i class="bi bi-lock" aria-hidden="true"></i> Catatan internal</label>
                                    </div>
                                @else
                                    <span></span>
                                @endif
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send" aria-hidden="true"></i> Kirim</button>
                            </div>
                        </form>
                    @endif
                @endcan
            </section>
        </div>

        <aside class="col-lg-4">
            @if ($isStaff)
                @include('tickets.partials.sla-card')
            @endif

            {{-- Pemohon --}}
            <section class="card p-3 mb-3" aria-labelledby="requester-title">
                <h2 id="requester-title" class="h6 fw-bold mb-3">Pemohon</h2>
                <dl class="sg-meta-grid border-0 pt-0 mb-0" style="grid-template-columns:1fr 1fr">
                    <div><dt>Nama</dt><dd>{{ $ticket->requester_name }}</dd></div>
                    <div><dt>Departemen</dt><dd>{{ $ticket->requester_department ?: '-' }}</dd></div>
                    @if ($ticket->requester_employee_id)<div><dt>NIK / ID</dt><dd>{{ $ticket->requester_employee_id }}</dd></div>@endif
                    <div><dt>Sumber</dt><dd>{{ $ticket->requester_user_id ? 'Akun SiGAP' : 'Form publik' }}</dd></div>
                    @if ($isStaff && ! $ticket->requester_user_id)<div><dt>Kode akses</dt><dd class="font-monospace">{{ $ticket->tracking_token }}</dd></div>@endif
                </dl>
                @if ($ticket->requester_phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ticket->requester_phone) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary w-100 mt-3">
                        <i class="bi bi-whatsapp" aria-hidden="true"></i> {{ $ticket->requester_phone }}
                    </a>
                @endif
            </section>

            @if ($hasActions)
                <section class="card p-3 mb-3 d-none d-lg-block" aria-label="Tindakan tim IT">
                    @include('tickets.partials.staff-actions', ['sfx' => 'd'])
                </section>
            @endif

            @if ($isStaff && $ticket->csat_rating)
                <section class="card p-3 mb-3" aria-labelledby="csat-title">
                    <h2 id="csat-title" class="h6 fw-bold mb-1">Penilaian pemohon</h2>
                    <div class="fs-5" style="color:var(--sg-accent)" aria-label="{{ $ticket->csat_rating }} dari 5 bintang">
                        @for ($i = 1; $i <= 5; $i++)<i class="bi {{ $i <= $ticket->csat_rating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>@endfor
                    </div>
                    @if ($ticket->csat_comment)<p class="sg-prewrap small mt-2 mb-1">{{ $ticket->csat_comment }}</p>@endif
                    <div class="small text-muted">{{ \App\Support\Format::datetime($ticket->csat_submitted_at) }}</div>
                </section>
            @endif

            <section class="card p-3 mb-3" aria-labelledby="history-title">
                <h2 id="history-title" class="h6 fw-bold mb-1">Riwayat</h2>
                <p class="small text-muted">Ditangani: <strong>{{ $ticket->assignedUser->name ?? '—' }}</strong></p>
                @include('tickets.partials.timeline')
            </section>
        </aside>
    </div>

    {{-- Mobile: aksi tim IT lewat bottom sheet --}}
    @if ($hasActions)
        <x-sg.sticky-actions>
            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#ticket-actions">
                <i class="bi bi-lightning-charge" aria-hidden="true"></i> Tindakan
            </button>
        </x-sg.sticky-actions>
        <x-sg.sheet id="ticket-actions" title="Tindakan tiket">
            @include('tickets.partials.staff-actions', ['sfx' => 'm'])
        </x-sg.sheet>
        @if ($errors->hasAny(['assigned_to', 'due_date', 'status', 'note']))
            @push('scripts')
                <script>if (matchMedia('(max-width: 991.98px)').matches) bootstrap.Modal.getOrCreateInstance(document.getElementById('ticket-actions')).show();</script>
            @endpush
        @endif
    @endif

    @can('delete', $ticket)
        <x-sg.confirm-danger id="delete-ticket" title="Hapus tiket ini?" :action="route('tickets.destroy', $ticket)"
                             text="Tiket {{ $ticket->ticket_number }} akan dihapus." button="Hapus tiket" />
    @endcan
</x-app-layout>
