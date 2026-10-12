<x-app-layout>
    <x-slot name="header">Kategori Tiket</x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger small"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card mb-3">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Kategori Tiket</h5>
            <div class="text-muted small">
                Kategori bertanda <strong>Perlu persetujuan atasan</strong> mewajibkan pemohon memilih atasan,
                mencetak formulir, dan mengunggah bukti tanda tangan sebelum tiket dikerjakan.
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle small">
                    <thead class="table-light">
                        <tr><th>Nama</th><th>Deskripsi</th><th class="text-center">Persetujuan atasan</th><th class="text-center">Aktif</th><th class="text-center">Tiket</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        @php($formId = 'category-'.$category->id)
                        <tr>
                            <td><input type="text" name="name" form="{{ $formId }}" class="form-control form-control-sm" value="{{ $category->name }}" required maxlength="255"></td>
                            <td><input type="text" name="description" form="{{ $formId }}" class="form-control form-control-sm" value="{{ $category->description }}" maxlength="255"></td>
                            <td class="text-center"><input type="checkbox" form="{{ $formId }}" class="form-check-input" name="requires_approval" value="1" {{ $category->requires_approval ? 'checked' : '' }}></td>
                            <td class="text-center"><input type="checkbox" form="{{ $formId }}" class="form-check-input" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}></td>
                            <td class="text-center">{{ $category->tickets_count }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.ticket-categories.update', $category) }}" id="{{ $formId }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Simpan</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white py-3"><h6 class="mb-0">Tambah Kategori</h6></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.ticket-categories.store') }}" class="row g-2 align-items-center">
                @csrf
                <div class="col-md-3"><input type="text" name="name" class="form-control form-control-sm" placeholder="Nama kategori" required maxlength="255"></div>
                <div class="col-md-4"><input type="text" name="description" class="form-control form-control-sm" placeholder="Deskripsi (opsional)" maxlength="255"></div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="requires_approval" value="1" id="newRequiresApproval">
                        <label class="form-check-label small" for="newRequiresApproval">Perlu persetujuan atasan</label>
                    </div>
                </div>
                <input type="hidden" name="is_active" value="1">
                <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100">Tambah</button></div>
            </form>
        </div>
    </div>
</x-app-layout>
