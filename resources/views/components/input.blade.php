@props(['disabled' => false, 'label' => '', 'name', 'type' => 'text', 'value' => null, 'required' => false, 'placeholder' => '', 'autocomplete' => 'off'])

<div class="mb-3">
    @if($label)
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    @endif
    
    @if($type === 'password')
    <div class="input-group">
        <input 
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            {!! $placeholder ? "placeholder=\"$placeholder\"" : '' !!}
            {!! $autocomplete !== 'off' ? "autocomplete=\"$autocomplete\"" : '' !!}
            {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}
        >
        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="{{ $name }}">
            <i class="fas fa-eye"></i>
        </button>
    </div>
    @else
    <input 
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        {!! $placeholder ? "placeholder=\"$placeholder\"" : '' !!}
        {!! $autocomplete !== 'off' ? "autocomplete=\"$autocomplete\"" : '' !!}
        {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}
    >
    @endif
    
    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>

@if($type === 'password')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle the icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    });
</script>
@endif 