{{--
    Halaman impor Excel: unggah file + panduan kolom + unduh template.
    :columns = [['nama_kolom', wajib(bool), 'keterangan'], ...]. Pesan session('error') tampil sebagai toast (x-sg.flash).
--}}
@props(['action', 'template', 'back', 'title', 'columns' => [], 'button' => 'Impor'])
<form action="{{ $action }}" method="POST" enctype="multipart/form-data" x-data="{ busy: false, name: '' }" @submit="busy = true">
    @csrf
    <x-sg.page-header :title="$title" class="d-none d-lg-flex" subtitle="Unggah file Excel sesuai template.">
        <x-slot:actions>
            <a href="{{ $template }}" class="btn btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Unduh template</a>
        </x-slot:actions>
    </x-sg.page-header>

    @if ($errors->has('import_errors'))
        <div class="alert alert-danger small" role="alert">
            <strong>Impor gagal:</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->get('import_errors') as $messages)
                    @foreach ((array) $messages as $message)<li>{{ $message }}</li>@endforeach
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 g-lg-4">
        <div class="col-lg-6">
            <section class="card p-3 p-lg-4" aria-labelledby="imp-file">
                <h2 id="imp-file" class="h6 fw-bold mb-3">File</h2>
                <label class="sg-dropzone @error('file') is-invalid @enderror" for="imp-input">
                    <i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i>
                    <span class="fw-semibold" x-text="name || 'Pilih file Excel'">Pilih file Excel</span>
                    <span class="small text-muted">.xlsx, .xls, atau .csv</span>
                </label>
                <input id="imp-input" type="file" name="file" class="visually-hidden" accept=".xlsx,.xls,.csv" required @change="name = $event.target.files[0]?.name || ''">
                @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <a href="{{ $template }}" class="btn btn-outline-secondary w-100 mt-3 d-lg-none"><i class="bi bi-download" aria-hidden="true"></i> Unduh template</a>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="card p-3 p-lg-4" aria-labelledby="imp-guide">
                <h2 id="imp-guide" class="h6 fw-bold mb-1">Kolom yang dibaca</h2>
                <p class="small text-muted">Baris pertama harus berisi nama kolom berikut.</p>
                <ul class="list-unstyled mb-0 small">
                    @foreach ($columns as [$col, $required, $desc])
                        <li class="d-flex gap-2 py-2 border-top">
                            <code class="flex-none">{{ $col }}</code>
                            <span class="flex-grow-1">{{ $desc }}</span>
                            <span class="flex-none {{ $required ? 'text-danger' : 'text-muted' }}">{{ $required ? 'wajib' : 'opsional' }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>

    <x-sg.sticky-actions>
        <a href="{{ $back }}" class="btn btn-outline-secondary flex-fill">Batal</a>
        <button type="submit" class="btn btn-primary flex-fill" :disabled="busy || !name" x-text="busy ? 'Mengimpor…' : @js($button)">{{ $button }}</button>
    </x-sg.sticky-actions>
    <div class="d-none d-lg-flex justify-content-end gap-2 mt-3">
        <a href="{{ $back }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary" :disabled="busy || !name" x-text="busy ? 'Mengimpor…' : @js($button)">{{ $button }}</button>
    </div>
</form>
