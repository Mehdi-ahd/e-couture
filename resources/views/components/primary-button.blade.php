<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-tg-accent border border-transparent rounded-md font-semibold text-xs text-tg-text-on-accent uppercase tracking-widest hover:bg-tg-accent-dark focus:bg-tg-accent-dark active:bg-tg-accent-dark focus:outline-none focus:ring-2 focus:ring-tg-accent focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
