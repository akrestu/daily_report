@props(['disabled' => false, 'label' => '', 'name', 'options' => [], 'selected' => null, 'required' => false, 'placeholder' => '-- Select Option --'])

<div class="mb-3">
    @if($label)
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    @endif
    
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-select' . ($errors->has($name) ? ' is-invalid' : '')]) }}
    >
        @if($placeholder)
        <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $value => $label)
        <option value="{{ $value }}" {{ (old($name, $selected) == $value) ? 'selected' : '' }}>
            {{ $label }}
        </option>
        @endforeach
    </select>
    
    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div> 