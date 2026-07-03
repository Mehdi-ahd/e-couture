@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-tg-text-primary']) }}>
    {{ $value ?? $slot }}
</label>
