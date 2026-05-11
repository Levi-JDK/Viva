/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        "./src/**/*.{php,html,js}",
        "./index.php"
    ],
    theme: {
        // Breakpoints unificados: evitan puntos ciegos entre 375/640/768/1024/1280/1440.
        screens: {
            xs: '375px',
            sm: '640px',
            md: '768px',
            lg: '1024px',
            xl: '1280px',
            '2xl': '1440px',
        },
        extend: {
            colors: {
                principal: '#b15b0a',
                secundario: '#a04e07',
                claro: '#F5E9D3',
                oscuro: '#4A3B2B',
                'warm-cream': '#FDF8F4',
                'deep-earth': '#3E2723',
                'fondo-claro': '#fff',
                'fondo-oscuro': '#eee',
                'tierra-oscuro': '#8B4513',
                'tierra-medio': '#CD853F',
                'tierra-claro': '#DEB887',
                'verde-artesanal': '#6B8E23',
                'naranja-artesanal': '#D2691E',
                'beige-suave': '#F5F5DC',
            },
            fontFamily: {
                sans: ['Outfit', 'sans-serif'],
                body: ['Outfit', 'sans-serif'],
            }
        },
    },
    plugins: [],
}
