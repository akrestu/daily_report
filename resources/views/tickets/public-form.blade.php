@extends('layouts.public')

@section('title', 'Ajukan Tiket IT')

{{-- Form tiket tanpa login, stepper 3 langkah — mockup: TicketFormStep1, TicketFormMobile, TicketFormDesktop.
     Semua field tetap satu form; validasi tetap di server (StoreTicketRequest). --}}
@php
    $stepOf = ['nik' => 1, 'requester_phone' => 1, 'job_site_id' => 1, 'type' => 2, 'category_id' => 2, 'impact' => 2, 'urgency' => 2,
               'supervisor_id' => 2, 'title' => 3, 'description' => 3, 'attachment' => 3];
    $firstErrorStep = collect($errors->keys())->map(fn ($k) => $stepOf[$k] ?? 3)->min() ?? 1;
    $config = [
        'step' => $errors->any() ? $firstErrorStep : 1,
        'nik' => old('nik', ''),
        'categories' => $categories->map(fn ($c) => ['id' => (string) $c->id, 'name' => $c->name, 'approval' => (bool) $c->requires_approval])->values(),
        'category' => (string) old('category_id', ''),
        'impact' => old('impact', ''),
        'urgency' => old('urgency', ''),
        'type' => old('type', 'incident'),
        'supervisor' => (string) old('supervisor_id', ''),
        'site' => (string) old('job_site_id', ''),
        'matrix' => \App\Models\Ticket::PRIORITY_MATRIX,
        'priorities' => config('sigap-ui.priority'),
        'lookupUrl' => route('tickets.public.employee'),
    ];
@endphp

@section('content')
<div x-data="sgPublicTicket(@js($config))" x-init="if (nik) check()">
    <div class="mb-3">
        <h1 class="h4 fw-bold mb-1">Ajukan Tiket IT</h1>
        <p class="text-muted small mb-0">Tanpa login. Sudah punya tiket? <a href="{{ route('tickets.public.track') }}">Lacak status</a></p>
    </div>

    {{-- Stepper --}}
    <ol class="sg-stepper" aria-label="Langkah pengajuan">
        @foreach ([1 => 'Identitas', 2 => 'Permintaan', 3 => 'Detail'] as $n => $label)
            <li :class="{ 'is-done': step > {{ $n }}, 'is-current': step === {{ $n }} }" :aria-current="step === {{ $n }} ? 'step' : null">
                <span class="sg-stepper__dot">
                    <i class="bi bi-check-lg" x-show="step > {{ $n }}" x-cloak aria-hidden="true"></i><span x-show="step <= {{ $n }}">{{ $n }}</span>
                </span>
                <span class="sg-stepper__label">{{ $label }}</span>
            </li>
        @endforeach
    </ol>

    @if ($errors->any())
        <div class="alert alert-danger small" role="alert"><strong>Periksa kembali isian yang ditandai.</strong> Lampiran perlu dipilih ulang.</div>
    @endif

    <form action="{{ route('tickets.public.store') }}" method="POST" enctype="multipart/form-data" @submit="busy = true" novalidate>
        @csrf

        {{-- Langkah 1: identitas --}}
        <section x-show="step === 1" aria-labelledby="s1">
            <h2 id="s1" class="section-title">Identitas pemohon</h2>
            <label for="nik" class="form-label fw-semibold">NIK karyawan <span class="text-danger">*</span></label>
            <div class="d-flex gap-2">
                <input type="text" id="nik" name="nik" inputmode="numeric" autocomplete="off" maxlength="30" required
                       class="form-control" @error('nik') :class="{ 'is-invalid': !employee }" @enderror x-model="nik" @blur="check()" @input="employee = null; message = ''"
                       aria-describedby="nik-help">
                <button type="button" class="btn btn-outline-secondary flex-none" @click="check()" :disabled="checking">
                    <span x-show="!checking">Cek</span><span x-show="checking" x-cloak class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                </button>
            </div>
            {{-- Pesan server disembunyikan setelah NIK lolos cek ulang agar tidak bertentangan dengan "NIK terverifikasi" --}}
            @error('nik')<div class="invalid-feedback d-block" x-show="!employee">{{ $message }}</div>@enderror
            <div id="nik-help" class="form-text" :class="{ 'text-danger': message && !employee }" x-text="message || 'NIK dipakai untuk memastikan Anda karyawan aktif.'"></div>

            <template x-if="employee">
                <div class="sg-status-card sg-status-card--approved d-flex align-items-center gap-3 mt-3">
                    <span class="sg-avatar" style="width:44px;height:44px;font-size:16px" x-text="employee.name.split(' ').map(w => w[0]).slice(0, 2).join('')"></span>
                    <span><strong class="d-block" x-text="employee.name"></strong><span class="small" x-text="employee.department || '-'"></span></span>
                    <i class="bi bi-patch-check-fill ms-auto fs-4" style="color:var(--sg-success-dot)" aria-label="Terverifikasi"></i>
                </div>
            </template>

            <div class="row g-3 mt-1">
                <div class="col-sm-6">
                    <label for="phone" class="form-label fw-semibold">No. HP / WhatsApp</label>
                    <input type="tel" id="phone" name="requester_phone" class="form-control @error('requester_phone') is-invalid @enderror"
                           value="{{ old('requester_phone') }}" placeholder="Untuk kabar status tiket" autocomplete="tel">
                    @error('requester_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @if ($jobSites->count())
                    <div class="col-sm-6">
                        <label for="site" class="form-label fw-semibold">Lokasi / site</label>
                        <select id="site" name="job_site_id" class="form-select" x-model="site">
                            <option value="">Pilih lokasi (opsional)</option>
                            @foreach ($jobSites as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}{{ $s->location ? ' — '.$s->location : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </section>

        {{-- Langkah 2: permintaan --}}
        <section x-show="step === 2" x-cloak aria-labelledby="s2">
            <h2 id="s2" class="section-title">Jenis permintaan</h2>
            <div class="sg-segment mb-3" style="grid-template-columns:1fr 1fr" role="radiogroup" aria-label="Jenis tiket">
                @foreach (\App\Models\Ticket::TYPES as $value => $label)
                    <input type="radio" class="btn-check" name="type" id="type-{{ $value }}" value="{{ $value }}" x-model="type">
                    <label class="sg-segment__item flex-column" for="type-{{ $value }}">
                        <i class="bi {{ $value === 'incident' ? 'bi-exclamation-octagon' : 'bi-box-seam' }}" aria-hidden="true"></i>{{ $label }}
                    </label>
                @endforeach
            </div>

            <p class="form-label fw-semibold mb-2">Kategori <span class="text-danger">*</span></p>
            <div class="sg-option-grid mb-1" role="radiogroup" aria-label="Kategori">
                <template x-for="c in categories" :key="c.id">
                    <label class="sg-option" :class="{ active: category === c.id }">
                        <input type="radio" name="category_id" class="visually-hidden" :value="c.id" x-model="category">
                        <span class="fw-semibold" x-text="c.name"></span>
                        <span class="small text-muted" x-show="c.approval" x-cloak>Perlu persetujuan atasan</span>
                    </label>
                </template>
            </div>
            @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

            <div class="row g-3 mt-1">
                <div class="col-sm-6">
                    <label for="impact" class="form-label fw-semibold">Siapa yang terdampak? <span class="text-danger">*</span></label>
                    <select id="impact" name="impact" class="form-select @error('impact') is-invalid @enderror" x-model="impact">
                        <option value="">Pilih dampak</option>
                        @foreach (\App\Models\Ticket::IMPACTS as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label for="urgency" class="form-label fw-semibold">Seberapa mendesak? <span class="text-danger">*</span></label>
                    <select id="urgency" name="urgency" class="form-select @error('urgency') is-invalid @enderror" x-model="urgency">
                        <option value="">Pilih urgensi</option>
                        @foreach (\App\Models\Ticket::URGENCIES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
            </div>

            <div aria-live="polite">
                <div class="d-flex align-items-center gap-2 mt-3" x-show="priority" x-cloak>
                    <span class="small text-muted">Prioritas otomatis:</span>
                    <span class="sg-priority" :class="priority && priorities[priority].class" x-text="priority && (priorities[priority].code + ' ' + priorities[priority].label)"></span>
                </div>
            </div>

            <div class="mt-3" x-show="needsApproval" x-cloak>
                <div class="alert alert-warning small mb-2">Kategori ini perlu <strong>persetujuan atasan</strong>. Setelah tiket dibuat, cetak formulir, minta tanda tangan, lalu unggah fotonya.</div>
                <label for="supervisor" class="form-label fw-semibold">Atasan yang menyetujui <span class="text-danger">*</span></label>
                <select id="supervisor" name="supervisor_id" class="form-select @error('supervisor_id') is-invalid @enderror" x-model="supervisor" :required="needsApproval">
                    <option value="">Pilih atasan</option>
                    <template x-for="s in supervisors" :key="s.id"><option :value="s.id" x-text="s.name + (s.position ? ' — ' + s.position : '')"></option></template>
                </select>
                @error('supervisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </section>

        {{-- Langkah 3: detail --}}
        <section x-show="step === 3" x-cloak aria-labelledby="s3">
            <h2 id="s3" class="section-title">Detail permintaan</h2>
            <label for="title" class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
            <input type="text" id="title" name="title" maxlength="255" class="form-control mb-3 @error('title') is-invalid @enderror"
                   value="{{ old('title') }}" placeholder="Mis. Printer lantai 2 tidak bisa mencetak">
            @error('title')<div class="invalid-feedback mb-2">{{ $message }}</div>@enderror
            <label for="description" class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
            <textarea id="description" name="description" rows="5" class="form-control mb-3 @error('description') is-invalid @enderror"
                      placeholder="Apa yang terjadi, sejak kapan, lokasi, dan informasi lain yang membantu">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback mb-2">{{ $message }}</div>@enderror
            <label for="attachment" class="form-label fw-semibold">Lampiran <span class="text-muted fw-normal">(opsional, maks. 5 MB)</span></label>
            <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx"
                   class="form-control @error('attachment') is-invalid @enderror">
            @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror

            <div class="sg-status-card mt-3 small">
                <div class="sg-status-card__label">Ringkasan</div>
                <div><span x-text="employee ? employee.name : nik"></span> · <span x-text="(categories.find(c => c.id === category) || {}).name || '-'"></span></div>
                <div x-show="priority" x-cloak>Prioritas: <strong x-text="priority && (priorities[priority].code + ' ' + priorities[priority].label)"></strong></div>
            </div>
        </section>

        {{-- Navigasi langkah (menempel di bawah pada mobile) --}}
        <div class="sg-public-actions">
            <button type="button" class="btn btn-outline-secondary" x-show="step > 1" x-cloak @click="step--">Kembali</button>
            <button type="button" class="btn btn-primary flex-fill" x-show="step < 3" @click="next()" :disabled="!canNext">Lanjut</button>
            <button type="submit" class="btn btn-primary flex-fill" x-show="step === 3" x-cloak :disabled="busy">
                <span x-text="busy ? 'Mengirim…' : 'Kirim Tiket'">Kirim Tiket</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function sgPublicTicket(cfg) {
        return {
            ...cfg, employee: null, supervisors: [], message: '', checking: false, busy: false, lastChecked: null,
            get priority() { return this.impact && this.urgency ? this.matrix[this.impact][this.urgency] : null; },
            get needsApproval() { return !!(this.categories.find((c) => c.id === this.category) || {}).approval; },
            get canNext() {
                if (this.step === 1) return !!this.employee;
                if (this.step === 2) return this.category && this.impact && this.urgency && (!this.needsApproval || this.supervisor);
                return true;
            },
            next() { if (this.canNext) { this.step++; window.scrollTo({ top: 0, behavior: 'smooth' }); } },
            async check() {
                const nik = this.nik.trim();
                if (!nik || nik === this.lastChecked) return;
                this.lastChecked = nik; this.checking = true; this.message = 'Memeriksa NIK…';
                try {
                    const res = await fetch(this.lookupUrl + '?nik=' + encodeURIComponent(nik), { headers: { Accept: 'application/json' } });
                    const data = await res.json();
                    if (res.ok && data.found) {
                        this.employee = data; this.supervisors = data.supervisors || []; this.message = 'NIK terverifikasi.';
                        if (data.job_site_id && !this.site) this.site = String(data.job_site_id);
                    } else {
                        this.employee = null;
                        this.message = res.status === 429 ? 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.' : (data.message || 'NIK tidak terdaftar.');
                    }
                } catch (e) {
                    this.message = 'Gagal memeriksa NIK. Periksa koneksi Anda.'; this.lastChecked = null;
                } finally { this.checking = false; }
            },
        };
    }
</script>
@endpush
@endsection
