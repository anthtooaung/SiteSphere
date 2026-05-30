@props([
    'label',
    'value',
    'checked' => false,
])

<label {{ $attributes->class('category-check')->merge(['data-filter-component' => 'category']) }}>
    <input type="checkbox" value="{{ $value }}" @checked($checked)>
    <span>{{ $label }}</span>
</label>
