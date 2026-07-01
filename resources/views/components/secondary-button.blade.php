<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-tg-bg-primary dark:bg-tg-bg-card border border-tg-border dark:border-tg-border rounded-md font-semibold text-xs text-tg-text-primary uppercase tracking-widest shadow-sm hover:bg-tg-bg-secondary dark:hover:bg-tg-bg-secondary focus:outline-none focus:ring-2 focus:ring-tg-accent focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
