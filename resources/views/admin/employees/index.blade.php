{{-- Admin · Master Karyawan — dipakai verifikasi NIK di form tiket publik. Flash tampil sebagai toast (x-sg.flash di layout). --}}
@php
    $rows = $employees->getCollection();
    $filtered = collect(request()->only(['search', 'department', 'status']))->filter()->isNotEmpty();
    $badge = fn ($active) => '<span class="sg-badge sg-badge--'.($active ? 'approved' : 'closed').'">'.($active ? 'Aktif' : 'Nonaktif').'</span>';
@endphp

<x-app-layout title="Master Karyawan">
    <div x-data="{ item: {}, remove(item) { this.item = item; window.bootstrap.Modal.getOrCreateInstance(document.getElementById('emp-delete')).show(); } }">
        <x-sg.page-header title="Master Karyawan" subtitle="Dipakai untuk verifikasi NIK pemohon di form tiket publik.">
            <x-slot:actions>
                <a href="{{ route('admin.employees.template') }}" class="btn btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Template</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#emp-import"><i class="bi bi-box-arrow-in-down" aria-hidden="true"></i> Impor</button>
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah</a>
            </x-slot:actions>
        </x-sg.page-header>

        @if ($errors->has('import_errors'))
            <div class="alert alert-danger small" role="alert">
                <strong>Impor gagal, tidak ada data yang disimpan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->get('import_errors') as $messages)
                        @foreach ((array) $messages as $message)<li>{{ $message }}</li>@endforeach
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.employees.index') }}" class="sg-filter-bar mb-3">
            <div class="sg-filter-bar__search"><x-sg.search name="search" placeholder="Cari NIK, nama, atau jabatan" /></div>
            <label for="f-e-dept" class="visually-hidden">Departemen</label>
            <select id="f-e-dept" name="department" class="form-select" onchange="this.form.submit()">
                <option value="">Semua departemen</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
            <label for="f-e-status" class="visually-hidden">Status</label>
            <select id="f-e-status" name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-outline-secondary">Cari</button>
            @if ($filtered)
                <a href="{{ route('admin.employees.index') }}" class="btn btn-quiet">Reset</a>
            @endif
        </form>

        @if ($rows->isEmpty())
            <x-sg.empty icon="bi-person-vcard" title="Belum ada data karyawan" :text="$filtered ? 'Tidak ada yang cocok dengan saringan.' : 'Unduh template lalu impor data karyawan.'" />
        @else
            <div class="sg-list d-lg-none">
                @foreach ($rows as $e)
                    <div class="sg-list-card">
                        <div class="sg-list-card__body">
                            <div class="sg-list-card__top">{!! $badge($e->is_active) !!}<span class="sg-list-card__time font-monospace">{{ $e->nik }}</span></div>
                            <a href="{{ route('admin.employees.edit', $e) }}" class="sg-list-card__title sg-clamp-1 text-reset text-decoration-none stretched-link">{{ $e->name }}</a>
                            <div class="sg-list-card__meta sg-clamp-1">{{ collect([$e->position, $e->department?->name, $e->jobSite?->name])->filter()->implode(' · ') ?: '-' }}</div>
                        </div>
                        <i class="bi bi-chevron-right sg-list-card__chevron" aria-hidden="true"></i>
                    </div>
                @endforeach
            </div>

            <x-sg.table class="d-none d-lg-block" caption="Daftar karyawan">
                <x-slot:head>
                    <tr><th>NIK</th><th>Nama</th><th>Departemen</th><th>Jabatan</th><th>Site</th><th class="text-end">Tiket</th><th>Status</th><th style="width:110px"><span class="visually-hidden">Aksi</span></th></tr>
                </x-slot:head>
                @foreach ($rows as $e)
                    <tr>
                        <td class="font-monospace text-nowrap">{{ $e->nik }}</td>
                        <td class="fw-semibold">{{ $e->name }}</td>
                        <td>{{ $e->department?->name ?? '-' }}</td>
                        <td>{{ $e->position ?? '-' }}</td>
                        <td>{{ $e->jobSite?->name ?? '-' }}</td>
                        <td class="text-end sg-num">{{ $e->tickets_count }}</td>
                        <td>{!! $badge($e->is_active) !!}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.employees.edit', $e) }}" class="btn btn-quiet btn-icon" aria-label="Edit {{ $e->name }}"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                            <button type="button" class="btn btn-quiet btn-icon text-danger" @click="remove(@js(['name' => $e->name, 'url' => route('admin.employees.destroy', $e), 'tickets' => $e->tickets_count]))" aria-label="Hapus {{ $e->name }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                @endforeach
            </x-sg.table>
            <div class="mt-3">{{ $employees->withQueryString()->links() }}</div>
        @endif

        <x-sg.sheet id="emp-import" title="Impor karyawan">
            <form action="{{ route('admin.employees.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="emp-file" class="form-label fw-semibold">File Excel (.xlsx, .xls, .csv)</label>
                <input id="emp-file" type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                <div class="form-text mb-3">
                    Kolom: nik, nama, departemen, jabatan, job_site, no_hp, status (aktif/nonaktif). NIK yang sudah ada akan diperbarui.
                    Format kolom NIK sebagai <strong>Teks</strong> agar angka 0 di depan tidak hilang.
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary flex-fill">Impor</button>
                </div>
            </form>
        </x-sg.sheet>

        <x-sg.sheet id="emp-delete" title="Hapus karyawan?">
            <form method="POST" :action="item.url">
                @csrf
                @method('DELETE')
                <p><strong x-text="item.name"></strong> akan dihapus.</p>
                <p class="small text-muted" x-show="item.tickets > 0">Karyawan ini punya riwayat tiket, jadi datanya dinonaktifkan, bukan dihapus.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-reject flex-fill">Hapus</button>
                </div>
            </form>
        </x-sg.sheet>
    </div>
</x-app-layout>
