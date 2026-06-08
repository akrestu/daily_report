@extends('layouts.public')

@section('title', 'Form Request Bantuan')

@section('content')
<div class="mb-3">
    <h5 class="fw-bold text-dark mb-1">
        <i class="fas fa-ticket-alt text-primary me-2"></i>Form Request Bantuan / Work Order
    </h5>
    <p class="text-muted small mb-0">Isi form di bawah untuk mengajukan permintaan bantuan. Tim kami akan segera menindaklanjuti.</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
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
    <p class="section-title"><i class="fas fa-user me-1"></i>Informasi Pemohon</p>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="requester_name" class="form-control @error('requester_name') is-invalid @enderror"
                placeholder="Masukkan nama lengkap Anda" value="{{ old('requester_name') }}" required>
            @error('requester_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">NIK / ID Karyawan</label>
            <input type="text" name="requester_employee_id" class="form-control @error('requester_employee_id') is-invalid @enderror"
                placeholder="Opsional" value="{{ old('requester_employee_id') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Departemen / Divisi <span class="text-danger">*</span></label>
            <input type="text" name="requester_department" class="form-control @error('requester_department') is-invalid @enderror"
                placeholder="Contoh: IT, HR, Finance..." value="{{ old('requester_department') }}" required>
            @error('requester_department')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">No. HP / WhatsApp</label>
            <input type="text" name="requester_phone" class="form-control @error('requester_phone') is-invalid @enderror"
                placeholder="Untuk follow-up (opsional)" value="{{ old('requester_phone') }}">
        </div>
        @if($jobSites->count() > 0)
        <div class="col-md-12">
            <label class="form-label fw-semibold small">Lokasi / Site</label>
            <select name="job_site_id" class="form-select @error('job_site_id') is-invalid @enderror">
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

    {{-- Detail Permintaan --}}
    <p class="section-title"><i class="fas fa-clipboard-list me-1"></i>Detail Permintaan</p>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Kategori Bantuan <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">— Pilih Kategori —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Prioritas <span class="text-danger">*</span></label>
            <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                <option value="low"    {{ old('priority') == 'low'    ? 'selected' : '' }}>Rendah (Low)</option>
                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                <option value="high"   {{ old('priority') == 'high'   ? 'selected' : '' }}>Tinggi (High)</option>
                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Mendesak (Urgent)</option>
            </select>
            @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold small">Judul Permintaan <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                placeholder="Ringkasan singkat permintaan Anda..." value="{{ old('title') }}" required maxlength="255">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold small">Deskripsi Detail <span class="text-danger">*</span></label>
            <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror"
                placeholder="Jelaskan secara detail apa yang dibutuhkan, lokasi, kondisi, atau informasi lain yang relevan..." required>{{ old('description') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold small">Lampiran <span class="text-muted fw-normal">(opsional, maks. 5 MB)</span></label>
            <input type="file" name="attachment" id="attachment"
                class="form-control @error('attachment') is-invalid @enderror"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx">
            <div class="form-text text-muted">Format: JPG, PNG, PDF, Word, Excel</div>
            @error('attachment')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg fw-semibold" id="submitBtn">
            <i class="fas fa-paper-plane me-2"></i>Kirim Permintaan
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('ticketForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
    });
</script>
@endpush
