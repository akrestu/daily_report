{{-- Judul halaman + subjudul + slot "actions" (tombol desktop; di mobile aksi utama ada di sticky bar) --}}
@props(['title', 'subtitle' => null])
<div {{ $attributes->class(['sg-page-header']) }}>
    <div class="min-w-0">
        <h1 class="sg-page-header__title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="sg-page-header__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="sg-page-header__actions">{{ $actions }}</div>
    @endisset
</div>
