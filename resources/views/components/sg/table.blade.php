{{-- Tabel padat desktop (48px/baris). Slot "bulk" = bar aksi massal di atas tabel, slot "head" = <tr> header, slot default = baris <tbody> --}}
@props(['caption' => null])
<div {{ $attributes->class(['sg-table-wrap']) }}>
    {{ $bulk ?? '' }}
    <table class="table table-hover align-middle sg-table">
        @if ($caption)
            <caption class="visually-hidden">{{ $caption }}</caption>
        @endif
        <thead>{{ $head }}</thead>
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
