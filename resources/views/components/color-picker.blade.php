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
    open: false,
    presetColors: [
        '#FF6B6B', '#EE5A24', '#F79F1F', '#FFC312', '#A3CB38',
        '#009432', '#0652DD', '#1B1464', '#6C5CE7', '#D980FA',
        '#FDA7DF', '#E77F67', '#CF6A87', '#574B90', '#303952',
        '#F8EFBA', '#58B19F', '#1B9CFC', '#3B3B98', '#9B59B6',
        '#1ABC9C', '#2ECC71', '#3498DB', '#9B59B6', '#E74C3C',
        '#34495E', '#F39C12', '#1ABC9C', '#2C3E50', '#E67E22'
    ],
    toggle() {
        this.open = !this.open;
    },
    selectPreset(c) {
        this.color = c;
        this.open = false;
        this.$dispatch('color-change', { name: '{{ $name }}', value: c });
    },
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
        this.open = false;
        this.$dispatch('color-change', { name: '{{ $name }}', value: e.target.value });
    },
    closePanel(e) {
        if (!this.$el.contains(e.target)) {
            this.open = false;
        }
    }
}" x-init="$watch('color', val => { $refs.nativePicker.value = val; $dispatch('color-change', { name: '{{ $name }}', value: val }); })"
@click.outside="open = false">
    @if ($label)
        <label for="{{ $inputId }}" class="color-picker-label">{{ $label }}</label>
    @endif
    <div class="color-picker-row">
        <input type="hidden" name="{{ $name }}" x-model="color">
        <button type="button" class="color-picker-swatch"
            :style="{ backgroundColor: color }"
            @click="toggle()"
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

        <!-- Color Picker Popup -->
        <div class="color-picker-popup" x-show="open" x-transition:enter="popup-enter" x-transition:leave="popup-leave" @click.away="open = false">
            <div class="color-picker-popup-header">
                <span class="color-picker-popup-title">Select Color</span>
                <button type="button" class="color-picker-popup-close" @click="open = false">&times;</button>
            </div>
            <div class="color-picker-popup-grid">
                <template x-for="(preset, index) in presetColors" :key="index">
                    <button type="button"
                        class="color-picker-preset"
                        :style="{ backgroundColor: preset }"
                        :class="{ 'active': color === preset }"
                        @click="selectPreset(preset)"
                        :aria-label="'Select color ' + preset">
                    </button>
                </template>
            </div>
            <div class="color-picker-popup-custom">
                <button type="button" class="color-picker-custom-btn" @click="$refs.nativePicker.click()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v8M8 12h8"/>
                    </svg>
                    Custom Color
                </button>
            </div>
        </div>
    </div>
</div>
