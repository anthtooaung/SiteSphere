@props([
    'id',
    'name',
    'label' => null,
    'plain' => false,
    'bag' => 'default',
])

@if ($plain)
    <div class="field-group">
        @if(isset($labelRow))
            {{ $labelRow }}
        @elseif($label)
            <label for="{{ $id }}">{{ $label }}</label>
        @endif
        <div class="input-wrap">
            {{ $slot }}
            <input id="{{ $id }}" name="{{ $name }}" {{ $attributes->class(['is-invalid' => isset($errors) && $errors->getBag($bag)->has($name)]) }}>
            @if(isset($suffix))
                {{ $suffix }}
            @endif
        </div>
        @error($name, $bag)
            <p class="field-error-message">{{ $message }}</p>
        @enderror
    </div>
@else
    <div class="field-group">
        @if(isset($labelRow))
            {{ $labelRow }}
        @elseif($label)
            <label for="{{ $id }}" class="block mb-1 text-md font-medium ms-2">{{ $label }}</label>
        @endif
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                {{ $slot }}
            </div>
            <input id="{{ $id }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'block w-full ps-9 pe-3 py-2.5 bg-neutral-200/40 border text-md rounded-xl focus:border-[var(--accent)] shadow-md placeholder:text-body'])->class(['is-invalid' => isset($errors) && $errors->getBag($bag)->has($name)]) }} placeholder="{{ $label }}">
            @if(isset($suffix))
                {{ $suffix }}
            @endif
            @if(isset($errorInfo))
                {{$errorInfo}}
            @endif
        </div>
        @error($name, $bag)
            <p class="field-error-message">{{ $message }}</p>
        @enderror
    </div>
@endif
