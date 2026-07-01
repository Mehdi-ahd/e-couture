@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-tg-text-green']) }}>
        {{ $status }}
    </div>
@endif
