@extends('layouts.public')

@section('title', 'Tiket '.$ticket->ticket_number)

@section('content')
@if(session('success'))
    <div class="alert alert-success small"><i class="fas fa-check-circle me-1"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger small"><i class="fas fa-exclamation-circle me-1"></i>{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger small"><i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
@endif

<div class="d-flex justify-content-between align-items-start mb-2">
    <div>
        <div class="fw-bold text-primary">{{ $ticket->ticket_number }}</div>
        <h5 class="fw-bold mb-0">{{ $ticket->title }}</h5>
    </div>
    <span class="badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
</div>

<table class="table table-sm small mb-3">
    <tr><td class="text-muted ps-0">Pemohon</td><td class="text-end pe-0">{{ $ticket->requester_name }} ({{ $ticket->requester_department }})</td></tr>
    <tr><td class="text-muted ps-0">Jenis / Kategori</td><td class="text-end pe-0">{{ $ticket->type_label }} &bull; {{ $ticket->category->name ?? '-' }}</td></tr>
    <tr><td class="text-muted ps-0">Prioritas</td><td class="text-end pe-0"><span class="badge {{ $ticket->priority_badge_class }}">{{ $ticket->priority_label }}</span></td></tr>
    <tr><td class="text-muted ps-0">Diajukan</td><td class="text-end pe-0">{{ $ticket->created_at->format('d M Y, H:i') }} WIB</td></tr>
    <tr><td class="text-muted ps-0">Ditangani oleh</td><td class="text-end pe-0">{{ $ticket->assignedUser->name ?? 'Belum ditugaskan' }}</td></tr>
</table>

@if($ticket->approval_status)
    @include('tickets.partials.approval-card', [
        'printUrl' => route('tickets.public.approval.form', $ticket),
        'proofUrl' => route('tickets.public.approval.proof', $ticket),
        'uploadUrl' => route('tickets.public.approval.upload', $ticket),
        'verifyUrl' => null,
    ])
@endif

@include('tickets.partials.requester-actions', ['action' => route('tickets.public.respond', $ticket), 'csatAction' => route('tickets.public.csat', $ticket)])

@if($ticket->status === \App\Models\Ticket::STATUS_PENDING)
    <div class="alert alert-warning small">
        <i class="fas fa-hourglass-half me-1"></i>
        Tim IT menunggu informasi dari Anda. Lihat catatan di riwayat di bawah, lalu balas melalui kolom komentar.
    </div>
@endif

<p class="section-title mt-3"><i class="fas fa-history me-1"></i>Riwayat</p>
<div class="mb-4">
    @include('tickets.partials.timeline')
</div>

<p class="section-title"><i class="fas fa-comments me-1"></i>Komentar</p>
@include('tickets.partials.comments', ['comments' => $ticket->comments])

@if($ticket->isActive())
<form action="{{ route('tickets.public.comment', $ticket) }}" method="POST" class="mt-2 mb-4">
    @csrf
    <textarea name="comment" rows="3" class="form-control form-control-sm mb-2" required maxlength="2000"
        placeholder="Tulis pesan untuk tim IT...">{{ old('comment') }}</textarea>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane me-1"></i>Kirim</button>
</form>
@endif

<a href="{{ route('tickets.public.track') }}" class="btn btn-outline-secondary btn-sm">
    <i class="fas fa-search me-1"></i>Cek Tiket Lain
</a>
@endsection
