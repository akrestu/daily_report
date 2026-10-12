<x-app-layout>
    <x-slot name="header">Detail Tiket</x-slot>

    <div class="row g-3">
        {{-- Info Tiket --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-primary me-2">{{ $ticket->ticket_number }}</span>
                        <span class="badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                        <span class="badge {{ $ticket->priority_badge_class }} ms-1">{{ $ticket->priority_label }}</span>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-secondary">
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

                    <h5 class="fw-bold mb-1">{{ $ticket->title }}</h5>
                    <div class="text-muted small mb-3">
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

                    @if($ticket->attachment_path)
                    <div class="mb-4">
                        <p class="fw-semibold small text-uppercase text-muted mb-1">Lampiran</p>
                        <a href="{{ route('tickets.attachment', $ticket) }}" target="_blank"
                            class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-paperclip me-1"></i>{{ $ticket->attachment_original_name ?? 'Lihat Lampiran' }}
                        </a>
                    </div>
                    @endif

                    @if($ticket->jobPlan)
                    <div class="alert alert-success d-flex align-items-start gap-2 mb-0">
                        <i class="fas fa-link mt-1"></i>
                        <div>
                            <div class="fw-semibold">Job Plan Terhubung</div>
                            <div class="small">
                                Tiket ini telah diteruskan sebagai job plan untuk
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

            {{-- Komentar Internal --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-comments text-primary me-2"></i>Komentar Internal</h6>
                </div>
                <div class="card-body">
                    @forelse($ticket->comments as $comment)
                    <div class="d-flex gap-2 mb-3">
                        <div class="flex-shrink-0">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle"
                                style="width:36px;height:36px;font-size:0.8rem;font-weight:600">
                                {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="bg-light rounded p-2">
                                <div class="fw-semibold small">{{ $comment->user->name ?? '-' }}
                                    <span class="text-muted fw-normal">· {{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="small mt-1" style="white-space: pre-wrap">{{ $comment->comment }}</div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small text-center py-3">Belum ada komentar.</p>
                    @endforelse

                    @can('addComment', $ticket)
                    <form action="{{ route('tickets.comment', $ticket) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="mb-2">
                            <textarea name="comment" rows="3" class="form-control @error('comment') is-invalid @enderror"
                                placeholder="Tulis komentar internal..." required>{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i>Kirim Komentar
                        </button>
                    </form>
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
                        <dt class="col-5 text-muted fw-normal">NIK</dt>
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

                        <dt class="col-5 text-muted fw-normal">Tanggal</dt>
                        <dd class="col-7">{{ $ticket->created_at->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Assign Handler --}}
            @can('assign', App\Models\Ticket::class)
            @if($ticket->status === 'open')
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-user-plus text-success me-2"></i>Assign & Buat Job Report</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('tickets.assign', $ticket) }}" method="POST">
                        @csrf
                        @if ($errors->any())
                        <div class="alert alert-danger small p-2">
                            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                        </div>
                        @endif

                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Assign ke Anggota Tim <span class="text-danger">*</span></label>
                            <select name="assigned_to" class="form-select form-select-sm" required>
                                <option value="">— Pilih Anggota Tim —</option>
                                @foreach($eligibleHandlers as $handler)
                                    <option value="{{ $handler->id }}">{{ $handler->name }} ({{ $handler->role->name ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Target Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control form-control-sm"
                                min="{{ today()->format('Y-m-d') }}"
                                value="{{ today()->addDays(3)->format('Y-m-d') }}" required>
                        </div>

                        <div class="alert alert-light border small py-2 mb-3">
                            <i class="fas fa-info-circle text-primary me-1"></i>
                            Tiket akan diteruskan sebagai <strong>Job Plan</strong> dan langsung masuk ke
                            <em>Tugas Masuk</em> anggota yang dipilih.
                        </div>

                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-check me-1"></i>Assign & Buat Job Report
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @endcan

            {{-- Update Status --}}
            @can('updateStatus', $ticket)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-sync-alt text-warning me-2"></i>Ubah Status</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('tickets.update-status', $ticket) }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <select name="status" class="form-select form-select-sm">
                            <option value="open"        {{ $ticket->status == 'open' ? 'selected' : '' }}>Terbuka</option>
                            <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>Diproses</option>
                            <option value="resolved"    {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Selesai</option>
                            <option value="closed"      {{ $ticket->status == 'closed' ? 'selected' : '' }}>Ditutup</option>
                        </select>
                        <button type="submit" class="btn btn-warning btn-sm text-dark">
                            <i class="fas fa-save"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endcan

            {{-- Handler & Timeline --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>Info Penanganan</h6>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted fw-normal">Handler</dt>
                        <dd class="col-7 fw-semibold">{{ $ticket->assignedUser->name ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Dibuat</dt>
                        <dd class="col-7">{{ $ticket->created_at->format('d M Y') }}</dd>

                        @if($ticket->resolved_at)
                        <dt class="col-5 text-muted fw-normal">Diselesaikan</dt>
                        <dd class="col-7">{{ $ticket->resolved_at->format('d M Y') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
