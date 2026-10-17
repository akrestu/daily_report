{{-- Prioritas tiket (urgent|high|medium|low): badge kotak + ikon batang sinyal --}}
@props(['value', 'code' => false])
@php
    $map = config("sigap-ui.priority.{$value}", config('sigap-ui.priority.low'));
    $bars = ['urgent' => 3, 'high' => 3, 'medium' => 2, 'low' => 1][$value] ?? 1;
@endphp
<span {{ $attributes->class(['sg-priority', $map['class']]) }}>
    <span class="sg-signal" data-bars="{{ $bars }}" aria-hidden="true"><i></i><i></i><i></i></span>
    {{ $code ? $map['code'].' ' : '' }}{{ $map['label'] }}
</span>
