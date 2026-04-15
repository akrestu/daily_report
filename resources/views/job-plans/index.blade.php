<x-app-layout>
    <x-slot name="header">Job Plan Saya</x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Card -->
    <div class="card mb-4 border-0 bg-light rounded-3">
        <div class="card-body p-3">
            <form action="{{ route('job-plans.index') }}" method="GET" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-primary"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                placeholder="Cari nama pekerjaan" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                            <input type="date" name="date_from" class="form-control border-start-0 ps-0"
                                placeholder="Dari" value="{{ request('date_from', date('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                            <input type="date" name="date_to" class="form-control border-start-0 ps-0"
                                placeholder="Sampai" value="{{ request('date_to', date('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>⏳ Aktif</option>
                            <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>✅ Dikonversi</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-3 flex-grow-1">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('job-plans.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="fas fa-redo"></i>
                        </a>
                        <button type="button" class="btn btn-outline-success rounded-pill px-3" onclick="openShareWhatsApp()">
                            <i class="fab fa-whatsapp me-1"></i> WA
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Plans Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>Job Plan Saya
                    <span class="badge bg-primary ms-1">{{ $plans->total() }}</span>
                </h5>
                <a href="{{ route('job-plans.create') }}" class="btn btn-primary rounded-pill px-3">
                    <i class="fas fa-plus-circle me-1"></i> Buat Plan Baru
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($plans->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">Belum ada job plan yang dibuat.</p>
                    <a href="{{ route('job-plans.create') }}" class="btn btn-primary rounded-pill mt-3">
                        <i class="fas fa-plus-circle me-1"></i> Buat Job Plan Pertama
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Nama Pekerjaan</th>
                                <th>Ditugaskan ke</th>
                                <th>Section</th>
                                <th>Tgl Rencana</th>
                                <th>Tenggat</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $i => $plan)
                            <tr>
                                <td class="ps-3 text-muted small">{{ $plans->firstItem() + $i }}</td>
                                <td>
                                    <a href="{{ route('job-plans.show', $plan) }}" class="fw-semibold text-decoration-none">
                                        {{ $plan->job_name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="fas fa-user text-muted small"></i>
                                        {{ $plan->assignee->name ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $plan->section->name ?? '-' }}</td>
                                <td>{{ $plan->planned_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    @if($plan->due_date && $plan->due_date->isPast() && !$plan->isConverted())
                                        <span class="text-danger fw-semibold">{{ $plan->due_date->format('d/m/Y') }}</span>
                                    @else
                                        {{ $plan->due_date?->format('d/m/Y') ?? '-' }}
                                    @endif
                                </td>
                                <td>
                                    @if($plan->isConverted())
                                        <span class="badge bg-success rounded-pill">✅ Dikonversi</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill">⏳ Aktif</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('job-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $plan)
                                        <a href="{{ route('job-plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $plan)
                                        <form action="{{ route('job-plans.destroy', $plan) }}" method="POST"
                                            onsubmit="return confirm('Hapus job plan ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($plans->hasPages())
                <div class="px-3 py-3 border-top">
                    {{ $plans->withQueryString()->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>

    <!-- WhatsApp Share Modal -->
    <div class="modal fade" id="whatsappShareModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);">
                    <h5 class="modal-title text-white">
                        <i class="fab fa-whatsapp me-2"></i>Preview Pesan WhatsApp — Job Plans
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="waShareLoading" class="text-center py-4">
                        <div class="spinner-border text-success" role="status"></div>
                        <p class="mt-2 text-muted">Menyiapkan pesan...</p>
                    </div>
                    <div id="waShareContent" style="display:none;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="text-muted small fw-semibold">Format:</span>
                            <button type="button" id="waModeDetail" class="btn btn-sm btn-success rounded-pill px-3" onclick="setWaFormat('detail')">
                                <i class="fas fa-list me-1"></i>Detail
                            </button>
                            <button type="button" id="waModeRingkasan" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="setWaFormat('ringkasan')">
                                <i class="fas fa-align-left me-1"></i>Ringkasan
                            </button>
                        </div>
                        <div id="waShareLimitAlert" class="alert alert-warning border-0 py-2 mb-3" style="display:none;">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="waShareLimitText"></span>
                        </div>
                        <label class="form-label fw-semibold text-muted small mb-1">Preview Pesan:</label>
                        <textarea id="waShareText" class="form-control font-monospace" rows="12" readonly
                            style="font-size:0.78rem; background:#f8f9fa; resize:none; white-space:pre;"></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">Maks. 65.536 karakter</small>
                            <small id="waCharCount" class="fw-semibold text-muted">0 karakter</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-outline-dark rounded-pill px-4" id="waCopyBtn" onclick="copyWaText()" style="display:none;">
                        <i class="far fa-copy me-1"></i> Salin Teks
                    </button>
                    <a href="#" target="_blank" id="waOpenBtn" class="btn btn-success rounded-pill px-4" style="display:none;">
                        <i class="fab fa-whatsapp me-1"></i> Buka WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentWaFormat = 'detail';

        function openShareWhatsApp() {
            currentWaFormat = 'detail';
            updateWaFormatButtons();
            const modal = new bootstrap.Modal(document.getElementById('whatsappShareModal'));
            modal.show();
            fetchWaShare();
        }

        function setWaFormat(format) {
            if (format === currentWaFormat) return;
            currentWaFormat = format;
            updateWaFormatButtons();
            fetchWaShare();
        }

        function updateWaFormatButtons() {
            const detailBtn    = document.getElementById('waModeDetail');
            const ringkasanBtn = document.getElementById('waModeRingkasan');
            if (currentWaFormat === 'detail') {
                detailBtn.classList.add('btn-success'); detailBtn.classList.remove('btn-outline-success');
                ringkasanBtn.classList.add('btn-outline-success'); ringkasanBtn.classList.remove('btn-success');
            } else {
                ringkasanBtn.classList.add('btn-success'); ringkasanBtn.classList.remove('btn-outline-success');
                detailBtn.classList.add('btn-outline-success'); detailBtn.classList.remove('btn-success');
            }
        }

        function fetchWaShare() {
            document.getElementById('waShareLoading').innerHTML =
                '<div class="spinner-border text-success" role="status"></div><p class="mt-2 text-muted">Menyiapkan pesan...</p>';
            document.getElementById('waShareLoading').style.display = 'block';
            document.getElementById('waShareContent').style.display = 'none';
            document.getElementById('waCopyBtn').style.display = 'none';
            document.getElementById('waOpenBtn').style.display = 'none';

            const filterForm = document.getElementById('filterForm');
            const params = new URLSearchParams();
            if (filterForm) {
                ['search', 'date_from', 'date_to', 'status'].forEach(function(name) {
                    const el = filterForm.querySelector('[name="' + name + '"]');
                    if (el && el.value) params.set(name === 'status' ? 'status' : name, el.value);
                });
            }
            const today = new Date().toISOString().split('T')[0];
            if (!params.get('date_from')) params.set('date_from', today);
            if (!params.get('date_to'))   params.set('date_to', today);
            params.set('format', currentWaFormat);

            fetch('{{ route("job-plans.whatsapp-share") }}?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => { if (!res.ok) throw new Error('Gagal memuat data.'); return res.json(); })
            .then(function(data) {
                document.getElementById('waShareText').value = data.text;
                updateCharCount();
                if (data.limited) {
                    document.getElementById('waShareLimitText').textContent =
                        'Menampilkan 30 dari ' + data.count + ' rencana. Gunakan filter atau mode Ringkasan.';
                    document.getElementById('waShareLimitAlert').style.display = 'block';
                } else {
                    document.getElementById('waShareLimitAlert').style.display = 'none';
                }
                document.getElementById('waOpenBtn').href = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(data.text);
                document.getElementById('waShareLoading').style.display = 'none';
                document.getElementById('waShareContent').style.display = 'block';
                document.getElementById('waCopyBtn').style.display = 'inline-block';
                document.getElementById('waOpenBtn').style.display = 'inline-block';
            })
            .catch(function(err) {
                document.getElementById('waShareLoading').innerHTML =
                    '<div class="alert alert-danger border-0"><i class="fas fa-exclamation-circle me-2"></i>' + err.message + '</div>';
            });
        }

        function updateCharCount() {
            const count = document.getElementById('waShareText').value.length;
            const el    = document.getElementById('waCharCount');
            el.textContent = count.toLocaleString('id-ID') + ' / 65.536 karakter';
            el.className = count > 58000 ? 'fw-semibold text-danger' : count > 45000 ? 'fw-semibold text-warning' : 'fw-semibold text-success';
        }

        function copyWaText() {
            const ta = document.getElementById('waShareText');
            ta.select(); ta.setSelectionRange(0, 99999);
            try {
                navigator.clipboard.writeText(ta.value).then(showCopyFeedback).catch(function() { document.execCommand('copy'); showCopyFeedback(); });
            } catch(e) { document.execCommand('copy'); showCopyFeedback(); }
        }

        function showCopyFeedback() {
            const btn = document.getElementById('waCopyBtn');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Tersalin!';
            btn.classList.replace('btn-outline-dark', 'btn-success');
            setTimeout(function() { btn.innerHTML = orig; btn.classList.replace('btn-success', 'btn-outline-dark'); }, 2000);
        }

        window.openShareWhatsApp = openShareWhatsApp;
        window.setWaFormat = setWaFormat;
        window.copyWaText = copyWaText;
    </script>
    @endpush
</x-app-layout>
