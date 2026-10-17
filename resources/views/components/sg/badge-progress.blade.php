{{-- Progres pekerjaan laporan (daily_reports.status): label kotak + ikon, sengaja beda bentuk dari badge approval --}}
@props(['value'])
@php($map = config("sigap-ui.report_progress.{$value}"))
<span {{ $attributes->class(['sg-progress-label', "sg-progress-label--{$value}"]) }}>
    <i class="bi {{ $map['icon'] ?? 'bi-square' }}" aria-hidden="true"></i>{{ $map['label'] ?? \Illuminate\Support\Str::headline((string) $value) }}
</span>
