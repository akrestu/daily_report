{{-- Ditugaskan ke Saya — laporan bawahan dengan Anda sebagai PIC --}}
<x-app-layout title="Ditugaskan ke Saya">
    <x-sg.page-header title="Ditugaskan ke Saya" :subtitle="\App\Support\Format::number($reports->total()).' laporan dengan Anda sebagai PIC'">
        <x-slot:actions>
            <a href="{{ route('daily-reports.export') }}?{{ http_build_query(request()->query()) }}" class="btn btn-outline-secondary"><i class="bi bi-download" aria-hidden="true"></i> Ekspor</a>
            <a href="{{ route('daily-reports.pending') }}" class="btn btn-primary"><i class="bi bi-check2-square" aria-hidden="true"></i> Antrian Persetujuan</a>
        </x-slot:actions>
    </x-sg.page-header>

    <x-report.filters :action="route('daily-reports.assigned-jobs')" status />

    <x-report.list :reports="$reports->getCollection()" show-owner caption="Laporan yang ditugaskan ke saya"
                   empty-title="Belum ada laporan" empty-text="Laporan bawahan yang memilih Anda sebagai PIC akan muncul di sini." />

    <div class="mt-3" data-sg-pager>{{ $reports->withQueryString()->links() }}</div>
</x-app-layout>
