    {{-- Detail Permintaan --}}
    <p class="section-title"><i class="fas fa-clipboard-list me-1"></i>Detail Permintaan</p>
    <div class="row g-3 mb-4">
        <div class="col-12">
            <label class="form-label fw-semibold small d-block">Jenis Tiket <span class="text-danger">*</span></label>
            @foreach(\App\Models\Ticket::TYPES as $value => $label)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="type" id="type_{{ $value }}" value="{{ $value }}"
                        {{ old('type', 'incident') === $value ? 'checked' : '' }} required>
                    <label class="form-check-label small" for="type_{{ $value }}">
                        {{ $label }}
                        <span class="text-muted">{{ $value === 'incident' ? '(sesuatu rusak / tidak berfungsi)' : '(minta perangkat, akses, instalasi)' }}</span>
                    </label>
                </div>
            @endforeach
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Kategori Bantuan <span class="text-danger">*</span></label>
            <select name="category_id" id="categorySelect" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">— Pilih Kategori —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" data-requires-approval="{{ $cat->requires_approval ? 1 : 0 }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}{{ $cat->requires_approval ? ' (perlu persetujuan atasan)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Siapa yang terdampak? <span class="text-danger">*</span></label>
            <select name="impact" class="form-select @error('impact') is-invalid @enderror" required>
                <option value="">— Pilih Dampak —</option>
                @foreach(\App\Models\Ticket::IMPACTS as $value => $label)
                    <option value="{{ $value }}" {{ old('impact') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('impact')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold small">Seberapa mendesak? <span class="text-danger">*</span></label>
            <select name="urgency" class="form-select @error('urgency') is-invalid @enderror" required>
                <option value="">— Pilih Urgensi —</option>
                @foreach(\App\Models\Ticket::URGENCIES as $value => $label)
                    <option value="{{ $value }}" {{ old('urgency') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('urgency')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <div class="form-text mt-0">Prioritas dan target waktu penanganan ditentukan otomatis dari dampak &amp; urgensi.</div>
        </div>
        <div class="col-12" id="approvalSection" style="display:none">
            <div class="alert alert-warning small mb-2">
                <i class="fas fa-user-tie me-1"></i>
                Kategori ini memerlukan <strong>persetujuan atasan</strong>. Setelah tiket dibuat, cetak formulir persetujuan,
                minta tanda tangan atasan, lalu unggah foto/scan formulir di halaman tiket.
            </div>
            <label class="form-label fw-semibold small">Atasan yang menyetujui <span class="text-danger">*</span></label>
            <select name="supervisor_id" id="supervisorSelect" class="form-select @error('supervisor_id') is-invalid @enderror">
                <option value="">— Pilih Atasan —</option>
                @foreach(($supervisors ?? []) as $sup)
                    <option value="{{ $sup->id }}" {{ old('supervisor_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->name }}{{ $sup->position ? ' — '.$sup->position : '' }}
                    </option>
                @endforeach
            </select>
            @error('supervisor_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text" id="supervisorHelp">
                {{ isset($supervisors) ? 'Daftar karyawan aktif di departemen Anda.' : 'Daftar atasan muncul setelah NIK Anda terverifikasi.' }}
            </div>
        </div>
        <script>
            (function () {
                const category = document.getElementById('categorySelect');
                const section = document.getElementById('approvalSection');
                const supervisor = document.getElementById('supervisorSelect');
                const oldValue = @json(old('supervisor_id'));

                function update() {
                    const option = category.selectedOptions[0];
                    const needed = !!option && option.dataset.requiresApproval === '1';
                    section.style.display = needed ? '' : 'none';
                    supervisor.required = needed;
                }

                // Dipanggil form publik setelah NIK terverifikasi
                window.fillSupervisors = function (list) {
                    supervisor.innerHTML = '<option value="">— Pilih Atasan —</option>';
                    list.forEach(function (item) {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.name + (item.position ? ' — ' + item.position : '');
                        if (String(item.id) === String(oldValue)) opt.selected = true;
                        supervisor.appendChild(opt);
                    });
                    document.getElementById('supervisorHelp').textContent = list.length
                        ? 'Daftar karyawan aktif di departemen Anda.'
                        : 'Tidak ada karyawan lain di departemen Anda. Hubungi tim IT.';
                };

                category.addEventListener('change', update);
                update();
            })();
        </script>
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

