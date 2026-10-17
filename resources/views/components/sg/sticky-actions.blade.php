{{-- Bar aksi utama di area jempol (mobile/tablet). Desktop: aksi ada di header, jadi disembunyikan kecuali :always. --}}
@props(['always' => false])
<div {{ $attributes->class(['sg-sticky-actions', 'd-lg-none' => ! $always]) }}>
    {{ $slot }}
</div>
