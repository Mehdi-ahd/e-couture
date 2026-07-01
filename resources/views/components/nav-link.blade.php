@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-tg-accent dark:border-tg-accent text-sm font-medium leading-5 text-tg-text-primary focus:outline-none focus:border-tg-accent-dark dark:focus:border-tg-accent transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-tg-text-tertiary hover:text-tg-text-primary dark:hover:text-tg-text-primary hover:border-tg-border dark:hover:border-tg-border focus:outline-none focus:text-tg-text-primary dark:focus:text-tg-text-primary focus:border-tg-border dark:focus:border-tg-border transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
