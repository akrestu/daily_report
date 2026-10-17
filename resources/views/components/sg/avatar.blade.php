{{-- Avatar: foto profil bila ada, selain itu inisial --}}
@props(['user', 'size' => 36])
@php
    $initials = collect(explode(' ', trim($user->name ?? '?')))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp
<span {{ $attributes->class(['sg-avatar']) }} style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * 0.38) }}px">
    @if ($user->profile_picture)
        <img src="{{ $user->profile_picture_url }}" alt="" loading="lazy">
    @else
        <span aria-hidden="true">{{ $initials }}</span>
    @endif
    <span class="visually-hidden">{{ $user->name }}</span>
</span>
