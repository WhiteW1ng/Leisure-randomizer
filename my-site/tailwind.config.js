import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Цвета берутся из переменных в resources/css/app.css (светлая и тёмная тема).
const v = (name) => `rgb(var(--c-${name}) / <alpha-value>)`;

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                serif: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                page: v('page'),
                card: v('card'),
                field: v('field'),
                chip: v('chip'),
                line: v('line'),
                ink: v('ink'),
                'ink-soft': v('ink-soft'),
                mute: v('mute'),
                accent: v('accent'),
                'accent-hover': v('accent-hover'),
                'accent-ink': v('accent-ink'),
                'accent-text': v('accent-text'),
                navy: v('navy'),
                'navy-soft': v('navy-soft'),
                'ok-bg': v('ok-bg'), 'ok-line': v('ok-line'), 'ok-text': v('ok-text'),
                'err-bg': v('err-bg'), 'err-line': v('err-line'), 'err-text': v('err-text'),
                'warn-bg': v('warn-bg'), 'warn-line': v('warn-line'), 'warn-text': v('warn-text'),
            },
        },
    },

    plugins: [forms],
};
