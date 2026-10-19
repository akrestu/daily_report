{{-- Admin · Kategori Tiket — daftar + sheet tambah/edit (sgAdminList) --}}
@php
    $toItem = fn ($c) => ['id' => $c->id, 'name' => $c->name, 'description' => (string) $c->description,
        'requires_approval' => (bool) $c->requires_approval, 'is_active' => (bool) $c->is_active];
    $reopen = $errors->any() && old('_sheet') !== null
        ? ['mode' => old('_sheet') ? 'edit' : 'create', 'item' => ['id' => old('_sheet'), 'name' => old('name'), 'description' => old('description'),
            'requires_approval' => (bool) old('requires_approval'), 'is_active' => (bool) old('is_active')]]
        : null;
@endphp

<x-app-layout title="Kategori Tiket">
    <div x-data="sgAdminList(@js(['base' => url('admin/ticket-categories')]))"
         @if ($reopen) x-init="$nextTick(() => {{ $reopen['mode'] === 'edit' ? 'edit' : 'create' }}(@js($reopen['item'])))" @endif>

        <x-sg.page-header title="Kategori Tiket" subtitle="Kategori yang perlu persetujuan atasan mewajibkan pemohon mengunggah formulir bertanda tangan sebelum tiket dikerjakan.">
            <x-slot:actions>
                <button type="button" class="btn btn-primary" @click="create({ is_active: true, requires_approval: false })"><i class="bi bi-plus-lg" aria-hidden="true"></i> Kategori baru</button>
            </x-slot:actions>
        </x-sg.page-header>

        @if ($categories->isEmpty())
            <x-sg.empty icon="bi-tags" title="Belum ada kategori" text="Tambahkan kategori agar pemohon bisa memilih jenis bantuan." />
        @else
            <div class="sg-list">
                @foreach ($categories as $c)
                    <button type="button" class="sg-list-card text-start w-100" @click="edit(@js($toItem($c)))">
                        <span class="sg-list-card__body">
                            <span class="sg-list-card__top">
                                <span class="d-flex gap-1 flex-wrap">
                                    <span class="sg-badge sg-badge--{{ $c->is_active ? 'approved' : 'closed' }}">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    @if ($c->requires_approval)<span class="sg-badge sg-badge--pending">Perlu persetujuan atasan</span>@endif
                                </span>
                                <span class="sg-list-card__time sg-num">{{ $c->tickets_count }} tiket</span>
                            </span>
                            <span class="sg-list-card__title d-block">{{ $c->name }}</span>
                            @if ($c->description)<span class="sg-list-card__meta d-block sg-clamp-1">{{ $c->description }}</span>@endif
                        </span>
                        <i class="bi bi-pencil-square sg-list-card__chevron" aria-hidden="true"></i>
                    </button>
                @endforeach
            </div>
        @endif

        <x-sg.sheet id="item-sheet" title="Kategori tiket">
            <form method="POST" :action="mode === 'edit' ? url(item.id) : @js(route('admin.ticket-categories.store'))" novalidate>
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="_sheet" :value="mode === 'edit' ? item.id : ''">
                <p class="fw-semibold mb-3" x-text="mode === 'edit' ? 'Edit kategori' : 'Kategori baru'"></p>
                <div class="mb-3">
                    <label for="k-name" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                    <input id="k-name" name="name" class="form-control @error('name') is-invalid @enderror" x-model="item.name" required maxlength="255" placeholder="Mis. Jaringan & internet">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="k-desc" class="form-label fw-semibold">Deskripsi</label>
                    <input id="k-desc" name="description" class="form-control @error('description') is-invalid @enderror" x-model="item.description" maxlength="255" placeholder="Opsional">
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="requires_approval" :value="item.requires_approval ? 1 : 0">
                    <input class="form-check-input" type="checkbox" role="switch" id="k-approval" x-model="item.requires_approval">
                    <label class="form-check-label" for="k-approval">Perlu persetujuan atasan</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="is_active" :value="item.is_active ? 1 : 0">
                    <input class="form-check-input" type="checkbox" role="switch" id="k-active" x-model="item.is_active">
                    <label class="form-check-label" for="k-active">Aktif (muncul di form tiket)</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary flex-fill">Simpan</button>
                </div>
            </form>
        </x-sg.sheet>
    </div>
</x-app-layout>
