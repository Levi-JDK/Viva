/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.{php,html,js}",
        "./index.php"
    ],
    theme: {
        extend: {
            colors: {
                principal: '#b15b0a',
                secundario: '#a04e07',
                claro: '#F5E9D3',
                oscuro: '#4A3B2B',
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
