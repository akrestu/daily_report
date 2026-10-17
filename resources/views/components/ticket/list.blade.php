{{--
    Daftar tiket: kartu di <992px, tabel padat di desktop. Mockup: TicketListMobile, TicketListDesktop.
    :tickets    koleksi Ticket (eager load category, assignedUser)
    :agent      tampilkan pemohon & SLA (tampilan tim IT)
    :selectable id form tujuan checkbox ids[] (hapus massal admin)
--}}
@props(['tickets', 'agent' => false, 'selectable' => null, 'emptyText' => 'Tidak ada tiket yang ditemukan.'])

@if ($tickets->isEmpty())
    <x-sg.empty icon="bi-ticket-perforated" title="Belum ada tiket" :text="$emptyText">{{ $slot }}</x-sg.empty>
@else
    <div class="sg-list d-lg-none">
        @foreach ($tickets as $ticket)
            <x-sg.list-card :href="route('tickets.show', $ticket)" :title="$ticket->title"
                            :time="\App\Support\Format::dayMonth($ticket->created_at).', '.$ticket->created_at->format('H:i')"
                            :meta="collect([$ticket->ticket_number, $agent ? $ticket->requester_name : $ticket->category?->name])->filter()->implode(' · ')">
                <x-slot:badge><x-sg.badge-status type="ticket_status" :value="$ticket->status" /></x-slot:badge>
                <div class="d-flex flex-wrap gap-1 mt-2">
                    <x-sg.badge-priority :value="$ticket->priority" code />
                    @if ($agent)@include('tickets.partials.sla-badge')@endif
                    @if ($ticket->awaitingApproval())<x-sg.badge-status type="ticket_approval" :value="$ticket->approval_status" />@endif
                </div>
            </x-sg.list-card>
        @endforeach
    </div>

    <x-sg.table class="d-none d-lg-block" caption="Daftar tiket">
        <x-slot:head>
            <tr>
                @if ($selectable)
                    <th scope="col" style="width:40px"><input type="checkbox" class="form-check-input" aria-label="Pilih semua" x-data
                        @change="document.querySelectorAll('[data-select-for={{ $selectable }}]').forEach(c => { c.checked = $event.target.checked; c.dispatchEvent(new Event('change', { bubbles: true })) })"></th>
                @endif
                <th scope="col">Tiket</th>
                <th scope="col">Kategori</th>
                <th scope="col">Prioritas</th>
                <th scope="col">Status</th>
                <th scope="col">{{ $agent ? 'Handler' : 'Ditangani' }}</th>
                @if ($agent)<th scope="col">SLA</th>@endif
                <th scope="col">Dibuat</th>
            </tr>
        </x-slot:head>
        @foreach ($tickets as $ticket)
            <tr class="position-relative">
                @if ($selectable)
                    <td class="position-relative" style="z-index:2">
                        <input type="checkbox" class="form-check-input" name="ids[]" value="{{ $ticket->id }}" form="{{ $selectable }}"
                               data-select-for="{{ $selectable }}" aria-label="Pilih {{ $ticket->ticket_number }}">
                    </td>
                @endif
                <td style="max-width:380px">
                    <a href="{{ route('tickets.show', $ticket) }}" class="stretched-link d-block text-reset text-decoration-none fw-semibold sg-clamp-1">{{ $ticket->title }}</a>
                    <div class="small text-muted sg-clamp-1">{{ $ticket->ticket_number }}@if ($agent) · {{ $ticket->requester_name }}{{ $ticket->requester_department ? ' · '.$ticket->requester_department : '' }}@endif</div>
                </td>
                <td class="text-nowrap">{{ $ticket->category?->name ?? '-' }}</td>
                <td><x-sg.badge-priority :value="$ticket->priority" code /></td>
                <td>
                    <x-sg.badge-status type="ticket_status" :value="$ticket->status" />
                    @if ($ticket->awaitingApproval())<div class="mt-1"><x-sg.badge-status type="ticket_approval" :value="$ticket->approval_status" /></div>@endif
                </td>
                <td class="text-nowrap">{{ $ticket->assignedUser?->name ?? '—' }}</td>
                @if ($agent)<td>@include('tickets.partials.sla-badge')</td>@endif
                <td class="sg-num text-nowrap">{{ \App\Support\Format::date($ticket->created_at) }}</td>
            </tr>
        @endforeach
    </x-sg.table>
@endif
