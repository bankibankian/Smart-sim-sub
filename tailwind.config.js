import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                sans: ['Atkinson Hyperlegible Next', ...defaultTheme.fontFamily.sans],
                display: ['Atkinson Hyperlegible Next', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#0056D2',
                deep: '#002244',
                vibrant: '#00A859',
                muted: '#4A607A',
                border: '#DDE3EA',
                softBg: '#F4F7FC',
                white: '#FFFFFF',
            },
        },
    },

    plugins: [forms],
};
