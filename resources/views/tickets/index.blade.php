<x-app-layout>
    <x-slot name="header">Tiket Masuk</x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-ticket-alt text-primary me-2"></i>Tiket Masuk
                        @if($openCount > 0)
                            <span class="badge bg-danger ms-1">{{ $openCount }} terbuka</span>
                        @endif
                    </h5>
                </div>
                <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end">
                    {{-- Batch delete (hanya admin) --}}
                    @can('deleteAny', App\Models\Ticket::class)
                    <form id="batchDeleteForm" action="{{ route('tickets.batch-delete') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div id="batchActions" class="d-none">
                            <button type="button" onclick="confirmBatchDelete()"
                                class="btn btn-danger btn-sm">
                                <i class="fas fa-trash me-1"></i>Hapus (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    </form>
                    @endcan
                    <a href="{{ route('tickets.public.form') }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i>Buka Form Publik
                    </a>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card-body border-bottom pb-3">
            <form method="GET" action="{{ route('tickets.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Cari tiket, nama, departemen..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach(\App\Models\Ticket::STATUSES as $value => $meta)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sla" class="form-select form-select-sm">
                        <option value="">Semua SLA</option>
                        <option value="breached" {{ request('sla') === 'breached' ? 'selected' : '' }}>SLA Terlewat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Semua Prioritas</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Mendesak</option>
                        <option value="high"   {{ request('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                        <option value="low"    {{ request('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3 mb-0" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show m-3 mb-0" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            @can('deleteAny', App\Models\Ticket::class)
                            <th class="ps-3" style="width:40px">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            @endcan
                            <th class="{{ auth()->user()->isAdmin() ? '' : 'ps-3' }}" style="width:130px">No. Tiket</th>
                            <th>Judul / Pemohon</th>
                            <th style="width:120px">Kategori</th>
                            <th style="width:90px">Prioritas</th>
                            <th style="width:100px">Status</th>
                            <th style="width:120px">Handler</th>
                            <th style="width:100px">SLA</th>
                            <th style="width:100px">Tanggal</th>
                            <th style="width:80px" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            @can('deleteAny', App\Models\Ticket::class)
                            <td class="ps-3">
                                <input type="checkbox" class="form-check-input row-check"
                                    value="{{ $ticket->id }}">
                            </td>
                            @endcan
                            <td class="{{ auth()->user()->isAdmin() ? '' : 'ps-3' }}">
                                <span class="fw-semibold text-primary small">{{ $ticket->ticket_number }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold small">{{ $ticket->title }}</div>
                                <div class="text-muted" style="font-size: 0.75rem">
                                    <i class="fas fa-user me-1"></i>{{ $ticket->requester_name }}
                                    &bull; {{ $ticket->requester_department }}
                                    @if($ticket->requester_phone)
                                        &bull; {{ $ticket->requester_phone }}
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border small">{{ $ticket->category->name ?? '-' }}</span></td>
                            <td><span class="badge {{ $ticket->priority_badge_class }} small">{{ $ticket->priority_label }}</span></td>
                            <td><span class="badge {{ $ticket->status_badge_class }} small">{{ $ticket->status_label }}</span>
                                @if($ticket->awaitingApproval())
                                    <div><span class="badge {{ $ticket->approval_badge_class }} mt-1" style="white-space:normal">{{ $ticket->approval_label }}</span></div>
                                @endif</td>
                            <td class="small">
                                @if($ticket->assignedUser)
                                    <i class="fas fa-user-check text-success me-1"></i>{{ $ticket->assignedUser->name }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>@include('tickets.partials.sla-badge')</td>
                            <td class="small text-muted">{{ $ticket->created_at->format('d M Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 10 : 9 }}" class="text-center py-5 text-muted">
                                <i class="fas fa-ticket-alt fa-2x mb-2 d-block opacity-25"></i>
                                Tidak ada tiket yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $tickets->links() }}
            </div>
            @endif
        </div>
    </div>

@can('deleteAny', App\Models\Ticket::class)
<script>
    const selectAll   = document.getElementById('selectAll');
    const batchForm   = document.getElementById('batchDeleteForm');
    const batchActions = document.getElementById('batchActions');
    const countLabel  = document.getElementById('selectedCount');

    function getChecked() {
        return document.querySelectorAll('.row-check:checked');
    }

    function updateBatchBar() {
        const checked = getChecked();
        countLabel.textContent = checked.length;
        batchActions.classList.toggle('d-none', checked.length === 0);
    }

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        updateBatchBar();
    });

    document.querySelectorAll('.row-check').forEach(cb => {
        cb.addEventListener('change', function () {
            selectAll.checked = document.querySelectorAll('.row-check').length === getChecked().length;
            updateBatchBar();
        });
    });

    function confirmBatchDelete() {
        const checked = getChecked();
        if (checked.length === 0) return;

        if (!confirm('Hapus ' + checked.length + ' tiket yang dipilih? Tindakan ini tidak dapat dibatalkan.')) return;

        // Remove previous hidden inputs
        batchForm.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'ids[]';
            input.value = cb.value;
            batchForm.appendChild(input);
        });

        batchForm.submit();
    }
</script>
@endcan
</x-app-layout>
