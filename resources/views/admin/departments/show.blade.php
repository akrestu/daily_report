{{-- Admin · Detail Departemen — info, statistik, anggota --}}
@use('App\Support\Format')
<x-app-layout title="Detail Departemen" :back="route('admin.departments.index')">
    <x-sg.page-header :title="$department->name" :subtitle="$department->code">
        <x-slot:actions>
            <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-primary"><i class="bi bi-pencil-square" aria-hidden="true"></i> Edit</a>
        </x-slot:actions>
    </x-sg.page-header>

    <div class="row g-3 g-lg-4">
        <div class="col-lg-4">
            <section class="card p-3 mb-3" aria-label="Informasi departemen">
                <dl class="sg-meta-grid border-0 pt-0 mb-0">
                    <div><dt>Kode</dt><dd>{{ $department->code }}</dd></div>
                    <div><dt>Pengguna</dt><dd class="sg-num">{{ Format::number($department->users->count()) }}</dd></div>
                    <div><dt>Laporan</dt><dd class="sg-num">{{ Format::number($department->dailyReports()->count()) }}</dd></div>
                    <div><dt>Diperbarui</dt><dd>{{ Format::date($department->updated_at) }}</dd></div>
                </dl>
                @if ($department->description)
                    <h2 class="sg-section-label mt-3">Deskripsi</h2>
                    <p class="sg-prewrap mb-0">{{ $department->description }}</p>
                @endif
            </section>
        </div>

        <div class="col-lg-8">
            <section aria-labelledby="dp-members">
                <h2 id="dp-members" class="h6 fw-bold mb-2">Anggota ({{ $department->users->count() }})</h2>
                @if ($department->users->isEmpty())
                    <x-sg.empty icon="bi-people" title="Belum ada anggota" text="Atur departemen pengguna di halaman Pengguna." />
                @else
                    <div class="sg-list">
                        @foreach ($department->users->sortByDesc(fn ($u) => $u->getRoleLevel()) as $member)
                            <div class="sg-list-card">
                                <x-sg.avatar :user="$member" :size="40" class="flex-none" />
                                <div class="sg-list-card__body">
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('admin.users.edit', $member) }}" class="sg-list-card__title sg-clamp-1 text-reset text-decoration-none stretched-link">{{ $member->name }}</a>
                                        <x-sg.badge-level :user="$member" short class="flex-none" />
                                    </div>
                                    <div class="sg-list-card__meta sg-clamp-1">{{ $member->user_id ?: $member->email ?: '-' }}</div>
                                </div>
                                <i class="bi bi-chevron-right sg-list-card__chevron" aria-hidden="true"></i>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
