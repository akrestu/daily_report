{{-- Kartu KPI. noteTone: pending | approved | rejected (warna catatan di bawah angka) --}}
@props(['label', 'value', 'note' => null, 'noteTone' => null, 'href' => null])
<div {{ $attributes->class(['card sg-kpi position-relative']) }}>
    <div class="sg-kpi__label">{{ $label }}</div>
    <div class="sg-kpi__value">{{ $value }}</div>
    @if ($note)
        <div @class(['sg-kpi__note', "sg-kpi__note--{$noteTone}" => $noteTone])>{{ $note }}</div>
    @endif
    @if ($href)
        <a href="{{ $href }}" class="stretched-link" aria-label="{{ $label }}"></a>
    @endif
</div>
