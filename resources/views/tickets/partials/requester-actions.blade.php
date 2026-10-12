{{--
    Aksi pemohon: konfirmasi selesai / belum selesai (status resolved) dan batalkan (status open/assigned).
    $action = URL tujuan form (route internal tickets.respond atau publik tickets.public.respond).
--}}
@if($ticket->status === \App\Models\Ticket::STATUS_RESOLVED)
    <div class="card border-success shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-success mb-1"><i class="fas fa-clipboard-check me-1"></i>Apakah masalah Anda sudah teratasi?</h6>
            <p class="small text-muted mb-2">
                Tim IT menandai tiket ini selesai. Jika tidak dikonfirmasi, tiket akan ditutup otomatis
                {{ config('tickets.auto_close_working_days') }} hari kerja setelah diselesaikan.
            </p>
            @if($ticket->resolution_note)
                <div class="bg-light rounded p-2 small mb-3" style="white-space:pre-wrap"><strong>Solusi:</strong> {{ $ticket->resolution_note }}</div>
            @endif

            <form action="{{ $action }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="action" value="confirm">
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Ya, sudah selesai</button>
            </form>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#reopenForm">
                <i class="fas fa-redo me-1"></i>Belum selesai
            </button>

            <form action="{{ $action }}" method="POST" class="collapse mt-3" id="reopenForm">
                @csrf
                <input type="hidden" name="action" value="reopen">
                <textarea name="reason" rows="3" class="form-control form-control-sm mb-2" required maxlength="2000"
                    placeholder="Jelaskan bagian mana yang belum selesai...">{{ old('reason') }}</textarea>
                <button type="submit" class="btn btn-danger btn-sm">Buka Kembali Tiket</button>
            </form>
        </div>
    </div>
@elseif(in_array($ticket->status, \App\Services\TicketWorkflow::REQUESTER_CANCELLABLE, true))
    <div class="mb-3 text-end">
        <button type="button" class="btn btn-link btn-sm text-danger p-0" data-bs-toggle="collapse" data-bs-target="#cancelForm">
            <i class="fas fa-times-circle me-1"></i>Batalkan tiket ini
        </button>
        <form action="{{ $action }}" method="POST" class="collapse mt-2 text-start" id="cancelForm">
            @csrf
            <input type="hidden" name="action" value="cancel">
            <textarea name="reason" rows="2" class="form-control form-control-sm mb-2" required maxlength="2000"
                placeholder="Alasan pembatalan...">{{ old('reason') }}</textarea>
            <button type="submit" class="btn btn-outline-danger btn-sm">Ya, batalkan tiket</button>
        </form>
    </div>
@endif
