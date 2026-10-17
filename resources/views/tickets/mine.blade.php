{{-- Tiket Saya — permintaan ke tim IT milik pengguna --}}
@php
    $statusChips = collect(['' => 'Semua'] + collect(config('sigap-ui.ticket_status'))->map(fn ($s) => $s['label'])->all())
        ->map(fn ($label, $value) => ['label' => $label, 'href' => route('tickets.mine', array_filter(['status' => $value])), 'active' => (string) request('status') === (string) $value])
        ->values()->all();
@endphp

<x-app-layout title="Tiket Saya">
    <x-sg.page-header title="Tiket Saya" subtitle="Semua permintaan ke tim IT diajukan melalui tiket.">
        <x-slot:actions>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg" aria-hidden="true"></i> Buat Tiket</a>
        </x-slot:actions>
    </x-sg.page-header>

    <x-sg.chip-filter class="mb-3" label="Status tiket" :items="$statusChips" />

    <x-ticket.list :tickets="$tickets->getCollection()" empty-text="Ajukan tiket bila butuh bantuan perangkat, akses, atau aplikasi.">
        <a href="{{ route('tickets.create') }}" class="btn btn-primary">Buat Tiket</a>
    </x-ticket.list>

    <div class="mt-3">{{ $tickets->withQueryString()->links() }}</div>
</x-app-layout>
