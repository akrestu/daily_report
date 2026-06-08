@extends('layouts.public')

@section('title', 'Permintaan Berhasil Dikirim')

@section('content')
<div class="text-center py-2">
    <div class="mb-3">
        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle"
            style="width: 72px; height: 72px; font-size: 2rem;">
            <i class="fas fa-check"></i>
        </div>
    </div>

    <h5 class="fw-bold text-success mb-1">Permintaan Berhasil Dikirim!</h5>
    <p class="text-muted small mb-4">Tim kami akan segera menindaklanjuti permintaan Anda.</p>

    @if($ticket)
    <div class="bg-light border rounded-3 p-3 mb-4 text-start">
        <div class="row g-2">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted small">Nomor Tiket</span>
                    <span class="fw-bold text-primary fs-6">{{ $ticket->ticket_number }}</span>
                </div>
            </div>
            <div class="col-12"><hr class="my-1"></div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Judul</span>
                    <span class="fw-semibold small text-end" style="max-width: 65%">{{ $ticket->title }}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Kategori</span>
                    <span class="small">{{ $ticket->category->name ?? '-' }}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Prioritas</span>
                    <span class="badge {{ $ticket->priority_badge_class }}">{{ $ticket->priority_label }}</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Status</span>
                    <span class="badge bg-primary">Terbuka</span>
                </div>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Tanggal</span>
                    <span class="small">{{ $ticket->created_at->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info small text-start mb-4">
        <i class="fas fa-info-circle me-1"></i>
        Simpan nomor tiket <strong>{{ $ticket->ticket_number }}</strong> untuk referensi Anda.
        @if($ticket->requester_phone)
            Tim kami akan menghubungi Anda melalui nomor <strong>{{ $ticket->requester_phone }}</strong> jika diperlukan.
        @endif
    </div>
    @else
    <div class="alert alert-warning small mb-4">
        Nomor tiket: <strong>{{ $ticketNumber }}</strong>
    </div>
    @endif

    <a href="{{ route('tickets.public.form') }}" class="btn btn-outline-primary">
        <i class="fas fa-plus me-1"></i>Ajukan Permintaan Lain
    </a>
</div>
@endsection
