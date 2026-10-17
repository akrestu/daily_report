{{-- Empty state: ikon garis + kalimat ramah + CTA (slot) --}}
@props(['icon' => 'bi-inbox', 'title', 'text' => null])
<div {{ $attributes->class(['sg-empty']) }}>
    <div class="sg-empty__icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></div>
    <p class="sg-empty__title">{{ $title }}</p>
    @if ($text)
        <p class="sg-empty__text">{{ $text }}</p>
    @endif
    {{ $slot }}
</div>
