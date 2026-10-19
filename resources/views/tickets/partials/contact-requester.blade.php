{{--
    Hubungi pemohon via WhatsApp (tim IT). Draf dari App\Support\TicketMessage sesuai status tiket,
    nada formal/ramah, bisa disunting; tombol membuka wa.me dengan teks terisi (petugas menekan Kirim di WhatsApp).
--}}
@php
    $waNumber = \App\Support\TicketMessage::whatsappNumber($ticket->requester_phone);
    $drafts = \App\Support\TicketMessage::drafts($ticket);
@endphp
<div x-data="{
        drafts: @js($drafts),
        tone: 'formal',
        text: '',
        edited: false,
        init() { this.text = this.drafts[this.tone]; },
        async setTone(t) {
            if (t === this.tone) return;
            if (this.edited && ! (await window.sgConfirm('Teks yang sudah Anda ubah akan diganti draf baru.', { title: 'Ganti nada pesan?', ok: 'Ganti' }))) {
                this.$refs['tone_' + this.tone].checked = true;
                return;
            }
            this.tone = t; this.text = this.drafts[t]; this.edited = false;
        },
        get waUrl() { return 'https://wa.me/{{ $waNumber }}?text=' + encodeURIComponent(this.text); },
        async copy() {
            try { await navigator.clipboard.writeText(this.text); window.sgToast?.('Pesan disalin'); }
            catch (e) { window.sgToast?.('Gagal menyalin pesan', { tone: 'danger' }); }
        },
     }" class="mt-3 pt-3 border-top">
    <h3 class="h6 fw-bold mb-1">Hubungi pemohon</h3>
    <p class="small text-muted mb-2">
        @if ($ticket->status === \App\Models\Ticket::STATUS_RESOLVED)
            Kabarkan tiket sudah selesai beserta catatan penyelesaian, dan minta pemohon mengonfirmasi &amp; menutup tiket lewat tautan di pesan.
        @else
            Draf menyesuaikan status tiket saat ini dan memuat tautan langsung ke tiket. Periksa sebelum dikirim.
        @endif
    </p>

    <div class="sg-segment mb-2" style="grid-template-columns:1fr 1fr" role="radiogroup" aria-label="Nada pesan">
        @foreach (\App\Support\TicketMessage::TONES as $value => $label)
            <input type="radio" class="btn-check" name="wa-tone" id="wa-tone-{{ $value }}" value="{{ $value }}" autocomplete="off"
                   x-ref="tone_{{ $value }}" @checked($loop->first) @change="setTone('{{ $value }}')">
            <label class="sg-segment__item" for="wa-tone-{{ $value }}">{{ $label }}</label>
        @endforeach
    </div>

    <label for="wa-text" class="visually-hidden">Isi pesan WhatsApp</label>
    <textarea id="wa-text" class="form-control mb-2" rows="9" x-model="text" @input="edited = true"></textarea>
    @if ($ticket->status === \App\Models\Ticket::STATUS_RESOLVED && ! trim((string) $ticket->resolution_note))
        <p class="small text-danger mb-2">Catatan penyelesaian kosong. Tambahkan penjelasan singkat apa yang sudah dikerjakan sebelum mengirim.</p>
    @endif

    @if ($waNumber)
        <a :href="waUrl" target="_blank" rel="noopener" class="btn btn-primary w-100 mb-2">
            <i class="bi bi-whatsapp" aria-hidden="true"></i> Kirim lewat WhatsApp
        </a>
    @else
        <p class="small text-danger mb-2">Nomor "{{ $ticket->requester_phone }}" tidak dikenali sebagai nomor WhatsApp. Salin pesan lalu kirim manual.</p>
    @endif
    <button type="button" class="btn btn-outline-secondary w-100" @click="copy()"><i class="bi bi-copy" aria-hidden="true"></i> Salin pesan</button>
</div>
