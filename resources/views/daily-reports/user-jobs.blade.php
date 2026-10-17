{{-- Laporan Saya — mockup: ListMobile / ListDesktop (daftar milik pengguna) --}}
<x-app-layout title="Laporan Saya">
    <x-sg.page-header title="Laporan Saya" :subtitle="\App\Support\Format::number($reports->total()).' laporan yang Anda buat'">
        <x-slot:actions>
            <a href="{{ route('daily-reports.export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Ekspor</a>
            <a href="{{ route('daily-reports.show-import') }}" class="btn btn-outline-secondary"><i class="bi bi-upload" aria-hidden="true"></i> Impor</a>
            <a href="{{ route('daily-reports.create') }}" class="btn btn-primary d-none d-lg-inline-flex"><i class="bi bi-plus-lg" aria-hidden="true"></i> Buat Laporan</a>
        </x-slot:actions>
    </x-sg.page-header>

    <x-report.filters :action="route('daily-reports.user-jobs')" status />

    <x-report.list :reports="$reports->getCollection()" caption="Laporan saya"
                   empty-text="Laporan yang Anda buat akan muncul di sini.">
        <x-slot:empty><a href="{{ route('daily-reports.create') }}" class="btn btn-primary">Buat Laporan</a></x-slot:empty>
    </x-report.list>

    <div class="mt-3">{{ $reports->withQueryString()->links() }}</div>
</x-app-layout>
