{{-- Tiket Masuk (tim IT) — mockup: TicketListMobile, TicketListDesktop --}}
@php
    $statusChips = collect(['' => 'Semua'] + collect(config('sigap-ui.ticket_status'))->map(fn ($s) => $s['label'])->all())
        ->map(fn ($label, $value) => ['label' => $label, 'href' => route('tickets.index', array_filter(['status' => $value] + request()->except('status', 'page'))), 'active' => (string) request('status') === (string) $value])
        ->values()->all();
    $canDelete = auth()->user()->can('deleteAny', App\Models\Ticket::class);
@endphp

<x-app-layout title="Tiket Masuk">
    <x-sg.page-header title="Tiket Masuk" :subtitle="$openCount ? $openCount.' tiket baru belum ditangani' : 'Semua tiket baru sudah ditangani'">
        <x-slot:actions>
            <a href="{{ route('tickets.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-graph-up" aria-hidden="true"></i> Dashboard</a>
            <a href="{{ route('tickets.public.form') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Form publik</a>
        </x-slot:actions>
    </x-sg.page-header>

    <x-sg.chip-filter class="mb-3" label="Status tiket" :items="$statusChips" />

    <form method="GET" action="{{ route('tickets.index') }}" class="sg-filter-bar mb-3" role="search" aria-label="Saring tiket">
        @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <div class="sg-filter-bar__search"><x-sg.search name="search" placeholder="Cari tiket, nama, departemen" /></div>
        <label for="t-priority" class="visually-hidden">Prioritas</label>
        <select id="t-priority" name="priority" class="form-select">
            <option value="">Semua prioritas</option>
            @foreach (config('sigap-ui.priority') as $value => $p)
                <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $p['code'] }} {{ $p['label'] }}</option>
            @endforeach
        </select>
        <label for="t-category" class="visually-hidden">Kategori</label>
        <select id="t-category" name="category" class="form-select">
            <option value="">Semua kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) request('category') === (string) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <label for="t-sla" class="visually-hidden">SLA</label>
        <select id="t-sla" name="sla" class="form-select">
            <option value="">Semua SLA</option>
            <option value="breached" @selected(request('sla') === 'breached')>Lewat SLA</option>
        </select>
        <button type="submit" class="btn btn-primary">Terapkan</button>
        @if (request()->hasAny(['search', 'priority', 'category', 'sla']))
            <a href="{{ route('tickets.index', array_filter(['status' => request('status')])) }}" class="btn btn-quiet">Reset</a>
        @endif
    </form>

    @if ($canDelete)
        <form id="ticket-bulk" method="POST" action="{{ route('tickets.batch-delete') }}" class="d-none d-lg-block"
              x-data="{ count: 0 }" @change.window="count = document.querySelectorAll('[data-select-for=ticket-bulk]:checked').length">
            @csrf
            @method('DELETE')
            <div class="sg-bulk-bar border mb-2" style="border-radius:12px" x-show="count" x-cloak>
                <span x-text="count + ' dipilih'"></span>
                <button type="button" class="btn btn-sm btn-reject-outline ms-auto" data-bs-toggle="modal" data-bs-target="#ticket-bulk-confirm">
                    <i class="bi bi-trash3" aria-hidden="true"></i> Hapus
                </button>
            </div>
            <x-sg.sheet id="ticket-bulk-confirm" title="Hapus tiket terpilih?">
                <p class="mb-0">Tiket yang dipilih akan dihapus. Tindakan ini tidak dapat dibatalkan dari aplikasi.</p>
                <x-slot:footer>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-reject">Hapus</button>
                </x-slot:footer>
            </x-sg.sheet>
        </form>
    @endif

    <x-ticket.list :tickets="$tickets->getCollection()" agent :selectable="$canDelete ? 'ticket-bulk' : null" />

    <div class="mt-3">{{ $tickets->withQueryString()->links() }}</div>
</x-app-layout>
