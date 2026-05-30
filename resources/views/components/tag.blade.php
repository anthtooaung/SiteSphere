@props([
    'label',
    'value',
    'checked' => false,
])

<label {{ $attributes->class('tag-check')->merge(['data-filter-component' => 'tag']) }}>
    <input type="checkbox" value="{{ $value }}" @checked($checked)>
    <span>{{ $label }}</span>
</label>
