<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada | VIVA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-orange-50 to-amber-50 min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-2xl">
        <!-- Icono de error -->
        <div class="mb-8">
            <i class="fas fa-compass text-9xl text-orange-500 opacity-50"></i>
        </div>
        
        <!-- Título del error -->
        <h1 class="text-6xl md:text-8xl font-bold text-gray-800 mb-4">404</h1>
        <h2 class="text-2xl md:text-3xl font-semibold text-gray-700 mb-6">
            Página no encontrada
        </h2>
        
        <!-- Mensaje descriptivo -->
        <p class="text-lg text-gray-600 mb-8">
            Lo sentimos, la página que buscas no existe o ha sido movida.
        </p>
        
        <!-- Botones de acción -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= BASE_URL ?>" 
               class="bg-gradient-to-r from-orange-500 to-amber-600 text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition-all duration-300 inline-flex items-center justify-center space-x-2">
                <i class="fas fa-home"></i>
                <span>Ir al Inicio</span>
            </a>
            <button onclick="history.back()" 
                    class="bg-white border-2 border-orange-500 text-orange-600 px-8 py-3 rounded-full font-semibold hover:bg-orange-50 transition-all duration-300 inline-flex items-center justify-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Volver Atrás</span>
            </button>
        </div>
        
        <!-- Información adicional -->
        <div class="mt-12 text-sm text-gray-500">
            <p>Si crees que esto es un error, por favor contacta al soporte.</p>
        </div>
    </div>
</body>
</html>
