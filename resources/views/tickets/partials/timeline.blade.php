{{-- Riwayat status tiket. Dipakai di halaman internal dan halaman cek status publik. --}}
<ul class="list-unstyled mb-0 small">
    @foreach($ticket->histories as $history)
        <li class="d-flex gap-2 {{ $loop->last ? '' : 'mb-3' }}">
            <div class="flex-shrink-0 pt-1">
                <span class="d-inline-block rounded-circle {{ \App\Models\Ticket::STATUSES[$history->to_status]['badge'] ?? 'bg-secondary' }}"
                    style="width:10px;height:10px"></span>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold">{{ $history->description }}</div>
                <div class="text-muted" style="font-size:.75rem">
                    {{ $history->actor_name }} &bull; {{ $history->created_at->format('d M Y H:i') }}
                </div>
                @if($history->note)
                    <div class="bg-light rounded px-2 py-1 mt-1" style="white-space:pre-wrap">{{ $history->note }}</div>
                @endif
            </div>
        </li>
    @endforeach
</ul>
