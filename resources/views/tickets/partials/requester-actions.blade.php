{{--
    Aksi pemohon: konfirmasi selesai / belum selesai (status resolved) dan batalkan (status open/assigned).
    $action = URL tujuan form (route internal tickets.respond atau publik tickets.public.respond).
    $csatAction = URL penilaian kepuasan (tickets.csat / tickets.public.csat).
--}}
@if($ticket->canSubmitCsat())
    <div class="card border-primary shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-1"><i class="bi bi-star-fill me-1"></i>Bagaimana layanan tim IT untuk tiket ini?</h6>
            <p class="small text-muted mb-2">Penilaian Anda membantu kami meningkatkan layanan.</p>
            <form action="{{ $csatAction }}" method="POST">
                @csrf
                <div class="csat-stars mb-2" role="radiogroup" aria-label="Nilai 1 sampai 5">
                    @for($i = 5; $i >= 1; $i--)
                        <input type="radio" name="rating" id="csat{{ $i }}" value="{{ $i }}" required {{ old('rating') == $i ? 'checked' : '' }}>
                        <label for="csat{{ $i }}" title="{{ [1 => 'Sangat buruk', 2 => 'Buruk', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat baik'][$i] }}">&#9733;</label>
                    @endfor
                </div>
                <textarea name="comment" rows="2" class="form-control form-control-sm mb-2" maxlength="1000"
                    placeholder="Saran atau komentar (opsional)">{{ old('comment') }}</textarea>
                <button type="submit" class="btn btn-primary btn-sm">Kirim Penilaian</button>
            </form>
        </div>
    </div>
@elseif($ticket->csat_rating)
    <div class="alert alert-light border small mb-3">
        <i class="bi bi-star-fill text-warning me-1"></i>
        Penilaian Anda: <strong>{{ $ticket->csat_rating }}/5</strong>. Terima kasih atas masukannya.
    </div>
@endif

@if($ticket->status === \App\Models\Ticket::STATUS_RESOLVED)
    <div class="card border-success shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-success mb-1"><i class="bi bi-clipboard-check me-1"></i>Apakah masalah Anda sudah teratasi?</h6>
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
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Ya, sudah selesai</button>
            </form>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#reopenForm">
                <i class="bi bi-arrow-clockwise me-1"></i>Belum selesai
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
            <i class="bi bi-x-circle me-1"></i>Batalkan tiket ini
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
