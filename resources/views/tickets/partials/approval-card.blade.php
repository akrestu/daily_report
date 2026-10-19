{{--
    Persetujuan atasan. Variabel:
    $printUrl, $proofUrl, $uploadUrl (null jika user tidak boleh upload), $verifyUrl (null jika tidak boleh verifikasi)
--}}
<div class="card shadow-sm mb-3 border-{{ $ticket->approval_status === 'approved' ? 'success' : ($ticket->approval_status === 'rejected' ? 'danger' : 'warning') }}">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <h6 class="fw-bold mb-0"><i class="bi bi-person-badge me-1"></i>Persetujuan Atasan</h6>
            <span class="badge {{ $ticket->approval_badge_class }}">{{ $ticket->approval_label }}</span>
        </div>

        <div class="small mb-2">
            Atasan: <strong>{{ $ticket->supervisor->name ?? '-' }}</strong>
            @if($ticket->supervisor?->position) ({{ $ticket->supervisor->position }}) @endif
        </div>

        @if($ticket->approval_status === 'rejected' && $ticket->approval_note)
            <div class="alert alert-danger small py-2 mb-2"><strong>Alasan ditolak:</strong> {{ $ticket->approval_note }}</div>
        @endif

        <div class="d-flex flex-wrap gap-2 mb-2">
            <a href="{{ $printUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-printer me-1"></i>Cetak Formulir Persetujuan
            </a>
            @if($ticket->approval_proof_path)
                <a href="{{ $proofUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-file-earmark-text me-1"></i>Lihat Bukti
                </a>
            @endif
        </div>

        @if($uploadUrl && $ticket->canUploadApproval())
            <form action="{{ $uploadUrl }}" method="POST" enctype="multipart/form-data" class="border rounded p-2 bg-light">
                @csrf
                <label class="form-label small fw-semibold mb-1">
                    {{ $ticket->approval_status === 'rejected' ? 'Unggah ulang' : 'Unggah' }} foto/scan formulir yang sudah ditandatangani atasan
                </label>
                <div class="input-group input-group-sm">
                    <input type="file" name="approval_proof" class="form-control @error('approval_proof') is-invalid @enderror"
                        accept=".jpg,.jpeg,.png,.pdf" required>
                    <button type="submit" class="btn btn-primary">Kirim</button>
                </div>
                @error('approval_proof')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <div class="form-text">JPG, PNG, atau PDF. Maks. 5 MB.</div>
            </form>
        @endif

        @if($verifyUrl && $ticket->approval_status === 'waiting_verification')
            <div class="border rounded p-2 bg-light">
                <div class="small fw-semibold mb-2">Verifikasi bukti persetujuan</div>
                <form action="{{ $verifyUrl }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="decision" value="approve">
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Sah, setujui</button>
                </form>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#rejectApprovalForm">
                    <i class="bi bi-x-lg me-1"></i>Tolak
                </button>
                <form action="{{ $verifyUrl }}" method="POST" class="collapse mt-2" id="rejectApprovalForm">
                    @csrf
                    <input type="hidden" name="decision" value="reject">
                    <input type="text" name="note" class="form-control form-control-sm mb-2" required maxlength="1000"
                        placeholder="Alasan (mis. tanda tangan atasan tidak ada, foto buram)">
                    <button type="submit" class="btn btn-danger btn-sm">Tolak Bukti</button>
                </form>
            </div>
        @endif

        @if($ticket->approval_verified_at)
            <div class="text-muted small mt-2">
                Diverifikasi oleh {{ $ticket->approvalVerifier->name ?? '-' }} &bull; {{ $ticket->approval_verified_at->format('d M Y H:i') }}
            </div>
        @endif
    </div>
</div>
