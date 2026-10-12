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

