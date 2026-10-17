{{-- Badge level jabatan. Terima :user (pakai getRoleLevel) atau :level (0 = Admin). short → "L3" --}}
@props(['user' => null, 'level' => null, 'short' => false])
@php
    $lv = $level ?? $user?->getRoleLevel() ?? 0;
    $isAdmin = $lv === 0 || ($user && $user->isAdmin());
    $label = $isAdmin ? 'Admin' : ($short ? "L{$lv}" : "Level {$lv}");
@endphp
<span {{ $attributes->class(['sg-level', $isAdmin ? 'sg-level--admin' : "sg-level--{$lv}"]) }}>{{ $label }}</span>
