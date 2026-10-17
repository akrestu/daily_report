@extends('layouts.public')

@section('title', 'Cek Status Tiket')

@section('content')
<div class="mb-3">
    <h5 class="fw-bold text-dark mb-1">
        <i class="bi bi-search text-primary me-2"></i>Cek Status Tiket
    </h5>
    <p class="text-muted small mb-0">Masukkan nomor tiket dan kode akses yang Anda terima saat mengajukan permintaan.</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger small mb-3">
        <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
    </div>
@endif

<form action="{{ route('tickets.public.track.lookup') }}" method="POST" class="mb-4">
    @csrf
    <div class="row g-2">
        <div class="col-sm-7">
            <label class="form-label small fw-semibold">Nomor Tiket</label>
            <input type="text" name="ticket_number" class="form-control text-uppercase" placeholder="TKT-20261012-0001"
                value="{{ old('ticket_number') }}" required maxlength="30">
        </div>
        <div class="col-sm-5">
            <label class="form-label small fw-semibold">Kode Akses</label>
            <input type="text" name="token" class="form-control text-uppercase font-monospace" placeholder="8 karakter"
                autocomplete="off" required maxlength="16">
        </div>
    </div>
    <button type="submit" class="btn btn-primary w-100 mt-3"><i class="bi bi-search me-1"></i>Cek Status</button>
</form>


<a href="{{ route('tickets.public.form') }}" class="btn btn-outline-primary w-100">
    <i class="bi bi-plus-lg me-1"></i>Ajukan Permintaan Baru
</a>
@endsection
