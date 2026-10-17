{{-- Chip filter (wrap, tanpa scroll horizontal). :items = [['label' => 'Menunggu', 'href' => url, 'active' => bool, 'count' => int|null], ...] --}}
@props(['items' => [], 'label' => 'Filter'])
<nav {{ $attributes->class(['sg-chip-row']) }} aria-label="{{ $label }}">
    @foreach ($items as $item)
        <a href="{{ $item['href'] }}" @class(['sg-chip', 'active' => $item['active'] ?? false])
           @if ($item['active'] ?? false) aria-current="true" @endif>
            {{ $item['label'] }}
            @isset($item['count'])
                <span class="sg-chip__count sg-num">{{ \App\Support\Format::number($item['count']) }}</span>
            @endisset
        </a>
    @endforeach
    {{ $slot }}
</nav>
