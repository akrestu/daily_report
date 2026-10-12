<x-app-layout>
    <x-slot name="header">Detail Tiket</x-slot>

    @php
        $isStaff = $canViewInternal;
        $canAssign = auth()->user()->can('assign', App\Models\Ticket::class) && \App\Services\TicketWorkflow::canBeAssigned($ticket);
    @endphp

    <div class="row g-3">
        {{-- Info Tiket --}}
        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @can('respond', $ticket)
                @include('tickets.partials.requester-actions', ['action' => route('tickets.respond', $ticket)])
            @endcan

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="fw-bold text-primary me-2">{{ $ticket->ticket_number }}</span>
                        <span class="badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                        <span class="badge {{ $ticket->priority_badge_class }} ms-1">{{ $ticket->priority_label }}</span>
                        @if($ticket->reopen_count > 0)
                            <span class="badge bg-danger ms-1" title="Jumlah dibuka kembali oleh pemohon">Reopen {{ $ticket->reopen_count }}x</span>
                        @endif
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ auth()->user()->can('viewAny', App\Models\Ticket::class) ? route('tickets.index') : route('tickets.mine') }}"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                        @can('delete', $ticket)
                        <form action="{{ route('tickets.destroy', $ticket) }}" method="POST"
                            onsubmit="return confirm('Hapus tiket ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $ticket->title }}</h5>
                    <div class="text-muted small mb-3">
                        <span class="badge bg-light text-dark border">{{ $ticket->type_label }}</span>
                        <span class="badge bg-light text-dark border">{{ $ticket->category->name ?? '-' }}</span>
                        &bull; {{ $ticket->created_at->diffForHumans() }}
                        @if($ticket->jobSite)
                            &bull; {{ $ticket->jobSite->name }}
                        @endif
                    </div>

                    <div class="mb-4">
                        <p class="fw-semibold small text-uppercase text-muted mb-1">Deskripsi</p>
                        <div class="bg-light rounded p-3" style="white-space: pre-wrap; font-size: 0.9rem">{{ $ticket->description }}</div>
                    </div>

                    @if($ticket->resolution_note && $ticket->status !== App\Models\Ticket::STATUS_RESOLVED)
                    <div class="mb-4">
                        <p class="fw-semibold small text-uppercase text-muted mb-1">Catatan Penyelesaian</p>
                        <div class="border border-success rounded p-3 small" style="white-space: pre-wrap">{{ $ticket->resolution_note }}</div>
                    </div>
                    @endif

                    @if($ticket->attachment_path)
                    <div class="mb-4">
                        <p class="fw-semibold small text-uppercase text-muted mb-1">Lampiran</p>
                        <a href="{{ route('tickets.attachment', $ticket) }}" target="_blank"
                            class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-paperclip me-1"></i>{{ $ticket->attachment_original_name ?? 'Lihat Lampiran' }}
                        </a>
                    </div>
                    @endif

                    @if($isStaff && $ticket->jobPlan)
                    <div class="alert alert-success d-flex align-items-start gap-2 mb-0">
                        <i class="fas fa-link mt-1"></i>
                        <div>
                            <div class="fw-semibold">Job Plan Terhubung</div>
                            <div class="small">
                                Tiket ini diteruskan sebagai job plan untuk
                                <strong>{{ $ticket->jobPlan->assignee->name ?? '-' }}</strong>.
                                Status: <span class="badge bg-{{ $ticket->jobPlan->status === 'converted' ? 'success' : 'warning text-dark' }}">
                                    {{ $ticket->jobPlan->status === 'converted' ? 'Dikonversi' : 'Aktif' }}
                                </span>
                            </div>
                            <a href="{{ route('job-plans.show', $ticket->job_plan_id) }}"
                                class="btn btn-sm btn-success mt-1">
                                <i class="fas fa-external-link-alt me-1"></i>Lihat Job Plan
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Komentar --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-comments text-primary me-2"></i>Komentar</h6>
                    @if($isStaff)
                        <div class="text-muted small">Balasan biasa terlihat oleh pemohon. Centang "catatan internal" untuk catatan tim IT saja.</div>
                    @endif
                </div>
                <div class="card-body">
                    @include('tickets.partials.comments', ['comments' => $ticket->comments])

                    @can('addComment', $ticket)
                    @if($ticket->isActive())
                    <form action="{{ route('tickets.comment', $ticket) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="mb-2">
                            <textarea name="comment" rows="3" class="form-control @error('comment') is-invalid @enderror"
                                placeholder="{{ $isStaff ? 'Tulis balasan ke pemohon atau catatan internal...' : 'Tulis komentar untuk tim IT...' }}" required>{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            @if($isStaff)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="is_internal">
                                    <label class="form-check-label small" for="is_internal"><i class="fas fa-lock me-1"></i>Catatan internal</label>
                                </div>
                            @else
                                <span></span>
                            @endif
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-paper-plane me-1"></i>Kirim
                            </button>
                        </div>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        {{-- Panel Kanan --}}
        <div class="col-lg-4">
            {{-- Info Pemohon --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user text-primary me-2"></i>Informasi Pemohon</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Nama</dt>
                        <dd class="col-7 fw-semibold">{{ $ticket->requester_name }}</dd>

                        @if($ticket->requester_employee_id)
                        <dt class="col-5 text-muted fw-normal">NIK / ID</dt>
                        <dd class="col-7">{{ $ticket->requester_employee_id }}</dd>
                        @endif

                        <dt class="col-5 text-muted fw-normal">Departemen</dt>
                        <dd class="col-7">{{ $ticket->requester_department }}</dd>

                        @if($ticket->requester_phone)
                        <dt class="col-5 text-muted fw-normal">No. HP</dt>
                        <dd class="col-7">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ticket->requester_phone) }}"
                                target="_blank" class="text-success">
                                <i class="fab fa-whatsapp me-1"></i>{{ $ticket->requester_phone }}
                            </a>
                        </dd>
                        @endif

                        <dt class="col-5 text-muted fw-normal">Sumber</dt>
                        <dd class="col-7">{{ $ticket->requester_user_id ? 'Akun SiGAP' : 'Form publik' }}</dd>

                        @if($isStaff && ! $ticket->requester_user_id)
                        <dt class="col-5 text-muted fw-normal">Kode Akses</dt>
                        <dd class="col-7 font-monospace">{{ $ticket->tracking_token }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Assign / Alihkan --}}
            @if($canAssign)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-user-plus text-success me-2"></i>{{ $ticket->assigned_to ? 'Alihkan Penanganan' : 'Assign & Buat Job Plan' }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
                        @csrf
                        @if ($errors->hasAny(['assigned_to', 'due_date']))
                        <div class="alert alert-danger small p-2">
                            @foreach($errors->only(['assigned_to', 'due_date']) as $e) <div>{{ $e }}</div> @endforeach
                        </div>
                        @endif

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Anggota Tim IT <span class="text-danger">*</span></label>
                            <select name="assigned_to" class="form-select form-select-sm" required>
                                <option value="">— Pilih Anggota Tim —</option>
                                @foreach($eligibleHandlers as $handler)
                                    <option value="{{ $handler->id }}" {{ $ticket->assigned_to === $handler->id ? 'disabled' : '' }}>
                                        {{ $handler->name }} ({{ $handler->role->name ?? '-' }}){{ $ticket->assigned_to === $handler->id ? ' — saat ini' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Target Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control form-control-sm"
                                min="{{ today()->format('Y-m-d') }}"
                                value="{{ optional($ticket->jobPlan?->due_date)->format('Y-m-d') ?? today()->addDays(3)->format('Y-m-d') }}" required>
                        </div>

                        <div class="alert alert-light border small py-2 mb-3">
                            <i class="fas fa-info-circle text-primary me-1"></i>
                            @if($ticket->assigned_to)
                                Job Plan yang masih aktif akan dipindahkan ke anggota yang dipilih.
                            @else
                                Tiket diteruskan sebagai <strong>Job Plan</strong> dan masuk ke <em>Tugas Masuk</em> anggota yang dipilih.
                            @endif
                        </div>

                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-check me-1"></i>{{ $ticket->assigned_to ? 'Alihkan' : 'Assign & Buat Job Plan' }}
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Ubah Status --}}
            @if(count($transitions) > 0)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-sync-alt text-warning me-2"></i>Ubah Status</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('tickets.update-status', $ticket) }}" method="POST">
                        @csrf
                        <select name="status" class="form-select form-select-sm mb-2" id="statusSelect" required>
                            <option value="">— Pilih status baru —</option>
                            @foreach($transitions as $status)
                                <option value="{{ $status }}" {{ old('status') === $status ? 'selected' : '' }}>
                                    {{ App\Models\Ticket::STATUSES[$status]['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <textarea name="note" rows="3" class="form-control form-control-sm mb-1" id="statusNote" maxlength="2000"
                            placeholder="Catatan (opsional)">{{ old('note') }}</textarea>
                        <div class="form-text mb-2" id="statusNoteHelp"></div>
                        <button type="submit" class="btn btn-warning btn-sm text-dark w-100">
                            <i class="fas fa-save me-1"></i>Simpan Status
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Riwayat --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history text-primary me-2"></i>Riwayat</h6>
                    <div class="small text-muted">Ditangani: <strong>{{ $ticket->assignedUser->name ?? '—' }}</strong></div>
                </div>
                <div class="card-body">
                    @include('tickets.partials.timeline')
                </div>
            </div>
        </div>
    </div>

    @if(count($transitions) > 0)
    <script>
        (function () {
            const hints = {
                pending: 'Wajib: jelaskan informasi apa yang ditunggu dari pemohon. Catatan ini terlihat oleh pemohon.',
                resolved: 'Wajib: tuliskan solusi / tindakan yang dilakukan. Pemohon akan diminta konfirmasi.',
                cancelled: 'Wajib: alasan pembatalan. Terlihat oleh pemohon.',
            };
            const select = document.getElementById('statusSelect');
            const note = document.getElementById('statusNote');
            const help = document.getElementById('statusNoteHelp');
            function update() {
                const hint = hints[select.value];
                note.required = !!hint;
                note.placeholder = hint ? 'Catatan (wajib)' : 'Catatan (opsional)';
                help.textContent = hint || '';
            }
            select.addEventListener('change', update);
            update();
        })();
    </script>
    @endif
</x-app-layout>
