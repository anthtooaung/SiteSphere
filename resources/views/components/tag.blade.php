@props([
    'label',
    'value',
    'checked' => false,
])

<span {{ $attributes->class('tag-check')->merge(['data-filter-component' => 'tag', 'data-value' => $value]) }}>
    <span>{{ $label }}</span>
</span>
