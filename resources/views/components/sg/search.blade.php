{{-- Kolom pencarian dengan ikon. Label tetap ada untuk pembaca layar. --}}
@props(['name' => 'search', 'value' => null, 'placeholder' => 'Cari…', 'label' => 'Cari'])
@php($id = $attributes->get('id', 'sg-search-'.$name))
<div class="sg-search">
    <label for="{{ $id }}" class="visually-hidden">{{ $label }}</label>
    <i class="bi bi-search" aria-hidden="true"></i>
    <input type="search" id="{{ $id }}" name="{{ $name }}" value="{{ $value ?? request($name) }}" placeholder="{{ $placeholder }}"
           enterkeyhint="search" {{ $attributes->except('id')->class(['form-control']) }}>
</div>
