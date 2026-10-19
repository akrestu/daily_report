{{--
    Isian permintaan tiket (Buat Tiket internal). Butuh di parent: $categories, $supervisors.
    Prioritas dihitung otomatis dari dampak × urgensi (Ticket::PRIORITY_MATRIX), sama dengan form publik.
--}}
<div x-data="{
        type: @js(old('type', 'incident')),
        category: @js((string) old('category_id', '')),
        impact: @js(old('impact', '')),
        urgency: @js(old('urgency', '')),
        approvalIds: @js($categories->where('requires_approval', true)->pluck('id')->map(fn ($id) => (string) $id)->values()),
        matrix: @js(\App\Models\Ticket::PRIORITY_MATRIX),
        priorities: @js(config('sigap-ui.priority')),
        get needsApproval() { return this.approvalIds.includes(this.category); },
        get priority() { return this.impact && this.urgency ? this.matrix[this.impact][this.urgency] : null; },
     }">
    <h2 class="sg-section-label">Detail permintaan</h2>

    <fieldset class="mb-3">
        <legend class="form-label fw-semibold fs-6">Jenis tiket <span class="text-danger">*</span></legend>
        <div class="sg-segment" role="radiogroup">
            @foreach (\App\Models\Ticket::TYPES as $value => $label)
                <input type="radio" class="btn-check" name="type" id="type-{{ $value }}" value="{{ $value }}" x-model="type" autocomplete="off" required>
                <label class="sg-segment__item" for="type-{{ $value }}">
                    <i class="bi {{ $value === 'incident' ? 'bi-exclamation-triangle' : 'bi-box-seam' }}" aria-hidden="true"></i>{{ $value === 'incident' ? 'Gangguan' : 'Permintaan' }}
                </label>
            @endforeach
        </div>
        <div class="small text-muted mt-1" x-text="type === 'incident' ? 'Sesuatu rusak atau tidak berfungsi.' : 'Minta perangkat, akses, atau instalasi.'"></div>
        @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </fieldset>

    <div class="mb-3">
        <label for="t-category" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
        <select name="category_id" id="t-category" x-model="category" required class="form-select @error('category_id') is-invalid @enderror">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}{{ $cat->requires_approval ? ' (perlu persetujuan atasan)' : '' }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="row g-3 mb-2">
        <div class="col-md-6">
            <label for="t-impact" class="form-label fw-semibold">Siapa yang terdampak? <span class="text-danger">*</span></label>
            <select id="t-impact" name="impact" x-model="impact" required class="form-select @error('impact') is-invalid @enderror">
                <option value="">Pilih dampak</option>
                @foreach (\App\Models\Ticket::IMPACTS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('impact')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="t-urgency" class="form-label fw-semibold">Seberapa mendesak? <span class="text-danger">*</span></label>
            <select id="t-urgency" name="urgency" x-model="urgency" required class="form-select @error('urgency') is-invalid @enderror">
                <option value="">Pilih urgensi</option>
                @foreach (\App\Models\Ticket::URGENCIES as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('urgency')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="mb-3" aria-live="polite">
        <div x-show="priority" x-cloak>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Prioritas otomatis:</span>
                <span class="sg-priority" :class="priority && priorities[priority].class" x-text="priority && (priorities[priority].code + ' ' + priorities[priority].label)"></span>
            </div>
        </div>
        <div class="small text-muted" x-show="!priority">Prioritas dan target waktu penanganan ditentukan otomatis dari dampak dan urgensi.</div>
    </div>

    {{-- Persetujuan atasan (kategori tertentu) --}}
    <div x-show="needsApproval" x-cloak>
        <div class="sg-internal-note p-3 mb-2 d-flex gap-2">
            <i class="bi bi-person-badge fs-5" aria-hidden="true"></i>
            <div class="small">Kategori ini perlu <strong>persetujuan atasan</strong>. Setelah tiket dibuat, cetak formulir persetujuan,
                minta tanda tangan atasan, lalu unggah foto formulir di halaman tiket.</div>
        </div>
        <div class="mb-3">
            <label for="t-supervisor" class="form-label fw-semibold">Atasan yang menyetujui <span class="text-danger">*</span></label>
            <select name="supervisor_id" id="t-supervisor" :required="needsApproval" class="form-select @error('supervisor_id') is-invalid @enderror">
                <option value="">Pilih atasan</option>
                @foreach (($supervisors ?? []) as $sup)
                    <option value="{{ $sup->id }}" @selected(old('supervisor_id') == $sup->id)>{{ $sup->name }}{{ $sup->position ? ' — '.$sup->position : '' }}</option>
                @endforeach
            </select>
            @error('supervisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Daftar karyawan aktif di departemen Anda.</div>
        </div>
    </div>

    <div class="mb-3">
        <label for="t-title" class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
        <input id="t-title" type="text" name="title" required maxlength="255" placeholder="Mis. Printer lantai 2 tidak bisa mencetak"
               class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label for="t-description" class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
        <textarea id="t-description" name="description" rows="5" required
                  placeholder="Apa yang terjadi atau dibutuhkan, lokasi, dan sejak kapan"
                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Lampiran tunggal (field "attachment"), foto dikompres di klien --}}
    <div class="sg-slot border-0 pt-0" x-data="sgSlot({ maxMb: 5 })">
        <input type="file" name="attachment" x-ref="field" class="d-none" tabindex="-1" aria-hidden="true">
        <input type="file" accept="image/*" capture="environment" x-ref="camera" class="d-none" tabindex="-1" aria-hidden="true" @change="pick($event)">
        <input type="file" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx" x-ref="gallery" class="d-none" tabindex="-1" aria-hidden="true" @change="pick($event)">
        <div class="sg-slot__thumb" aria-hidden="true">
            <template x-if="preview"><img :src="preview" alt=""></template>
            <template x-if="!preview"><i class="bi bi-paperclip"></i></template>
        </div>
        <div class="min-w-0 flex-grow-1">
            <div class="fw-semibold">Lampiran <span class="text-muted fw-normal small">(opsional, maks. 5 MB)</span></div>
            <div class="small sg-clamp-1" :class="error ? 'text-danger' : 'text-muted'"
                 x-text="busy ? 'Menyiapkan foto…' : error || picked || 'Foto, PDF, Word, atau Excel'"></div>
            @error('attachment')<div class="small text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="sg-slot__actions">
            <button type="button" class="btn btn-outline-secondary btn-icon" @click="$refs.camera.click()" aria-label="Ambil foto lampiran"><i class="bi bi-camera" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-outline-secondary btn-icon" @click="$refs.gallery.click()" aria-label="Pilih file lampiran"><i class="bi bi-images" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-quiet btn-icon" x-show="picked" x-cloak @click="undo()" aria-label="Hapus lampiran"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
    </div>
    @include('tickets.partials.held-attachment')
</div>
