@extends('layouts.public')

@section('title', 'Permintaan Berhasil Dikirim')

@section('content')
<div class="text-center py-2">
    <div class="mb-3">
        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle"
            style="width: 72px; height: 72px; font-size: 2rem;">
            <i class="bi bi-check-lg"></i>
        </div>
    </div>

    <h5 class="fw-bold text-success mb-1">Permintaan Berhasil Dikirim!</h5>
    <p class="text-muted small mb-4">Tim kami akan segera menindaklanjuti permintaan Anda.</p>

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
                    <span class="badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
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

    <div class="border border-warning rounded-3 p-3 mb-3 text-start" style="background:#fff8e1">
        <div class="fw-semibold small mb-2"><i class="bi bi-key me-1 text-warning"></i>Simpan data berikut untuk cek status tiket</div>
        <div class="d-flex justify-content-between small"><span class="text-muted">Nomor Tiket</span><strong>{{ $ticket->ticket_number }}</strong></div>
        <div class="d-flex justify-content-between small"><span class="text-muted">Kode Akses</span><strong class="font-monospace fs-6 text-danger">{{ $ticket->tracking_token }}</strong></div>
        <div class="text-muted mt-2" style="font-size:.75rem">Kode akses hanya ditampilkan sekali. Catat atau screenshot halaman ini.</div>
    </div>

    @if($ticket->approval_status)
    <div class="alert alert-warning small text-start mb-3">
        <i class="bi bi-person-badge me-1"></i>
        Permintaan ini memerlukan <strong>persetujuan atasan</strong>. Buka <strong>Cek Status Tiket</strong> untuk mencetak formulir,
        minta tanda tangan atasan, lalu unggah fotonya. Tiket baru akan dikerjakan setelah persetujuan diverifikasi tim IT.
    </div>
    @endif

    @if($ticket->requester_phone)
    <div class="alert alert-info small text-start mb-4">
        <i class="bi bi-info-circle me-1"></i>
        Tim kami akan menghubungi Anda melalui nomor <strong>{{ $ticket->requester_phone }}</strong> jika diperlukan.
    </div>
    @endif

    <a href="{{ route('tickets.public.track') }}" class="btn btn-primary me-1">
        <i class="bi bi-search me-1"></i>Cek Status Tiket
    </a>
    <a href="{{ route('tickets.public.form') }}" class="btn btn-outline-primary">
        <i class="bi bi-plus-lg me-1"></i>Ajukan Permintaan Lain
    </a>
</div>
@endsection
