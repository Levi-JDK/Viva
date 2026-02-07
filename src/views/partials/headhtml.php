<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Quattrocento:wght@400;700&family=Lato:wght@400;700&display=swap"
		rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script>
        tailwind.config = {
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
                        sans: ['Inter', 'sans-serif'],
                        body: ['Lato', 'sans-serif'],
                    }
                }
            }
        }
    </script>
	<link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>src/styles/animations.css">
	<link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>src/styles/web.css">
	<link rel="stylesheet" type="text/css" href="<?= BASE_URL ?>src/styles/responsive.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
	<title>VIVA | Iniciar Sesión - Artesanías Colombianas</title>
</head>
<body class="flex flex-col min-h-screen font-sans text-oscuro bg-fondo-claro">