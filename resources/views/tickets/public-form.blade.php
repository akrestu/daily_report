@extends('layouts.public')

@section('title', 'Form Request Bantuan')

@section('content')
<div class="mb-3">
    <h5 class="fw-bold text-dark mb-1">
        <i class="bi bi-ticket-perforated text-primary me-2"></i>Form Request Bantuan / Work Order
    </h5>
    <p class="text-muted small mb-0">Isi form di bawah untuk mengajukan permintaan bantuan. Tim kami akan segera menindaklanjuti.</p>
    <p class="small mb-0 mt-1">Sudah punya tiket? <a href="{{ route('tickets.public.track') }}">Cek status tiket</a></p>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('tickets.public.store') }}" method="POST" enctype="multipart/form-data" id="ticketForm">
    @csrf

    {{-- Informasi Pemohon --}}
    <p class="section-title"><i class="bi bi-person me-1"></i>Informasi Pemohon</p>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">NIK Karyawan <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror"
                    placeholder="Masukkan NIK Anda" value="{{ old('nik') }}" required maxlength="30" autocomplete="off">
                <button type="button" class="btn btn-outline-primary" id="checkNikBtn">Cek</button>
                @error('nik')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-text" id="nikHelp">NIK digunakan untuk verifikasi identitas karyawan PT. WBK.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">No. HP / WhatsApp</label>
            <input type="text" name="requester_phone" class="form-control @error('requester_phone') is-invalid @enderror"
                placeholder="Untuk follow-up (opsional)" value="{{ old('requester_phone') }}">
        </div>
        <div class="col-12 d-none" id="employeeCard">
            <div class="alert alert-success small mb-0 py-2">
                <i class="bi bi-person-check me-1"></i>
                <strong id="employeeName"></strong> &mdash; <span id="employeeDept"></span>
            </div>
        </div>
        @if($jobSites->count() > 0)
        <div class="col-md-12">
            <label class="form-label fw-semibold small">Lokasi / Site</label>
            <select name="job_site_id" id="job_site_id" class="form-select @error('job_site_id') is-invalid @enderror">
                <option value="">— Pilih Lokasi (opsional) —</option>
                @foreach($jobSites as $site)
                    <option value="{{ $site->id }}" {{ old('job_site_id') == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}@if($site->location) — {{ $site->location }}@endif
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    @include('tickets.partials.request-fields')

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg fw-semibold" id="submitBtn">
            <i class="bi bi-send me-2"></i>Kirim Permintaan
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        const nikInput = document.getElementById('nik');
        const help = document.getElementById('nikHelp');
        const card = document.getElementById('employeeCard');
        const siteSelect = document.getElementById('job_site_id');
        let lastChecked = null;

        async function checkNik() {
            const nik = nikInput.value.trim();
            if (!nik || nik === lastChecked) return;
            lastChecked = nik;
            help.textContent = 'Memeriksa NIK...';
            help.className = 'form-text';
            card.classList.add('d-none');

            try {
                const res = await fetch('{{ route('tickets.public.employee') }}?nik=' + encodeURIComponent(nik), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (res.ok && data.found) {
                    document.getElementById('employeeName').textContent = data.name;
                    document.getElementById('employeeDept').textContent = data.department || '-';
                    card.classList.remove('d-none');
                    nikInput.classList.remove('is-invalid');
                    help.textContent = 'NIK terverifikasi.';
                    help.className = 'form-text text-success';
                    if (siteSelect && data.job_site_id && !siteSelect.value) siteSelect.value = data.job_site_id;
                    if (window.fillSupervisors) window.fillSupervisors(data.supervisors || []);
                } else {
                    help.textContent = res.status === 429
                        ? 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.'
                        : (data.message || 'NIK tidak terdaftar.');
                    help.className = 'form-text text-danger';
                }
            } catch (e) {
                help.textContent = 'Gagal memeriksa NIK. Periksa koneksi Anda.';
                help.className = 'form-text text-danger';
                lastChecked = null;
            }
        }

        document.getElementById('checkNikBtn').addEventListener('click', checkNik);
        nikInput.addEventListener('blur', checkNik);
        nikInput.addEventListener('input', () => { lastChecked = null; card.classList.add('d-none'); });
        if (nikInput.value) checkNik();

        document.getElementById('ticketForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        });
    })();
</script>
@endpush
