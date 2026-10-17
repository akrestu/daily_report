{{-- Rencana Kerja (dibuat oleh saya) — mockup: PlanMobile, PlanDesktop --}}
@php($canShare = auth()->user()->isAdmin() || auth()->user()->getRoleLevel() >= 3)
<x-app-layout title="Rencana Kerja">
    <x-sg.page-header title="Rencana Kerja" :subtitle="\App\Support\Format::number($plans->total()).' rencana yang Anda tugaskan'">
        <x-slot:actions>
            @if ($canShare)
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#wa-share"><i class="bi bi-whatsapp" aria-hidden="true"></i> Ringkasan WA</button>
            @endif
            <a href="{{ route('job-plans.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Buat Rencana</a>
        </x-slot:actions>
    </x-sg.page-header>

    @include('job-plans.partials.filters', ['action' => route('job-plans.index')])

    <x-plan.list :plans="$plans->getCollection()">
        <a href="{{ route('job-plans.create') }}" class="btn btn-primary">Buat Rencana</a>
    </x-plan.list>

    <div class="mt-3">{{ $plans->withQueryString()->links() }}</div>

    @if ($canShare)
        <x-sg.wa-share :url="route('job-plans.whatsapp-share')" unit="rencana" />
    @endif
</x-app-layout>
