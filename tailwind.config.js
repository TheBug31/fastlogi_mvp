/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
        './app/Support/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                canvas: '#F5F7FA',
                sidebar: '#0B1220',
                'sidebar-active': '#162033',
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
