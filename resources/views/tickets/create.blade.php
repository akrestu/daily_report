<x-app-layout>
    <x-slot name="header">Buat Tiket</x-slot>

    <style>
        .section-title { font-size: .8rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em;
            color: #6c757d; margin-bottom: .75rem; padding-bottom: .5rem; border-bottom: 1px solid #e9ecef; }
    </style>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-ticket-alt text-primary me-2"></i>Buat Tiket Layanan IT</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger small">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" id="ticketForm">
                        @csrf

                        <p class="section-title"><i class="fas fa-user me-1"></i>Pemohon</p>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nama</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Departemen</label>
                                <input type="text" class="form-control" value="{{ auth()->user()->department->name ?? '-' }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">No. HP / WhatsApp</label>
                                <input type="text" name="requester_phone" class="form-control" placeholder="Untuk follow-up (opsional)"
                                    value="{{ old('requester_phone') }}" maxlength="30">
                            </div>
                            @if($jobSites->count() > 0)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Lokasi / Site</label>
                                <select name="job_site_id" class="form-select">
                                    <option value="">— Pilih Lokasi (opsional) —</option>
                                    @foreach($jobSites as $site)
                                        <option value="{{ $site->id }}" {{ old('job_site_id', auth()->user()->job_site_id) == $site->id ? 'selected' : '' }}>
                                            {{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                        </div>

                        @include('tickets.partials.request-fields')

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('tickets.mine') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary fw-semibold" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('ticketForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
        });
    </script>
</x-app-layout>
