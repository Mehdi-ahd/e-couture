import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto', ...defaultTheme.fontFamily.sans],
                medium: ['Roboto-Medium', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                tg: {
                    bg: {
                        primary: 'var(--tg-bg-primary)',
                        secondary: 'var(--tg-bg-secondary)',
                        surface: 'var(--tg-bg-surface)',
                        section: 'var(--tg-bg-section)',
                        header: 'var(--tg-bg-header)',
                        input: 'var(--tg-bg-input)',
                        'input-activated': 'var(--tg-bg-input-activated)',
                        card: 'var(--tg-bg-card)',
                    },
                    text: {
                        primary: 'var(--tg-text-primary)',
                        secondary: 'var(--tg-text-secondary)',
                        tertiary: 'var(--tg-text-tertiary)',
                        quaternary: 'var(--tg-text-quaternary)',
                        hint: 'var(--tg-text-hint)',
                        link: 'var(--tg-text-link)',
                        header: 'var(--tg-text-header)',
                        'header-subtitle': 'var(--tg-text-header-subtitle)',
                        'on-accent': 'var(--tg-text-on-accent)',
                        green: 'var(--tg-text-green)',
                        blue: 'var(--tg-text-blue)',
                    },
                    accent: {
                        DEFAULT: 'var(--tg-accent)',
                        dark: 'var(--tg-accent-dark)',
                        text: 'var(--tg-accent-text)',
                        'blue-icon': 'var(--tg-accent-blue-icon)',
                    },
                    border: {
                        DEFAULT: 'var(--tg-border)',
                        input: 'var(--tg-border-input)',
                        focused: 'var(--tg-border-focused)',
                    },
                    divider: 'var(--tg-divider)',
                    success: 'var(--tg-success)',
                    danger: {
                        DEFAULT: 'var(--tg-danger)',
                        bold: 'var(--tg-danger-bold)',
                        bg: 'var(--tg-danger-bg)',
                    },
                    warning: 'var(--tg-warning)',
                    badge: {
                        DEFAULT: 'var(--tg-badge)',
                        muted: 'var(--tg-badge-muted)',
                        text: 'var(--tg-badge-text)',
                    },
                    switch: {
                        track: 'var(--tg-switch-track)',
                        'track-checked': 'var(--tg-switch-track-checked)',
                    },
                    checkbox: {
                        bg: 'var(--tg-checkbox-bg)',
                        check: 'var(--tg-checkbox-check)',
                        unchecked: 'var(--tg-checkbox-unchecked)',
                    },
                    fab: {
                        bg: 'var(--tg-fab-bg)',
                        icon: 'var(--tg-fab-icon)',
                    },
                    'fast-scroll': {
                        active: 'var(--tg-fast-scroll-active)',
                        inactive: 'var(--tg-fast-scroll-inactive)',
                    },
                },
            },
            borderRadius: {
                tg: '14px',
            },
            boxShadow: {
                tg: '0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04)',
                'tg-md': '0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.04)',
                'tg-lg': '0 10px 25px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.04)',
            },
        },
    },

    plugins: [forms],
};
