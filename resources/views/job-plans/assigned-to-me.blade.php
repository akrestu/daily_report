{{-- Rencana untuk Saya — mockup: PlanMobile (tab "Ditugaskan ke Saya") --}}
<x-app-layout title="Rencana untuk Saya">
    <x-sg.page-header title="Rencana untuk Saya"
        :subtitle="$activeCount ? $activeCount.' rencana aktif menunggu dikonversi menjadi laporan' : 'Tidak ada rencana aktif'" />

    @include('job-plans.partials.filters', ['action' => route('job-plans.assigned-to-me')])

    <x-plan.list :plans="$plans->getCollection()" mine />

    <div class="mt-3">{{ $plans->withQueryString()->links() }}</div>
</x-app-layout>
