{{-- Struktur Organisasi — mockup: OrgMobile (accordion per level), OrgDesktop (bagan bertingkat). Admin tidak termasuk legenda. --}}
@php
    $levels = collect(range(7, 1))->map(fn ($n) => ['n' => $n, 'users' => $organizationTree['level'.$n] ?? collect()])
        ->filter(fn ($l) => $l['users']->count() > 0)->values();
    $total = $levels->sum(fn ($l) => $l['users']->count());
@endphp

<x-app-layout title="Struktur Organisasi">
    <x-sg.page-header title="Struktur Organisasi" :subtitle="$department->name.' · '.$total.' anggota'" />

    <div class="sg-chip-row mb-3" aria-label="Legenda level">
        @foreach ($levels as $level)<x-sg.badge-level :level="$level['n']" />@endforeach
    </div>

    @if ($levels->isEmpty())
        <x-sg.empty icon="bi-diagram-3" title="Belum ada anggota" text="Anggota departemen akan tampil di sini." />
    @else
        <div class="sg-org">
            @foreach ($levels as $level)
                <details class="sg-org__level" open>
                    <summary class="sg-org__head">
                        <x-sg.badge-level :level="$level['n']" />
                        <span class="fw-semibold">Level {{ $level['n'] }}</span>
                        <span class="text-muted small ms-auto">{{ $level['users']->count() }} orang</span>
                        <i class="bi bi-chevron-down sg-org__chev" aria-hidden="true"></i>
                    </summary>
                    <div class="sg-org__people">
                        @foreach ($level['users'] as $member)
                            <div class="sg-org__person">
                                <x-sg.avatar :user="$member" :size="44" />
                                <div class="min-w-0">
                                    <div class="fw-semibold sg-clamp-1">{{ $member->name }}</div>
                                    <div class="small text-muted sg-clamp-1">{{ $member->role->name ?? 'Level '.$level['n'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>
    @endif
</x-app-layout>
