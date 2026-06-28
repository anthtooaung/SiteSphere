@props([
    'name' => 'color',
    'value' => '#6c5ce7',
    'id' => null,
    'label' => null,
])

@php
    $inputId = $id ?? 'color-picker-' . uniqid();
@endphp

<div class="color-picker-component" x-data="{
    color: '{{ $value }}',
    updateFromText(e) {
        let val = e.target.value.trim();
        if (val && !val.startsWith('#')) val = '#' + val;
        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
            this.color = val;
            this.$dispatch('color-change', { name: '{{ $name }}', value: val });
        }
    },
    updateFromPicker(e) {
        this.color = e.target.value;
        this.$dispatch('color-change', { name: '{{ $name }}', value: e.target.value });
    }
}" x-init="$watch('color', val => { $refs.nativePicker.value = val; $dispatch('color-change', { name: '{{ $name }}', value: val }); })">
    @if ($label)
        <label for="{{ $inputId }}" class="color-picker-label">{{ $label }}</label>
    @endif
    <div class="color-picker-row">
        <input type="hidden" name="{{ $name }}" x-model="color">
        <button type="button" class="color-picker-swatch"
            :style="{ backgroundColor: color }"
            @click="$refs.nativePicker.click()"
            aria-label="Pick color">
        </button>
        <input type="text" class="color-picker-text"
            id="{{ $inputId }}"
            :value="color"
            @input="updateFromText($event)"
            @blur="if (!/^#[0-9A-Fa-f]{6}$/.test(color)) color = '{{ $value }}'"
            placeholder="#FF5733"
            maxlength="7"
            spellcheck="false"
            autocomplete="off">
        <input type="color" x-ref="nativePicker"
            :value="color"
            @input="updateFromPicker($event)"
            class="color-picker-native"
            tabindex="-1">
    </div>
</div>
