{{-- Badge SLA. :minutes = sisa menit jam kerja (negatif = lewat). :paused = SLA dijeda --}}
@props(['minutes' => null, 'paused' => false])
@php
    if ($paused) {
        [$tone, $icon, $text] = ['paused', 'bi-pause-circle', 'SLA dijeda'];
    } elseif ($minutes === null) {
        [$tone, $icon, $text] = ['paused', 'bi-dash-circle', 'Tanpa SLA'];
    } elseif ($minutes < 0) {
        [$tone, $icon, $text] = ['breach', 'bi-exclamation-triangle-fill', 'Lewat '.\App\Support\Format::duration(abs($minutes))];
    } else {
        $tone = $minutes < config('sigap-ui.sla_warn_minutes') ? 'warn' : 'ok';
        [$icon, $text] = ['bi-clock', 'Sisa '.\App\Support\Format::duration($minutes)];
    }
@endphp
<span {{ $attributes->class(['sg-sla', "sg-sla--{$tone}"]) }}><i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $text }}</span>
