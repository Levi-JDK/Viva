<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta | VIVA - Artesanías Colombianas</title>
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/styles/web.css">
    <style>
        /* Smooth Transition Styles */
        .content-section {
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .content-section.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .content-section.closing {
            display: block; /* Ensure it stays visible during exit animation */
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none; /* Prevent clicks during exit */
        }
        
        /* Menu Item Active Indicator Animation */
        .menu-item {
            border-left: 4px solid transparent; /* Reserve space for border/shadow */
        }
        
        .menu-item.active-item {
            background-color: var(--beige-suave);
            border-left-color: var(--naranja-artesanal);
            /* box-shadow: inset 4px 0 0 0 var(--naranja-artesanal);  Alternative if border doesn't animate well */
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header Simple -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="<?= BASE_URL ?>" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-tierra-oscuro to-verde-artesanal rounded-lg flex items-center justify-center">
                        <img src="<?= BASE_URL ?>images/Logo.png" alt="VIVA">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-tierra-oscuro">VIVA</h1>
                        <p class="text-xs text-tierra-medio">Artesanías Colombianas</p>
                    </div>
                </a>

                <!-- User Info -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex items-center space-x-3">
                        <!-- Avatar: Siempre muestra la imagen desde foto_user (default: images/default.jpg) -->
                        <div class="w-10 h-10 rounded-full overflow-hidden">
                            
                            <img src="<?= BASE_URL . $foto_usuario ?>?v=<?= time() ?>" 
                                 alt="<?= htmlspecialchars($nombre_usuario) ?>" 
                                 class="w-full h-full object-cover">
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($nombre_completo) ?></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($email_usuario) ?></p>
                        </div>
                    </div>
                    <button id="mobile-menu-btn" class="lg:hidden text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Container -->
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Navigation -->
            <aside id="sidebar" class="lg:w-64 w-full bg-white rounded-xl shadow-lg p-6 h-fit lg:sticky lg:top-24">
                <h2 class="text-lg font-bold text-tierra-oscuro mb-4">Mi Cuenta</h2>
                <nav class="space-y-2">
                    <button onclick="showSection('profile')" class="menu-item active w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-300 hover:bg-beige-suave hover:translate-x-2">
                        <i class="fas fa-user text-tierra-medio"></i>
                        <span class="font-medium text-gray-700">Mi Perfil</span>
                    </button>
                    <button onclick="showSection('orders')" class="menu-item w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-300 hover:bg-beige-suave hover:translate-x-2">
                        <i class="fas fa-shopping-bag text-tierra-medio"></i>
                        <span class="font-medium text-gray-700">Mis Pedidos</span>
                    </button>
                    <button onclick="showSection('favorites')" class="menu-item w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-300 hover:bg-beige-suave hover:translate-x-2">
                        <i class="fas fa-heart text-tierra-medio"></i>
                        <span class="font-medium text-gray-700">Favoritos</span>
                    </button>
                    <button onclick="showSection('settings')" class="menu-item w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-300 hover:bg-beige-suave hover:translate-x-2">
                        <i class="fas fa-cog text-tierra-medio"></i>
                        <span class="font-medium text-gray-700">Configuración</span>
                    </button>
                    <hr class="my-4 border-gray-200">
                    <a href="<?= BASE_URL ?>" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-300 hover:bg-beige-suave text-gray-700 hover:translate-x-2">
                        <i class="fas fa-arrow-left text-tierra-medio"></i>
                        <span class="font-medium">Volver al Inicio</span>
                    </a>
                    <button onclick="logout()" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-300 hover:bg-red-50 text-red-600 hover:translate-x-2">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="font-medium">Cerrar Sesión</span>
                    </button>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1">
                
                <!-- Profile Section -->
                <section id="profile" class="content-section active">
                    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-tierra-oscuro">Mi Perfil</h2>
                            <div id="edit-buttons">
                                <button onclick="toggleEdit()" id="btn-editar" class="btn-primary text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition-all">
                                    <i class="fas fa-edit mr-2"></i>Editar
                                </button>
                                <div id="save-cancel-buttons" class="hidden space-x-2">
                                    <button onclick="saveProfile()" class="bg-verde-artesanal text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition-all">
                                        <i class="fas fa-save mr-2"></i>Guardar
                                    </button>
                                    <button onclick="cancelEdit()" class="bg-gray-500 text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition-all">
                                        <i class="fas fa-times mr-2"></i>Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Info -->
                        <div class="space-y-6">
                            <!-- Avatar with Pencil Icon Overlay -->
                            <!-- Avatar with Edit Overlay -->
                            <div class="flex items-center space-x-6">
                                <div id="avatar-container" class="avatar-container relative group w-24 h-24 cursor-pointer">
                                    <!-- 
                                        Avatar del usuario - Siempre muestra la imagen desde foto_user
                                        - Por defecto: images/default.jpg
                                        - Cache-busting: Se agrega timestamp para forzar recarga después de uploads
                                    -->
                                    <div class="w-full h-full rounded-full overflow-hidden">
                                        <img id="avatar-image" 
                                             src="<?= BASE_URL . $foto_usuario ?>?v=<?= time() ?>" 
                                             alt="<?= htmlspecialchars($nombre_completo) ?>" 
                                             class="w-full h-full object-cover">
                                    </div>
                                    
                                    <!-- Hover overlay con ícono de edición -->
                                    <div class="avatar-hover-overlay absolute inset-0 bg-black bg-opacity-40 rounded-full flex items-center justify-center text-white transition-all">
                                        <i class="fas fa-pencil-alt text-xl"></i>
                                    </div>
                                    
                                    <!-- Formulario oculto para upload automático -->
                                    <form id="profile-upload-form" action="<?= BASE_URL ?>src/functions/upload.php" method="POST" enctype="multipart/form-data" class="hidden">
                                        <input type="file" id="profile-image-input" name="imagen_perfil" accept="image/*">
                                    </form>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($nombre_completo) ?></h3>
                                    <p class="text-sm text-gray-500">Miembro desde <?= $fecha_formateada ?></p>
                                </div>
                            </div>

                            <!-- Info Grid -->
                            <form id="profile-form" class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre </label>
                                    <input type="text" id="input-nombre" value="<?= htmlspecialchars($nombre_usuario) ?>" class="profile-input w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed focus:border-tierra-medio focus:outline-none transition-all" disabled>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Apellido</label>
                                    <input type="text" id="input-apellido" value="<?= htmlspecialchars($apellido_usuario) ?>" class="profile-input w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed focus:border-tierra-medio focus:outline-none transition-all" disabled>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico</label>
                                    <input type="email" id="input-email" value="<?= htmlspecialchars($email_usuario) ?>" class="profile-input w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed focus:border-tierra-medio focus:outline-none transition-all" disabled>
                                </div>
                                <!-- <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                                    <input type="tel" id="input-telefono" value="+57 300 123 4567" class="profile-input w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed focus:border-tierra-medio focus:outline-none transition-all" disabled>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ciudad</label>
                                    <input type="text" id="input-ciudad" value="Bogotá, Colombia" class="profile-input w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed focus:border-tierra-medio focus:outline-none transition-all" disabled>
                                </div> -->
                            </div>    
                    </div>
                </section>

                <!-- Orders Section -->
                <section id="orders" class="content-section">
                    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-tierra-oscuro mb-6">Mis Pedidos</h2>
                        
                        <!-- Orders List -->
                        <div class="space-y-4">
                            <!-- Order Item -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0">
                                            <img src="<?= BASE_URL ?>images/wayuu.jpg" alt="Producto" class="w-full h-full object-cover rounded-lg">
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">Pedido #12345</h3>
                                            <p class="text-sm text-gray-500">3 productos • 28 Enero 2025</p>
                                            <p class="text-sm font-semibold text-tierra-oscuro mt-1">$350.000</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:items-end space-y-2">
                                        <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                            En Camino
                                        </span>
                                        <button class="text-sm text-naranja-artesanal hover:underline">Ver detalles</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Another Order -->
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0">
                                            <img src="<?= BASE_URL ?>images/mochila-arhuaca.jpg" alt="Producto" class="w-full h-full object-cover rounded-lg">
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">Pedido #12344</h3>
                                            <p class="text-sm text-gray-500">1 producto • 15 Enero 2025</p>
                                            <p class="text-sm font-semibold text-tierra-oscuro mt-1">$280.000</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col md:items-end space-y-2">
                                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                            Entregado
                                        </span>
                                        <button class="text-sm text-naranja-artesanal hover:underline">Ver detalles</button>
                                    </div>
                                </div>
                            </div>

                          
                        </div>
                    </div>
                </section>

                <!-- Favorites Section -->
                <section id="favorites" class="content-section">
                    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-tierra-oscuro mb-6">Mis Favoritos</h2>
                        
                        <!-- Favorites Grid -->
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Favorite Item -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all group">
                                <div class="relative">
                                    <img src="<?= BASE_URL ?>images/wayuu.jpg" alt="Producto" class="w-full h-48 object-cover">
                                    <button class="absolute top-3 right-3 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-red-50 transition-all">
                                        <i class="fas fa-heart text-red-500"></i>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-800 mb-1">Canasto Wayuu Tradicional</h3>
                                    <p class="text-sm text-gray-500 mb-2">Comunidad Wayuu</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-bold text-tierra-oscuro">$120.000</span>
                                        <button class="btn-primary text-white px-4 py-2 rounded-lg text-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Another Favorite -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all group">
                                <div class="relative">
                                    <img src="<?= BASE_URL ?>images/mochila-arhuaca.jpg" alt="Producto" class="w-full h-48 object-cover">
                                    <button class="absolute top-3 right-3 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-red-50 transition-all">
                                        <i class="fas fa-heart text-red-500"></i>
                                    </button>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-800 mb-1">Mochila Arhuaca Original</h3>
                                    <p class="text-sm text-gray-500 mb-2">Comunidad Arhuaca</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-bold text-tierra-oscuro">$280.000</span>
                                        <button class="btn-primary text-white px-4 py-2 rounded-lg text-sm">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Settings Section -->
                <section id="settings" class="content-section">
                    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                        <h2 class="text-2xl font-bold text-tierra-oscuro mb-6">Configuración</h2>
                        
                        <div class="space-y-6">
                            <!-- Notifications -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Notificaciones</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <span class="text-gray-700">Notificaciones por email</span>
                                        <input type="checkbox" checked class="w-5 h-5 text-tierra-medio rounded">
                                    </label>
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <span class="text-gray-700">Ofertas y promociones</span>
                                        <input type="checkbox" checked class="w-5 h-5 text-tierra-medio rounded">
                                    </label>
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <span class="text-gray-700">Actualizaciones de pedidos</span>
                                        <input type="checkbox" checked class="w-5 h-5 text-tierra-medio rounded">
                                    </label>
                                </div>
                            </div>

                            <!-- Privacy -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Privacidad</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <span class="text-gray-700">Perfil público</span>
                                        <input type="checkbox" class="w-5 h-5 text-tierra-medio rounded">
                                    </label>
                                    <label class="flex items-center justify-between cursor-pointer">
                                        <span class="text-gray-700">Mostrar historial de compras</span>
                                        <input type="checkbox" class="w-5 h-5 text-tierra-medio rounded">
                                    </label>
                                </div>
                            </div>

                            <!-- Security -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Seguridad</h3>
                                <button class="w-full md:w-auto btn-primary text-white px-6 py-3 rounded-lg">
                                    <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>


    <script>
        let isTransitioning = false; // Flag to prevent rapid clicks
        
        function showSection(sectionId) {
            // Prevent multiple transitions at once
            if (isTransitioning) return;
            
            const currentSection = document.querySelector('.content-section.active');
            const targetSection = document.getElementById(sectionId);
            
            // Si la sección ya es la activa, no hacer nada
            if (currentSection && currentSection.id === sectionId) return;

            // Actualizar menú activo
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active-item');
                // Removemos las clases antiguas por si acaso
                item.classList.remove('bg-beige-suave', 'border-l-4', 'border-naranja-artesanal');
                
                if (item.getAttribute('onclick')?.includes(sectionId)) {
                    item.classList.add('active-item');
                }
            });

            // Animación de salida para la sección actual
            if (currentSection) {
                isTransitioning = true; // Set flag
                
                currentSection.classList.add('closing');
                currentSection.classList.remove('active');
                
                // Esperar a que termine la transición (300ms = 0.3s)
                setTimeout(() => {
                    currentSection.classList.remove('closing');
                    currentSection.style.display = 'none'; // Asegurar que se oculte
                    
                    if (targetSection) {
                        targetSection.style.display = 'block';
                        // Pequeño delay para permitir que el navegador renderice el display:block antes de la opacidad
                        requestAnimationFrame(() => {
                            targetSection.classList.add('active');
                            isTransitioning = false; // Clear flag
                        });
                    } else {
                        isTransitioning = false; // Clear flag even if no target
                    }
                }, 300);
            } else {
                // Si no hay sección activa (ej: carga inicial), mostrar directamente
                if (targetSection) {
                    targetSection.style.display = 'block';
                    requestAnimationFrame(() => {
                        targetSection.classList.add('active');
                    });
                }
            }
        }

        function logout() {
            if(confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                window.location.href = BASE_URL;
            }
        }

        // Profile Edit Functions
        let originalFormData = {};

        function toggleEdit() {
            const inputs = document.querySelectorAll('.profile-input');
            const btnEditar = document.getElementById('btn-editar');
            const saveCancelButtons = document.getElementById('save-cancel-buttons');
            
            // Save original data
            originalFormData = {
                nombre: document.getElementById('input-nombre').value,
                apellido: document.getElementById('input-apellido').value,
                email: document.getElementById('input-email').value
            };
            
            // Enable inputs
            inputs.forEach(input => {
                // No habilitar email si es inmutable
                if (input.id === 'input-email') return;

                input.disabled = false;
                input.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
                input.classList.add('bg-white', 'text-gray-800');
            });
            
            // Toggle buttons
            btnEditar.classList.add('hidden');
            saveCancelButtons.classList.remove('hidden');
            saveCancelButtons.classList.add('flex');
        }

        async function saveProfile() {
            const nombre = document.getElementById('input-nombre').value;
            const apellido = document.getElementById('input-apellido').value;
            const btnGuardar = document.querySelector('#save-cancel-buttons button:first-child');
            const originalText = btnGuardar.innerHTML;

            // Mostrar estado de carga
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';

            const formData = new FormData();
            formData.append('accion', 'update_profile');
            formData.append('nombre', nombre);
            formData.append('apellido', apellido);

            try {
                const response = await fetch(BASE_URL + 'dashboard', {
                    method: 'POST',
                    body: formData
                });

                // Verificar si la respuesta es JSON válido
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Respuesta no válida del servidor");
                }

                const data = await response.json();

                if (data.clase === 'mensaje-exito') {
                    showToast(data.mensaje, 'success');
                    
                    // Actualizar UI
                    document.querySelector('h3.text-xl').textContent = `${nombre} ${apellido}`;
                    document.getElementById('avatar-image').alt = `${nombre} ${apellido}`;
                    
                    // Actualizar datos originales
                    originalFormData.nombre = nombre;
                    originalFormData.apellido = apellido; // Guardar apellido también

                    disableEditMode();
                } else {
                    showToast('Error: ' + data.mensaje, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al conectar con el servidor', 'error');
            } finally {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = originalText;
            }
        }

        function cancelEdit() {
            // Restore original data
            document.getElementById('input-nombre').value = originalFormData.nombre;
            document.getElementById('input-apellido').value = originalFormData.apellido;
            // No need to restore email as it's disabled
            
            // Disable editing mode
            disableEditMode();
        }

        function disableEditMode() {
            const inputs = document.querySelectorAll('.profile-input');
            const btnEditar = document.getElementById('btn-editar');
            const saveCancelButtons = document.getElementById('save-cancel-buttons');
            
            // Disable all inputs
            inputs.forEach(input => {
                input.disabled = true;
                input.classList.remove('bg-white', 'text-gray-800');
                input.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
            });
            
            // Toggle buttons
            btnEditar.classList.remove('hidden');
            saveCancelButtons.classList.remove('flex');
            saveCancelButtons.classList.add('hidden');
        }

        // Mobile menu toggle
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hidden');
        });

        // Check for hash in URL on page load and navigate to section
        window.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1); // Remove the '#'
            if (hash) {
                // Valid sections: profile, orders, favorites, settings
                const validSections = ['profile', 'orders', 'favorites', 'settings'];
                if (validSections.includes(hash)) {
                    // Hide all sections
                    document.querySelectorAll('.content-section').forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    // Remove active class from all menu items
                    document.querySelectorAll('.menu-item').forEach(item => {
                        item.classList.remove('bg-beige-suave', 'border-l-4', 'border-naranja-artesanal');
                    });
                    
                    // Show the section from hash
                    const targetSection = document.getElementById(hash);
                    if (targetSection) {
                        targetSection.classList.add('active');
                        
                        // Find and activate the corresponding menu item
                        const menuItems = document.querySelectorAll('.menu-item');
                        menuItems.forEach(item => {
                            if (item.getAttribute('onclick')?.includes(hash)) {
                                item.classList.add('bg-beige-suave', 'border-l-4', 'border-naranja-artesanal');
                            }
                        });
                    }
                }
            }
        });
    </script>
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>
    <script src="<?= BASE_URL ?>src/scripts/toast.js"></script>
    <script src="<?= BASE_URL ?>src/scripts/profile.js"></script>
</body>
</html>
