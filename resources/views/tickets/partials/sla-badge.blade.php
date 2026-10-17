@php($sla = \App\Services\TicketSla::summary($ticket)['resolution'])
@switch($sla['state'])
    @case('running')
        <span class="badge {{ $sla['remaining'] <= 60 ? 'bg-warning text-dark' : 'bg-light text-dark border' }}"
            title="Tenggat: {{ $sla['due']->format('d M Y H:i') }}">
            <i class="bi bi-stopwatch me-1"></i>{{ \App\Services\TicketSla::formatMinutes($sla['remaining']) }}
        </span>
        @break
    @case('breached')
        <span class="badge bg-danger" title="Tenggat: {{ $sla['due']->format('d M Y H:i') }}"><i class="bi bi-exclamation-triangle me-1"></i>Terlewat</span>
        @break
    @case('paused')
        <span class="badge bg-secondary"><i class="bi bi-pause-circle me-1"></i>Dijeda</span>
        @break
    @case('met')
        <span class="badge bg-success"><i class="bi bi-check-lg me-1"></i>Tercapai</span>
        @break
    @default
        <span class="text-muted small">-</span>
@endswitch
