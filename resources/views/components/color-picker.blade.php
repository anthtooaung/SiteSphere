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
    h: 210, s: 1, v: 1,
    svDragging: false,

    hexToRgb(hex) {
        hex = hex.replace('#', '');
        return {
            r: parseInt(hex.slice(0, 2), 16),
            g: parseInt(hex.slice(2, 4), 16),
            b: parseInt(hex.slice(4, 6), 16)
        };
    },
    rgbToHex(r, g, b) {
        return '#' + [r, g, b].map(v => Math.min(255, Math.max(0, Math.round(v))).toString(16).padStart(2, '0')).join('');
    },
    rgbToHsv(r, g, b) {
        r /= 255; g /= 255; b /= 255;
        const max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min;
        let h = 0;
        if (d !== 0) {
            if (max === r) h = 60 * (((g - b) / d) % 6);
            else if (max === g) h = 60 * ((b - r) / d + 2);
            else h = 60 * ((r - g) / d + 4);
        }
        if (h < 0) h += 360;
        return { h, s: max === 0 ? 0 : d / max, v: max };
    },
    hsvToRgb(h, s, v) {
        h = ((h % 360) + 360) % 360;
        const c = v * s, x = c * (1 - Math.abs((h / 60) % 2 - 1)), m = v - c;
        let r = 0, g = 0, b = 0;
        if (h < 60) { r = c; g = x; }
        else if (h < 120) { r = x; g = c; }
        else if (h < 180) { g = c; b = x; }
        else if (h < 240) { g = x; b = c; }
        else if (h < 300) { r = x; b = c; }
        else { r = c; b = x; }
        return { r: Math.round((r + m) * 255), g: Math.round((g + m) * 255), b: Math.round((b + m) * 255) };
    },

    initPicker() {
        const rgb = this.hexToRgb(this.color);
        const hsv = this.rgbToHsv(rgb.r, rgb.g, rgb.b);
        this.h = hsv.h; this.s = hsv.s; this.v = hsv.v;
    },

    get hueColor() {
        const rgb = this.hsvToRgb(this.h, 1, 1);
        return this.rgbToHex(rgb.r, rgb.g, rgb.b);
    },
    get svStyle() {
        return 'background: linear-gradient(to top, #000, transparent), linear-gradient(to right, #fff, ' + this.hueColor + ')';
    },
    get svKnobStyle() {
        return 'left:' + (this.s * 100) + '%;top:' + ((1 - this.v) * 100) + '%';
    },
    get hueKnobStyle() {
        return 'left:' + ((this.h / 360) * 100) + '%;background:' + this.hueColor;
    },
    get hexField() {
        return this.color.toUpperCase();
    },
    get rgbFields() {
        return this.hexToRgb(this.color);
    },

    onSvPointerDown(e) {
        this.svDragging = true;
        this.$refs.svArea.setPointerCapture(e.pointerId);
        this.updateSvFromEvent(e);
    },
    onSvPointerMove(e) {
        if (this.svDragging) this.updateSvFromEvent(e);
    },
    onSvPointerUp() {
        this.svDragging = false;
    },
    updateSvFromEvent(e) {
        const rect = this.$refs.svArea.getBoundingClientRect();
        this.s = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
        this.v = Math.min(1, Math.max(0, 1 - ((e.clientY - rect.top) / rect.height)));
        this.applyFromHSV();
    },

    onHueInput(e) {
        this.h = Number(e.target.value);
        this.applyFromHSV();
    },

    onHexInput(e) {
        let val = String(e.target.value || '').trim();
        if (!val.startsWith('#')) val = '#' + val;
        if (/^#[0-9a-fA-F]{6}$/.test(val)) {
            this.color = val;
            const rgb = this.hexToRgb(val);
            const hsv = this.rgbToHsv(rgb.r, rgb.g, rgb.b);
            this.h = hsv.h; this.s = hsv.s; this.v = hsv.v;
            this.$dispatch('color-change', { name: '{{ $name }}', value: val });
        }
    },

    onRgbInput() {
        const r = Math.min(255, Math.max(0, parseInt(this.$refs.rInput.value) || 0));
        const g = Math.min(255, Math.max(0, parseInt(this.$refs.gInput.value) || 0));
        const b = Math.min(255, Math.max(0, parseInt(this.$refs.bInput.value) || 0));
        const hex = this.rgbToHex(r, g, b);
        this.color = hex;
        const hsv = this.rgbToHsv(r, g, b);
        this.h = hsv.h; this.s = hsv.s; this.v = hsv.v;
        this.$dispatch('color-change', { name: '{{ $name }}', value: hex });
    },

    applyFromHSV() {
        const rgb = this.hsvToRgb(this.h, this.s, this.v);
        const hex = this.rgbToHex(rgb.r, rgb.g, rgb.b);
        this.color = hex;
        this.$dispatch('color-change', { name: '{{ $name }}', value: hex });
    },

    toggle() { this.open = !this.open; if (this.open) this.initPicker(); },
    closePanel() { this.open = false; }
}" x-init="$watch('color', val => { $dispatch('color-change', { name: '{{ $name }}', value: val }); })"
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
            @input="onHexInput($event)"
            @blur="if (!/^#[0-9A-Fa-f]{6}$/.test(color)) color = '{{ $value }}'"
            placeholder="#FF5733"
            maxlength="7"
            spellcheck="false"
            autocomplete="off">

        <!-- Color Picker Popup -->
        <div class="color-picker-backdrop" x-show="open" @click="closePanel()"></div>
        <div class="color-picker-popup" x-show="open" x-transition:enter="popup-enter" x-transition:leave="popup-leave">
            <div class="color-picker-popup-header">
                <span class="color-picker-popup-title">Select Color</span>
                <button type="button" class="color-picker-popup-close" @click="closePanel()">&times;</button>
            </div>
            <div class="color-picker-sv-area" x-ref="svArea"
                :style="svStyle"
                @pointerdown="onSvPointerDown($event)"
                @pointermove="onSvPointerMove($event)"
                @pointerup="onSvPointerUp()"
                @pointercancel="onSvPointerUp()">
                <span class="color-picker-sv-knob" :style="svKnobStyle"></span>
            </div>
            <div class="color-picker-hue-row">
                <div class="color-picker-hue-track"></div>
                <span class="color-picker-hue-knob" :style="hueKnobStyle"></span>
                <input type="range" class="color-picker-hue-input"
                    min="0" max="360" step="1"
                    :value="Math.round(h)"
                    @input="onHueInput($event)"
                    aria-label="Hue">
            </div>
            <div class="color-picker-fields">
                <div class="color-field">
                    <label>Hex</label>
                    <input type="text" :value="hexField"
                        @input="onHexInput($event)"
                        @blur="onHexInput($event)"
                        @keydown.enter="onHexInput($event)"
                        maxlength="7" spellcheck="false" autocomplete="off">
                </div>
                <div class="color-field">
                    <label>R</label>
                    <input type="number" x-ref="rInput" :value="rgbFields.r"
                        @input="onRgbInput()" min="0" max="255">
                </div>
                <div class="color-field">
                    <label>G</label>
                    <input type="number" x-ref="gInput" :value="rgbFields.g"
                        @input="onRgbInput()" min="0" max="255">
                </div>
                <div class="color-field">
                    <label>B</label>
                    <input type="number" x-ref="bInput" :value="rgbFields.b"
                        @input="onRgbInput()" min="0" max="255">
                </div>
            </div>
        </div>
    </div>
</div>
