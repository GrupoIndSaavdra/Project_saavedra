@props([
    'name',
    'alt' => null,
])

<img src="{{ icon($name) }}" alt="{{ $alt ?? ucwords(str_replace(['_', '-'], ' ', $name)) }}" {{ $attributes }}>
