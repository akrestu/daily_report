{{-- Admin · Departemen — pola daftar master data (sgAdminList): cari, tabel/kartu, sheet tambah/edit, hapus & hapus massal --}}
@php
    $rows = $departments->getCollection();
    $toItem = fn ($d) => ['id' => $d->id, 'name' => $d->name, 'code' => $d->code, 'description' => (string) $d->description, 'handles_tickets' => (bool) $d->handles_tickets, 'users' => $d->users_count];
    // Gagal validasi di sheet → buka lagi dengan isian lama
    $reopen = $errors->any() && old('_sheet') !== null
        ? ['mode' => old('_sheet') ? 'edit' : 'create', 'item' => ['id' => old('_sheet'), 'name' => old('name'), 'code' => old('code'), 'description' => old('description'), 'handles_tickets' => (bool) old('handles_tickets')]]
        : null;
@endphp

<x-app-layout title="Departemen">
    <div x-data="sgAdminList(@js(['base' => url('admin/departments'), 'ids' => $rows->pluck('id')]))"
         @if ($reopen) x-init="$nextTick(() => {{ $reopen['mode'] === 'edit' ? 'edit' : 'create' }}(@js($reopen['item'])))" @endif>

        <x-sg.page-header title="Departemen" :subtitle="\App\Support\Format::number($departments->total()).' departemen'">
            <x-slot:actions>
                <button type="button" class="btn btn-primary" @click="create()"><i class="bi bi-plus-lg" aria-hidden="true"></i> Departemen baru</button>
            </x-slot:actions>
        </x-sg.page-header>

        <form method="GET" action="{{ route('admin.departments.index') }}" class="sg-filter-bar mb-3">
            <div class="sg-filter-bar__search"><x-sg.search name="search" placeholder="Cari nama, kode, atau deskripsi" /></div>
            <button type="submit" class="btn btn-outline-secondary">Cari</button>
            @if (request('search'))
                <a href="{{ route('admin.departments.index') }}" class="btn btn-quiet">Reset</a>
            @endif
        </form>

        @if ($rows->isEmpty())
            <x-sg.empty icon="bi-building" title="Tidak ada departemen" :text="request('search') ? 'Tidak ada yang cocok dengan pencarian.' : 'Tambahkan departemen pertama.'" />
        @else
            {{-- Mobile --}}
            <div class="sg-list d-lg-none">
                @foreach ($rows as $d)
                    <div class="sg-list-card" :class="{ 'is-selected': isSelected({{ $d->id }}) }">
                        <input type="checkbox" class="form-check-input flex-none mt-1" :checked="isSelected({{ $d->id }})" @change="toggle({{ $d->id }})" aria-label="Pilih {{ $d->name }}">
                        <div class="sg-list-card__body">
                            <div class="sg-list-card__title sg-clamp-2"><a href="{{ route('admin.departments.show', $d) }}" class="text-reset text-decoration-none stretched-link">{{ $d->name }}</a></div>
                            <div class="sg-list-card__meta">{{ $d->code }} · {{ $d->users_count }} pengguna</div>
                        </div>
                        <div class="d-flex gap-1 flex-none">
                            <button type="button" class="btn btn-outline-secondary btn-icon" @click="edit(@js($toItem($d)))" aria-label="Edit {{ $d->name }}"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-icon text-danger" @click="remove(@js($toItem($d)))" aria-label="Hapus {{ $d->name }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop --}}
            <x-sg.table class="d-none d-lg-block" caption="Daftar departemen">
                <x-slot:head>
                    <tr>
                        <th style="width:44px"><input type="checkbox" class="form-check-input" :checked="allSelected" @change="toggleAll()" aria-label="Pilih semua di halaman ini"></th>
                        <th>Nama</th><th>Kode</th><th>Deskripsi</th><th class="text-end">Pengguna</th><th style="width:110px"><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </x-slot:head>
                @foreach ($rows as $d)
                    <tr :class="{ 'table-active': isSelected({{ $d->id }}) }">
                        <td><input type="checkbox" class="form-check-input" :checked="isSelected({{ $d->id }})" @change="toggle({{ $d->id }})" aria-label="Pilih {{ $d->name }}"></td>
                        <td><a href="{{ route('admin.departments.show', $d) }}" class="fw-semibold text-reset">{{ $d->name }}</a></td>
                        <td class="text-nowrap">{{ $d->code }}@if ($d->handles_tickets) <span class="badge text-bg-light ms-1" title="Tim layanan tiket"><i class="bi bi-inbox" aria-hidden="true"></i> Tiket</span>@endif</td>
                        <td class="text-muted"><span class="sg-clamp-1">{{ $d->description ?: '-' }}</span></td>
                        <td class="text-end sg-num">{{ $d->users_count }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-quiet btn-icon" @click="edit(@js($toItem($d)))" aria-label="Edit {{ $d->name }}"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
                            <button type="button" class="btn btn-quiet btn-icon text-danger" @click="remove(@js($toItem($d)))" aria-label="Hapus {{ $d->name }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                @endforeach
            </x-sg.table>
            <div class="mt-3">{{ $departments->withQueryString()->links() }}</div>
        @endif

        {{-- Bar hapus massal --}}
        <div class="sg-batch-bar" x-show="selected.length" x-cloak>
            <div class="d-flex gap-2 align-items-center w-100">
                <span class="fw-semibold me-auto" x-text="selected.length + ' dipilih'"></span>
                <button type="button" class="btn btn-outline-secondary" @click="selected = []">Batal</button>
                <button type="button" class="btn btn-reject" @click="sheet('bulk-sheet')" x-text="'Hapus (' + selected.length + ')'"></button>
            </div>
        </div>

        {{-- Tambah / edit --}}
        <x-sg.sheet id="item-sheet" title="Departemen">
            <form method="POST" :action="mode === 'edit' ? url(item.id) : @js(route('admin.departments.store'))" novalidate>
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="_sheet" :value="mode === 'edit' ? item.id : ''">
                <p class="fw-semibold mb-3" x-text="mode === 'edit' ? 'Edit departemen' : 'Departemen baru'"></p>
                <div class="mb-3">
                    <label for="d-name" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                    <input id="d-name" name="name" class="form-control @error('name') is-invalid @enderror" x-model="item.name" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="d-code" class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                    <input id="d-code" name="code" class="form-control @error('code') is-invalid @enderror" x-model="item.code" required maxlength="50" placeholder="Mis. HR, IT, FIN" autocapitalize="characters">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="d-desc" class="form-label fw-semibold">Deskripsi</label>
                    <textarea id="d-desc" name="description" rows="3" class="form-control @error('description') is-invalid @enderror" x-model="item.description"></textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="handles_tickets" value="0">
                    <input id="d-tickets" type="checkbox" role="switch" name="handles_tickets" value="1" class="form-check-input" x-model="item.handles_tickets">
                    <label for="d-tickets" class="form-check-label fw-semibold">Tim layanan tiket</label>
                    <div class="form-text">Anggota departemen ini memproses tiket masuk (level 4 ke atas memilah &amp; menugaskan).</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary flex-fill">Simpan</button>
                </div>
            </form>
        </x-sg.sheet>

        {{-- Hapus satu --}}
        <x-sg.sheet id="delete-sheet" title="Hapus departemen?">
            <form method="POST" :action="url(item.id)">
                @csrf
                @method('DELETE')
                <p>Departemen <strong x-text="item.name"></strong> akan dihapus permanen.</p>
                <div class="alert alert-danger small" x-show="item.users > 0">
                    Masih ada <strong x-text="item.users"></strong> pengguna di departemen ini. Pindahkan dulu sebelum menghapus.
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-reject flex-fill" :disabled="item.users > 0">Hapus</button>
                </div>
            </form>
        </x-sg.sheet>

        {{-- Hapus massal --}}
        <x-sg.confirm-danger id="bulk-sheet" title="Hapus departemen terpilih?" :action="route('admin.departments.batch-delete')" confirm-text="HAPUS"
                             text="Departemen yang masih memiliki pengguna akan dilewati.">
            <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
        </x-sg.confirm-danger>
    </div>
</x-app-layout>
