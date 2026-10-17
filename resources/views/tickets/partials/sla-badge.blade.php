{{-- Badge SLA penyelesaian (logika TicketSla::summary). Dipakai daftar & detail tiket. --}}
@php($sla = \App\Services\TicketSla::summary($ticket)['resolution'])
@switch($sla['state'])
    @case('running')
        <span @class(['sg-sla', 'sg-sla--warn' => $sla['remaining'] <= config('sigap-ui.sla_warn_minutes'), 'sg-sla--ok' => $sla['remaining'] > config('sigap-ui.sla_warn_minutes')])
              title="Tenggat: {{ \App\Support\Format::datetime($sla['due']) }}">
            <i class="bi bi-clock" aria-hidden="true"></i>Sisa {{ \App\Services\TicketSla::formatMinutes($sla['remaining']) }}
        </span>
        @break
    @case('breached')
        <span class="sg-sla sg-sla--breach" title="Tenggat: {{ \App\Support\Format::datetime($sla['due']) }}"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>Lewat SLA</span>
        @break
    @case('paused')
        <span class="sg-sla sg-sla--paused"><i class="bi bi-pause-circle" aria-hidden="true"></i>SLA dijeda</span>
        @break
    @case('met')
        <span class="sg-sla sg-sla--ok"><i class="bi bi-check-lg" aria-hidden="true"></i>Tercapai</span>
        @break
    @default
        <span class="text-muted small">-</span>
@endswitch
