@props([
    'label',
    'value',
    'checked' => false,
])

<span {{ $attributes->class('category-check')->merge(['data-filter-component' => 'category', 'data-value' => $value]) }}>
    <span>{{ $label }}</span>
</span>
