@extends('layouts.public')

@section('title', 'Cek Status Tiket')

@section('content')
<div class="mb-3">
    <h5 class="fw-bold text-dark mb-1">
        <i class="fas fa-search text-primary me-2"></i>Cek Status Tiket
    </h5>
    <p class="text-muted small mb-0">Masukkan nomor tiket dan kode akses yang Anda terima saat mengajukan permintaan.</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger small mb-3">
        <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
    </div>
@endif

<form action="{{ route('tickets.public.track.show') }}" method="POST" class="mb-4">
    @csrf
    <div class="row g-2">
        <div class="col-sm-7">
            <label class="form-label small fw-semibold">Nomor Tiket</label>
            <input type="text" name="ticket_number" class="form-control text-uppercase" placeholder="TKT-20261012-0001"
                value="{{ old('ticket_number', isset($ticket) ? $ticket->ticket_number : '') }}" required maxlength="30">
        </div>
        <div class="col-sm-5">
            <label class="form-label small fw-semibold">Kode Akses</label>
            <input type="text" name="token" class="form-control text-uppercase font-monospace" placeholder="8 karakter"
                autocomplete="off" required maxlength="16">
        </div>
    </div>
    <button type="submit" class="btn btn-primary w-100 mt-3"><i class="fas fa-search me-1"></i>Cek Status</button>
</form>

@isset($ticket)
<div class="bg-light border rounded-3 p-3 mb-3">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="fw-bold text-primary">{{ $ticket->ticket_number }}</span>
        <span class="badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
    </div>
    <div class="fw-semibold mb-2">{{ $ticket->title }}</div>
    <table class="table table-sm small mb-0">
        <tr><td class="text-muted ps-0">Pemohon</td><td class="text-end pe-0">{{ $ticket->requester_name }} ({{ $ticket->requester_department }})</td></tr>
        <tr><td class="text-muted ps-0">Kategori</td><td class="text-end pe-0">{{ $ticket->category->name ?? '-' }}</td></tr>
        <tr><td class="text-muted ps-0">Prioritas</td><td class="text-end pe-0"><span class="badge {{ $ticket->priority_badge_class }}">{{ $ticket->priority_label }}</span></td></tr>
        <tr><td class="text-muted ps-0">Diajukan</td><td class="text-end pe-0">{{ $ticket->created_at->format('d M Y, H:i') }} WIB</td></tr>
        <tr><td class="text-muted ps-0">Ditangani oleh</td><td class="text-end pe-0">{{ $ticket->assignedUser->name ?? 'Belum ditugaskan' }}</td></tr>
        @if($ticket->resolved_at)
        <tr><td class="text-muted ps-0">Diselesaikan</td><td class="text-end pe-0">{{ $ticket->resolved_at->format('d M Y, H:i') }} WIB</td></tr>
        @endif
    </table>
</div>
@endisset

<a href="{{ route('tickets.public.form') }}" class="btn btn-outline-primary w-100">
    <i class="fas fa-plus me-1"></i>Ajukan Permintaan Baru
</a>
@endsection
