<x-app-layout>
    <x-slot name="header">Tiket Saya</x-slot>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-ticket-alt text-primary me-2"></i>Tiket Saya</h5>
            <div class="d-flex gap-2">
                <form method="GET" action="{{ route('tickets.mine') }}">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        @foreach(\App\Models\Ticket::STATUSES as $value => $meta)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('tickets.create') }}" class="btn btn-primary btn-sm text-nowrap">
                    <i class="fas fa-plus me-1"></i>Buat Tiket
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success m-3 mb-0"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:150px">No. Tiket</th>
                            <th>Judul</th>
                            <th style="width:90px">Prioritas</th>
                            <th style="width:100px">Status</th>
                            <th style="width:140px">Ditangani</th>
                            <th style="width:120px">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('tickets.show', $ticket) }}" class="fw-semibold small text-decoration-none">{{ $ticket->ticket_number }}</a>
                            </td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="fw-semibold small text-dark text-decoration-none">{{ $ticket->title }}</a>
                                <div class="text-muted" style="font-size:.75rem">{{ $ticket->category->name ?? '-' }}</div>
                            </td>
                            <td><span class="badge {{ $ticket->priority_badge_class }} small">{{ $ticket->priority_label }}</span></td>
                            <td><span class="badge {{ $ticket->status_badge_class }} small">{{ $ticket->status_label }}</span>
                                @if($ticket->awaitingApproval())
                                    <div><span class="badge {{ $ticket->approval_badge_class }} mt-1" style="white-space:normal">{{ $ticket->approval_label }}</span></div>
                                @endif</td>
                            <td class="small">{{ $ticket->assignedUser->name ?? '—' }}</td>
                            <td class="small text-muted">{{ $ticket->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Belum ada tiket. Semua permintaan ke tim IT diajukan melalui tiket.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tickets->hasPages())
            <div class="card-footer bg-white">{{ $tickets->links() }}</div>
        @endif
    </div>
</x-app-layout>
