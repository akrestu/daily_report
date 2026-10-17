{{-- Admin · Pengguna — daftar + filter (cari, role, departemen) + hapus massal. Tambah/edit di halaman form (admin/users/partials/form). --}}
@php
    $rows = $users->getCollection();
    $filtered = collect(request()->only(['search', 'role', 'department']))->filter()->isNotEmpty();
@endphp

<x-app-layout title="Pengguna">
    <div x-data="sgAdminList(@js(['base' => url('admin/users'), 'ids' => $rows->where('id', '!=', auth()->id())->pluck('id')->values()]))">

        <x-sg.page-header title="Pengguna" :subtitle="\App\Support\Format::number($users->total()).' pengguna'">
            <x-slot:actions>
                <a href="{{ route('admin.users.export') }}" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up" aria-hidden="true"></i> Ekspor</a>
                <a href="{{ route('admin.users.show-import') }}" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-in-down" aria-hidden="true"></i> Impor</a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus" aria-hidden="true"></i> Pengguna baru</a>
            </x-slot:actions>
        </x-sg.page-header>

        <form method="GET" action="{{ route('admin.users.index') }}" class="sg-filter-bar mb-3">
            <div class="sg-filter-bar__search"><x-sg.search name="search" placeholder="Cari nama, User ID, atau email" /></div>
            <label for="f-u-role" class="visually-hidden">Role</label>
            <select id="f-u-role" name="role" class="form-select" onchange="this.form.submit()">
                <option value="">Semua role</option>
                @foreach ($roles as $id => $name)
                    <option value="{{ $id }}" @selected(request('role') == $id)>{{ $name }}</option>
                @endforeach
            </select>
            <label for="f-u-dept" class="visually-hidden">Departemen</label>
            <select id="f-u-dept" name="department" class="form-select" onchange="this.form.submit()">
                <option value="">Semua departemen</option>
                @foreach ($departments as $id => $name)
                    <option value="{{ $id }}" @selected(request('department') == $id)>{{ $name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-secondary">Cari</button>
            @if ($filtered)
                <a href="{{ route('admin.users.index') }}" class="btn btn-quiet">Reset</a>
            @endif
        </form>

        @if ($rows->isEmpty())
            <x-sg.empty icon="bi-people" title="Tidak ada pengguna" :text="$filtered ? 'Tidak ada yang cocok dengan saringan.' : 'Tambahkan pengguna pertama.'" />
        @else
            <div class="sg-list d-lg-none">
                @foreach ($rows as $u)
                    <div class="sg-list-card" :class="{ 'is-selected': isSelected({{ $u->id }}) }">
                        @unless ($u->id === auth()->id())
                            <input type="checkbox" class="form-check-input flex-none mt-1 position-relative" style="z-index:2" :checked="isSelected({{ $u->id }})" @change="toggle({{ $u->id }})" aria-label="Pilih {{ $u->name }}">
                        @endunless
                        <x-sg.avatar :user="$u" :size="40" class="flex-none" />
                        <div class="sg-list-card__body">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.users.edit', $u) }}" class="sg-list-card__title sg-clamp-1 text-reset text-decoration-none stretched-link">{{ $u->name }}</a>
                                <x-sg.badge-level :user="$u" short class="flex-none" />
                            </div>
                            <div class="sg-list-card__meta sg-clamp-1">{{ collect([$u->user_id, $u->department?->name, $u->jobSite?->name])->filter()->implode(' · ') ?: '-' }}</div>
                        </div>
                        <i class="bi bi-chevron-right sg-list-card__chevron" aria-hidden="true"></i>
                    </div>
                @endforeach
            </div>

            <x-sg.table class="d-none d-lg-block" caption="Daftar pengguna">
                <x-slot:head>
                    <tr>
                        <th style="width:44px"><input type="checkbox" class="form-check-input" :checked="allSelected" @change="toggleAll()" aria-label="Pilih semua di halaman ini"></th>
                        <th>Nama</th><th>User ID</th><th>Role</th><th>Departemen</th><th>Job site</th><th style="width:60px"><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </x-slot:head>
                @foreach ($rows as $u)
                    <tr :class="{ 'table-active': isSelected({{ $u->id }}) }">
                        <td>
                            @unless ($u->id === auth()->id())
                                <input type="checkbox" class="form-check-input" :checked="isSelected({{ $u->id }})" @change="toggle({{ $u->id }})" aria-label="Pilih {{ $u->name }}">
                            @endunless
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <x-sg.avatar :user="$u" :size="32" />
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="fw-semibold text-reset sg-clamp-1">{{ $u->name }}</a>
                                    <div class="small text-muted sg-clamp-1">{{ $u->email ?: '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-nowrap">{{ $u->user_id ?: '-' }}</td>
                        <td><x-sg.badge-level :user="$u" /></td>
                        <td>{{ $u->department?->name ?? '-' }}</td>
                        <td>{{ $u->jobSite?->name ?? '-' }}</td>
                        <td class="text-end"><a href="{{ route('admin.users.edit', $u) }}" class="btn btn-quiet btn-icon" aria-label="Edit {{ $u->name }}"><i class="bi bi-pencil-square" aria-hidden="true"></i></a></td>
                    </tr>
                @endforeach
            </x-sg.table>
            <div class="mt-3">{{ $users->links() }}</div>
        @endif

        <div class="sg-batch-bar" x-show="selected.length" x-cloak>
            <div class="d-flex gap-2 align-items-center w-100">
                <span class="fw-semibold me-auto" x-text="selected.length + ' dipilih'"></span>
                <button type="button" class="btn btn-outline-secondary" @click="selected = []">Batal</button>
                <button type="button" class="btn btn-reject" @click="sheet('bulk-sheet')" x-text="'Hapus (' + selected.length + ')'"></button>
            </div>
        </div>

        <x-sg.confirm-danger id="bulk-sheet" title="Hapus pengguna terpilih?" :action="route('admin.users.batch-delete')" confirm-text="HAPUS"
                             text="Akun yang dipilih dihapus permanen. Akun Anda sendiri tidak ikut terhapus.">
            <template x-for="id in selected" :key="id"><input type="hidden" name="selected_users[]" :value="id"></template>
        </x-sg.confirm-danger>
    </div>
</x-app-layout>
