{{-- Riwayat status tiket. Dipakai di halaman internal dan halaman cek status publik. --}}
@php
    $icons = ['open' => 'bi-inbox', 'assigned' => 'bi-person-check', 'in_progress' => 'bi-tools', 'pending' => 'bi-hourglass-split',
              'resolved' => 'bi-check-lg', 'closed' => 'bi-lock', 'cancelled' => 'bi-x-lg'];
    $items = $ticket->histories->map(fn ($h) => [
        'title' => $h->description,
        'meta' => $h->actor_name.' · '.\App\Support\Format::datetime($h->created_at),
        'tone' => config('sigap-ui.ticket_status.'.$h->to_status.'.tone', 'closed'),
        'icon' => $icons[$h->to_status] ?? 'bi-circle-fill',
        'note' => $h->note,
    ])->all();
@endphp
<x-sg.timeline :items="$items" />
