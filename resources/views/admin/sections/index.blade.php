{{-- Admin · Section — pola daftar master data (lihat admin/departments/index) --}}
@php
    $rows = $sections->getCollection();
    $toItem = fn ($s) => ['id' => $s->id, 'name' => $s->name, 'code' => $s->code, 'department_id' => (string) $s->department_id,
        'description' => (string) $s->description, 'is_active' => (bool) $s->is_active, 'reports' => $s->daily_reports_count];
    $reopen = $errors->any() && old('_sheet') !== null
        ? ['mode' => old('_sheet') ? 'edit' : 'create', 'item' => ['id' => old('_sheet'), 'name' => old('name'), 'code' => old('code'),
            'department_id' => (string) old('department_id'), 'description' => old('description'), 'is_active' => (bool) old('is_active')]]
        : null;
    $badge = fn ($active) => '<span class="sg-badge sg-badge--'.($active ? 'approved' : 'closed').'">'.($active ? 'Aktif' : 'Nonaktif').'</span>';
@endphp

<x-app-layout title="Section">
    <div x-data="sgAdminList(@js(['base' => url('admin/sections'), 'ids' => $rows->pluck('id')]))"
         @if ($reopen) x-init="$nextTick(() => {{ $reopen['mode'] === 'edit' ? 'edit' : 'create' }}(@js($reopen['item'])))" @endif>

        <x-sg.page-header title="Section" :subtitle="\App\Support\Format::number($sections->total()).' section'">
            <x-slot:actions>
                <button type="button" class="btn btn-primary" @click="create({ is_active: true, department_id: @js((string) request('department_id', '')) })"><i class="bi bi-plus-lg" aria-hidden="true"></i> Section baru</button>
            </x-slot:actions>
        </x-sg.page-header>

        <form method="GET" action="{{ route('admin.sections.index') }}" class="sg-filter-bar mb-3">
            <div class="sg-filter-bar__search"><x-sg.search name="search" placeholder="Cari nama, kode, atau departemen" /></div>
            <label for="f-sec-dept" class="visually-hidden">Departemen</label>
            <select id="f-sec-dept" name="department_id" class="form-select" onchange="this.form.submit()">
                <option value="">Semua departemen</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-secondary">Cari</button>
            @if (request()->hasAny(['search', 'department_id']))
                <a href="{{ route('admin.sections.index') }}" class="btn btn-quiet">Reset</a>
            @endif
        </form>

        @if ($rows->isEmpty())
            <x-sg.empty icon="bi-diagram-2" title="Tidak ada section" :text="request()->hasAny(['search', 'department_id']) ? 'Tidak ada yang cocok dengan saringan.' : 'Tambahkan section pertama.'" />
        @else
            <div class="sg-list d-lg-none">
                @foreach ($rows as $s)
                    <div class="sg-list-card" :class="{ 'is-selected': isSelected({{ $s->id }}) }">
                        <input type="checkbox" class="form-check-input flex-none mt-1" :checked="isSelected({{ $s->id }})" @change="toggle({{ $s->id }})" aria-label="Pilih {{ $s->name }}">
                        <div class="sg-list-card__body">
                            <div class="sg-list-card__top">{!! $badge($s->is_active) !!}</div>
                            <div class="sg-list-card__title sg-clamp-2">{{ $s->name }}</div>
                            <div class="sg-list-card__meta sg-clamp-1">{{ collect([$s->code, $s->department?->name, $s->daily_reports_count.' laporan'])->filter()->implode(' · ') }}</div>
                        </div>
                        <div class="d-flex gap-1 flex-none">
                            <button type="button" class="btn btn-outline-secondary btn-icon" @click="edit(@js($toItem($s)))" aria-label="Edit {{ $s->name }}"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-icon text-danger" @click="remove(@js($toItem($s)))" aria-label="Hapus {{ $s->name }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-sg.table class="d-none d-lg-block" caption="Daftar section">
                <x-slot:head>
                    <tr>
                        <th style="width:44px"><input type="checkbox" class="form-check-input" :checked="allSelected" @change="toggleAll()" aria-label="Pilih semua di halaman ini"></th>
                        <th>Nama</th><th>Kode</th><th>Departemen</th><th>Status</th><th class="text-end">Laporan</th><th style="width:110px"><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </x-slot:head>
                @foreach ($rows as $s)
                    <tr :class="{ 'table-active': isSelected({{ $s->id }}) }">
                        <td><input type="checkbox" class="form-check-input" :checked="isSelected({{ $s->id }})" @change="toggle({{ $s->id }})" aria-label="Pilih {{ $s->name }}"></td>
                        <td class="fw-semibold">{{ $s->name }}</td>
                        <td class="text-nowrap">{{ $s->code }}</td>
                        <td>{{ $s->department?->name ?? '-' }}</td>
                        <td>{!! $badge($s->is_active) !!}</td>
                        <td class="text-end sg-num">{{ $s->daily_reports_count }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-quiet btn-icon" @click="edit(@js($toItem($s)))" aria-label="Edit {{ $s->name }}"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
                            <button type="button" class="btn btn-quiet btn-icon text-danger" @click="remove(@js($toItem($s)))" aria-label="Hapus {{ $s->name }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                @endforeach
            </x-sg.table>
            <div class="mt-3">{{ $sections->withQueryString()->links() }}</div>
        @endif

        <div class="sg-batch-bar" x-show="selected.length" x-cloak>
            <div class="d-flex gap-2 align-items-center w-100">
                <span class="fw-semibold me-auto" x-text="selected.length + ' dipilih'"></span>
                <button type="button" class="btn btn-outline-secondary" @click="selected = []">Batal</button>
                <button type="button" class="btn btn-reject" @click="sheet('bulk-sheet')" x-text="'Hapus (' + selected.length + ')'"></button>
            </div>
        </div>

        <x-sg.sheet id="item-sheet" title="Section">
            <form method="POST" :action="mode === 'edit' ? url(item.id) : @js(route('admin.sections.store'))" novalidate>
                @csrf
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <input type="hidden" name="_sheet" :value="mode === 'edit' ? item.id : ''">
                <p class="fw-semibold mb-3" x-text="mode === 'edit' ? 'Edit section' : 'Section baru'"></p>
                <div class="mb-3">
                    <label for="c-dept" class="form-label fw-semibold">Departemen <span class="text-danger">*</span></label>
                    <select id="c-dept" name="department_id" class="form-select @error('department_id') is-invalid @enderror" x-model="item.department_id" required>
                        <option value="">Pilih departemen</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-7">
                        <label for="c-name" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                        <input id="c-name" name="name" class="form-control @error('name') is-invalid @enderror" x-model="item.name" required maxlength="255">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-5">
                        <label for="c-code" class="form-label fw-semibold">Kode <span class="text-danger">*</span></label>
                        <input id="c-code" name="code" class="form-control @error('code') is-invalid @enderror" x-model="item.code" required maxlength="50" placeholder="Mis. IT-NET" autocapitalize="characters">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label for="c-desc" class="form-label fw-semibold">Deskripsi</label>
                    <textarea id="c-desc" name="description" rows="2" class="form-control @error('description') is-invalid @enderror" x-model="item.description"></textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="is_active" :value="item.is_active ? 1 : 0">
                    <input class="form-check-input" type="checkbox" role="switch" id="c-active" x-model="item.is_active">
                    <label class="form-check-label" for="c-active">Aktif (bisa dipilih di laporan)</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary flex-fill">Simpan</button>
                </div>
            </form>
        </x-sg.sheet>

        <x-sg.sheet id="delete-sheet" title="Hapus section?">
            <form method="POST" :action="url(item.id)">
                @csrf
                @method('DELETE')
                <p>Section <strong x-text="item.name"></strong> akan dihapus permanen.</p>
                <div class="alert alert-danger small" x-show="item.reports > 0">
                    Masih ada <strong x-text="item.reports"></strong> laporan di section ini, jadi tidak bisa dihapus. Nonaktifkan saja bila tidak dipakai lagi.
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-reject flex-fill" :disabled="item.reports > 0">Hapus</button>
                </div>
            </form>
        </x-sg.sheet>

        <x-sg.confirm-danger id="bulk-sheet" title="Hapus section terpilih?" :action="route('admin.sections.batch-delete')" confirm-text="HAPUS"
                             text="Section yang masih memiliki laporan akan dilewati.">
            <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
        </x-sg.confirm-danger>
    </div>
</x-app-layout>
