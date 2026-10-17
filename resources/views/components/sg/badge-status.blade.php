{{-- Badge status pill (dot + label). type: report_approval | ticket_status | ticket_approval (config/sigap-ui.php) --}}
@props(['type' => 'report_approval', 'value'])
@php($map = config("sigap-ui.{$type}.{$value}"))
<span {{ $attributes->class(['sg-badge', 'sg-badge--'.($map['tone'] ?? 'closed')]) }}>{{ $map['label'] ?? \Illuminate\Support\Str::headline((string) $value) }}</span>
