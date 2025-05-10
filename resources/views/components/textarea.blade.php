@props(['disabled' => false, 'label' => '', 'name', 'value' => null, 'required' => false, 'placeholder' => '', 'rows' => 3])

<div class="mb-3">
    @if($label)
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    @endif
    
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        {!! $placeholder ? "placeholder=\"$placeholder\"" : '' !!}
        {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}
    >{{ old($name, $value) }}</textarea>
    
    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div> 