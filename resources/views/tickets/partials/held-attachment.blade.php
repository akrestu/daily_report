{{-- Lampiran yang ditahan server saat validasi gagal (StoreTicketRequest::holdAttachment). File baru yang dipilih menggantikannya. --}}
@if ($held = session('held_attachment'))
    <div class="sg-status-card sg-status-card--approved d-flex align-items-center gap-2 mt-2" x-data="{ keep: true }" x-show="keep">
        <input type="hidden" name="attachment_token" value="{{ $held['token'] }}" :disabled="!keep">
        <i class="bi bi-paperclip flex-none" aria-hidden="true"></i>
        <div class="min-w-0 flex-grow-1 small">
            <div class="fw-semibold sg-clamp-1">{{ $held['name'] }}</div>
            <div>Lampiran tetap tersimpan, tidak perlu dipilih ulang.</div>
        </div>
        <button type="button" class="btn btn-quiet btn-icon flex-none" @click="keep = false" aria-label="Hapus lampiran {{ $held['name'] }}"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
@endif
