{{--
    Timeline vertikal (approval laporan, riwayat & lacak tiket).
    :items = [['title' => 'Dikirim', 'meta' => 'Andi · 17 Okt 2026, 14:30', 'tone' => 'approved', 'icon' => 'bi-check-lg', 'note' => null], ...]
    tone: approved | pending | rejected | progress | open | closed
--}}
@props(['items' => []])
<ol {{ $attributes->class(['sg-timeline']) }}>
    @foreach ($items as $item)
        <li class="sg-timeline__item">
            <div class="sg-timeline__rail" aria-hidden="true">
                <span @class(['sg-timeline__dot', 'sg-timeline__dot--'.($item['tone'] ?? '') => ! empty($item['tone'])])>
                    <i class="bi {{ $item['icon'] ?? 'bi-circle-fill' }}"></i>
                </span>
                <span class="sg-timeline__line"></span>
            </div>
            <div class="sg-timeline__body">
                <div class="sg-timeline__title">{{ $item['title'] }}</div>
                @if (! empty($item['meta']))
                    <div class="sg-timeline__meta">{{ $item['meta'] }}</div>
                @endif
                @if (! empty($item['note']))
                    <div class="sg-timeline__note">{{ $item['note'] }}</div>
                @endif
            </div>
        </li>
    @endforeach
</ol>
