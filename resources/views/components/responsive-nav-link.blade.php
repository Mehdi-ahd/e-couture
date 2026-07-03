@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-tg-accent dark:border-tg-accent text-start text-base font-medium text-tg-accent dark:text-tg-accent bg-tg-accent/5 dark:bg-tg-accent/5 focus:outline-none focus:text-tg-accent-dark dark:focus:text-tg-accent focus:bg-tg-accent/10 dark:focus:bg-tg-accent/10 focus:border-tg-accent-dark dark:focus:border-tg-accent transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-tg-text-secondary hover:text-tg-text-primary dark:hover:text-tg-text-primary hover:bg-tg-bg-secondary dark:hover:bg-tg-bg-secondary hover:border-tg-border dark:hover:border-tg-border focus:outline-none focus:text-tg-text-primary dark:focus:text-tg-text-primary focus:bg-tg-bg-secondary dark:focus:bg-tg-bg-secondary focus:border-tg-border dark:focus:border-tg-border transition duration-150 ease-in-out';
$mergedAttributes = $attributes->merge(['class' => $classes]);
@endphp

<a {{ $mergedAttributes }}>
    {{ $slot }}
</a>
