import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand:{
                    primary: '#208888',
                    secondary: '#ff644f',
                },
                neutral: {
                    bg: '#ffffff',
                    text: '#1a1a1a',
                    darkBg: '#030712',
                    darkText: '#f1f5f9',
                }
            }
        },
    },

    plugins: [forms],
};
