@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-tg-border dark:border-tg-border-input dark:bg-tg-bg-input dark:text-tg-text-primary focus:border-tg-accent dark:focus:border-tg-accent focus:ring-tg-accent dark:focus:ring-tg-accent rounded-md shadow-sm']) }}>
