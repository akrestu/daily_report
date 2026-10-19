@extends('layouts.public')

@section('title', 'Tiket Terkirim')

{{-- Sukses kirim tiket — mockup: TicketSuccessMobile --}}
@section('content')
@php($copyText = "Tiket SiGAP {$ticket->ticket_number}\nKode akses: {$ticket->tracking_token}\nLacak: ".route('tickets.public.track'))
<div x-data="{ copied: false, async copy() { await navigator.clipboard.writeText(@js($copyText)); this.copied = true; sgToast('Nomor tiket & kode akses disalin'); } }">
    <x-sg.brand-panel class="text-center mb-3">
        <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle" style="width:56px;height:56px;background:var(--sg-accent);color:var(--sg-on-accent);font-size:28px">
            <i class="bi bi-check-lg" aria-hidden="true"></i>
        </div>
        <h1 class="h5 fw-bold mb-1">Tiket berhasil dikirim</h1>
        <p class="small mb-3 opacity-75">Tim IT akan segera menindaklanjuti permintaan Anda.</p>
        <div class="small text-uppercase fw-semibold" style="color:var(--sg-accent);letter-spacing:.08em">Nomor tiket</div>
        <div class="display-6 fw-bold sg-num mb-3">{{ $ticket->ticket_number }}</div>
        <div class="d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-accent" @click="copy()"><i class="bi" :class="copied ? 'bi-check2' : 'bi-copy'" aria-hidden="true"></i> Salin</button>
            <a class="btn btn-outline-light" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($copyText) }}"><i class="bi bi-whatsapp" aria-hidden="true"></i> Bagikan</a>
        </div>
    </x-sg.brand-panel>

    <div class="sg-internal-note p-3 mb-3">
        <div class="fw-semibold mb-1"><i class="bi bi-key" aria-hidden="true"></i> Kode akses — hanya ditampilkan sekali</div>
        <div class="font-monospace fs-4 fw-bold sg-num">{{ $ticket->tracking_token }}</div>
        <div class="small">Simpan bersama nomor tiket untuk melacak status. Tombol Salin menyalin keduanya.</div>
    </div>

    <dl class="sg-meta-grid mb-3" style="grid-template-columns:1fr 1fr">
        <div><dt>Judul</dt><dd>{{ $ticket->title }}</dd></div>
        <div><dt>Kategori</dt><dd>{{ $ticket->category->name ?? '-' }}</dd></div>
        <div><dt>Prioritas</dt><dd><x-sg.badge-priority :value="$ticket->priority" code /></dd></div>
        <div><dt>Status</dt><dd><x-sg.badge-status type="ticket_status" :value="$ticket->status" /></dd></div>
        <div><dt>Dikirim</dt><dd>{{ \App\Support\Format::datetime($ticket->created_at) }} WIB</dd></div>
    </dl>

    @if ($ticket->approval_status)
        <div class="alert alert-warning small">Permintaan ini perlu <strong>persetujuan atasan</strong>. Buka <strong>Lacak Tiket</strong> untuk mencetak formulir,
            minta tanda tangan atasan, lalu unggah fotonya. Tiket dikerjakan setelah persetujuan diverifikasi tim IT.</div>
    @endif
    @if ($ticket->requester_phone)
        <p class="small text-muted">Kabar status dikirim ke <strong>{{ $ticket->requester_phone }}</strong>.</p>
    @endif

    <div class="sg-public-actions">
        <a href="{{ route('tickets.public.form') }}" class="btn btn-outline-secondary">Ajukan lagi</a>
        <a href="{{ route('tickets.public.track') }}" class="btn btn-primary flex-fill"><i class="bi bi-search" aria-hidden="true"></i> Lacak Tiket</a>
    </div>
</div>
@endsection
