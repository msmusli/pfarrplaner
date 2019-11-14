<div class="form-group" @if(isset($id)) id="{{ $id }}" @endif>
    <label for="{{ $name }}">{{ $label }}</label>
    <input type="text" class="form-control @if(isset($class)) {{ $class }}@endif" name="{{ $name }}" @if(isset($id)) id="{{ $id }}_input" @endif @if(isset($value)) value="{{ $value }}" @endif @if(isset($placeholder)) placeholder="{{ $placeholder }}" @endif @if(isset($enabled) && (!$enabled)) disabled @endif/>
</div>