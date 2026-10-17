{{-- Kartu list mobile: badge + waktu, judul (maks 2 baris), meta 1 baris, chevron. Slot "badge" untuk badge status. --}}
@props(['href' => null, 'title', 'time' => null, 'meta' => null, 'selected' => false])
<div {{ $attributes->class(['sg-list-card', 'is-selected' => $selected]) }}>
    <div class="sg-list-card__body">
        <div class="sg-list-card__top">
            <span>{{ $badge ?? '' }}</span>
            @if ($time)
                <span class="sg-list-card__time">{{ $time }}</span>
            @endif
        </div>
        <div class="sg-list-card__title sg-clamp-2">
            @if ($href)
                <a href="{{ $href }}" class="stretched-link text-reset text-decoration-none">{{ $title }}</a>
            @else
                {{ $title }}
            @endif
        </div>
        @if ($meta)
            <div class="sg-list-card__meta sg-clamp-1">{{ $meta }}</div>
        @endif
        {{ $slot }}
    </div>
    @if ($href)
        <i class="bi bi-chevron-right sg-list-card__chevron" aria-hidden="true"></i>
    @endif
</div>
