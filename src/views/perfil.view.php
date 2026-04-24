<?php 
$page_title = "Mi Cuenta | VIVA - Artesanías Colombianas";
$body_class = "bg-gray-50 font-sans antialiased";
require_once __DIR__ . '/partials/base_head.php'; 
?>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="w-64 bg-white shadow-xl h-full flex-shrink-0 flex flex-col z-20 hidden lg:flex border-r border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <a href="<?= BASE_URL ?>" class="flex items-center space-x-3 group">
                     <div class="w-10 h-10 bg-gradient-to-br from-tierra-oscuro to-verde-artesanal rounded-xl flex items-center justify-center shadow-md group-hover:scale-105 transition-all">
                        <img src="<?= BASE_URL ?>images/Logo.png" alt="VIVA" class="w-full h-full object-contain rounded-xl">
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-tierra-oscuro">VIVA</h2>
                        <p class="text-xs text-gray-500 uppercase tracking-widest">Mi Cuenta</p>
                    </div>
                </a>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-6 space-y-1">
                <?php if (in_array(4, $menu_ids_usuario)): ?>
                <button data-action="show-section" data-section-id="profile" class="menu-item active-item w-full flex items-center space-x-4 px-6 py-3.5 text-left text-gray-600 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-user w-5 text-center text-tierra-medio"></i>
                    <span class="font-semibold">Mi Perfil</span>
                </button>
                <?php endif; ?>
                <?php if (in_array(5, $menu_ids_usuario)): ?>
                <button data-action="show-section" data-section-id="orders" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left text-gray-600 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-shopping-bag w-5 text-center text-tierra-medio"></i>
                    <span class="font-semibold">Mis Pedidos</span>
                </button>
                <?php endif; ?>
                <?php if (in_array(6, $menu_ids_usuario)): ?>
                <button data-action="show-section" data-section-id="favorites" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left text-gray-600 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-heart w-5 text-center text-tierra-medio"></i>
                    <span class="font-semibold">Favoritos</span>
                </button>
                <?php endif; ?>
                
                <?php if (isset($es_productor) && $es_productor && in_array(10, $menu_ids_usuario)): ?>
                <a href="<?= BASE_URL ?>mis_productos" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left text-gray-600 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-box-open w-5 text-center text-tierra-medio"></i>
                    <span class="font-semibold">Mis Productos</span>
                </a>
                <?php endif; ?>

                <?php if (in_array(7, $menu_ids_usuario)): ?>
                <button data-action="show-section" data-section-id="settings" class="menu-item w-full flex items-center space-x-4 px-6 py-3.5 text-left text-gray-600 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-cog w-5 text-center text-tierra-medio"></i>
                    <span class="font-semibold">Configuración</span>
                </button>
                <?php endif; ?>
                
            </nav>

            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                <a href="<?= BASE_URL ?>" class="flex items-center space-x-3 text-gray-500 hover:text-naranja-artesanal transition-colors mb-4 pl-2">
                    <i class="fas fa-arrow-left w-5 text-center"></i>
                    <span class="font-medium text-sm">Volver al Inicio</span>
                </a>
                <button data-action="logout" class="flex items-center space-x-3 text-red-500 hover:text-red-700 transition-colors w-full pl-2">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span class="font-medium text-sm">Cerrar Sesión</span>
                </button>
            </div>
        </aside>
        
        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden relative bg-gray-50">
            <!-- Header for Mobile / Top Bar -->
             <header class="bg-white shadow-sm flex items-center justify-between px-6 py-4 z-10 lg:hidden">
                <div class="flex items-center">
                    <button class="text-gray-500 hover:text-gray-700 mr-4" data-event="click:toggleSidebar">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="text-xl font-bold text-gray-800">Mi Cuenta</h2>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]">
                <div class="min-h-full flex flex-col bg-gradient-to-br from-orange-50/50 via-white/80 to-amber-50/50">
                    <div class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full space-y-8">
                    <?php if (in_array(4, $menu_ids_usuario)): ?>
                <!-- Profile Section -->
                <section id="profile" class="content-section active">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                            <div class="flex items-center justify-between mb-8">
                                <div>
                                    <h2 class="text-2xl font-bold text-tierra-oscuro">Mi Perfil</h2>
                                    <p class="text-sm text-gray-500 mt-1">Gestiona tu información personal</p>
                                </div>
                                <div id="edit-buttons" class="flex items-center gap-3">
                                    <button data-action="toggle-edit" id="btn-editar" class="btn-primary text-white px-5 py-2.5 rounded-lg text-sm hover:shadow-lg transition-all flex items-center">
                                        <i class="fas fa-edit mr-2"></i>Editar
                                    </button>
                                    <div id="save-cancel-buttons" class="hidden flex items-center gap-3">
                                        <button data-action="save-profile" class="bg-verde-artesanal text-white px-5 py-2.5 rounded-lg text-sm hover:shadow-lg transition-all flex items-center">
                                            <i class="fas fa-save mr-2"></i>Guardar
                                        </button>
                                        <button data-action="cancel-edit" class="bg-gray-100 text-gray-600 hover:bg-gray-200 px-5 py-2.5 rounded-lg text-sm transition-all flex items-center">
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
                                <div id="avatar-container" class="avatar-container relative group w-24 h-24 cursor-pointer" data-action="trigger-profile-upload">
                                    <!-- 
                                        Avatar del usuario - Siempre muestra la imagen desde foto_user
                                        - Por defecto: images/profiles/default.webp
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
                                    <form id="profile-upload-form" action="<?= BASE_URL ?>api/upload" method="POST" enctype="multipart/form-data" class="hidden">
                                        <input type="file" id="profile-image-input" name="imagen_perfil" accept="image/*" data-action="profile-upload-change">
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
                            </form>    
                    </div>
                </section>
                <?php endif; ?>

                <?php if (in_array(5, $menu_ids_usuario)): ?>
                <!-- Orders Section -->
                <section id="orders" class="content-section">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-tierra-oscuro">Mis Pedidos</h2>
                                <p class="text-sm text-gray-500 mt-1">Historial y estado de tus compras</p>
                            </div>
                        </div>
                        
                        <!-- Orders List -->
                        <div class="space-y-4">
                            <?php if (empty($pedidos)): ?>
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-shopping-bag text-3xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-800 mb-1">Aún no tienes pedidos</h3>
                                    <p class="text-sm text-gray-500 mb-4">Explora nuestro catálogo y realiza tu primera compra.</p>
                                    <a href="<?= BASE_URL ?>catalogo" class="btn-primary inline-block text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                        Ver catálogo
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($pedidos as $pedido): 
                                    $fecha_pedido = new DateTime($pedido['fec_factura']);
                                    $imagen_pedido = !empty($pedido['primera_imagen']) ? BASE_URL . $pedido['primera_imagen'] : BASE_URL . 'images/default_product.png';
                                    
                                    // Determinar color de estado
                                    $color_estado = 'bg-gray-100 text-gray-700';
                                    if(strtolower($pedido['epayco_estado']) == 'aceptada') {
                                        $color_estado = 'bg-green-100 text-green-700';
                                    } else if(strtolower($pedido['epayco_estado']) == 'pendiente') {
                                        $color_estado = 'bg-blue-100 text-blue-700';
                                    } else if(strtolower($pedido['epayco_estado']) == 'rechazada' || strtolower($pedido['epayco_estado']) == 'fallida') {
                                        $color_estado = 'bg-red-100 text-red-700';
                                    }
                                ?>
                                <!-- Order Item -->
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        <div class="flex items-start space-x-4">
                                            <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0">
                                                <img src="<?= $imagen_pedido ?>" alt="Producto" class="w-full h-full object-cover rounded-lg">
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800">Pedido #<?= htmlspecialchars($pedido['id_factura']) ?></h3>
                                                <p class="text-sm text-gray-500">
                                                    <?= $pedido['total_productos'] ?> producto<?= $pedido['total_productos'] != 1 ? 's' : '' ?> • 
                                                    <?= htmlspecialchars($fecha_pedido->format('d M Y')) ?>
                                                </p>
                                                <div class="flex items-center space-x-2 mt-1">
                                                    <p class="text-sm font-semibold text-tierra-oscuro">$<?= number_format($pedido['val_tot_fact'], 0, ',', '.') ?></p>
                                                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md"><i class="fas fa-credit-card mr-1 text-gray-400"></i> <?= htmlspecialchars($pedido['nom_pago']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col md:items-end space-y-2">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full <?= $color_estado ?>">
                                                <?= ucfirst(htmlspecialchars($pedido['epayco_estado'] ?: 'Recibido')) ?>
                                            </span>
                                            <a href="<?= BASE_URL ?>pedido?id=<?= $pedido['id_factura'] ?>" class="text-sm text-naranja-artesanal hover:underline font-medium">Ver detalles</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <?php if (in_array(6, $menu_ids_usuario)): ?>
                <!-- Favorites Section -->
                <section id="favorites" class="content-section">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-tierra-oscuro">Mis Favoritos</h2>
                                <p class="text-sm text-gray-500 mt-1">Productos que te encantan</p>
                            </div>
                        </div>
                        
                        <!-- Favorites Grid -->
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" id="favoritos-grid">
                            <!-- Los favoritos se renderizan aquí dinámicamente vía JS -->
                        </div>

                        <!-- Estado vacío de favoritos -->
                        <div id="favoritos-vacio" class="hidden text-center py-16">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-regular fa-heart text-3xl text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-medium text-oscuro mb-1">Aún no tienes favoritos</h3>
                            <p class="text-gray-500 mb-4 text-sm">Explora el catálogo y guarda los productos que más te gusten.</p>
                            <a href="<?= BASE_URL ?>catalogo" class="btn-primary text-white px-6 py-2 rounded-lg font-medium inline-block">
                                Ver catálogo
                            </a>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <?php if (in_array(7, $menu_ids_usuario)): ?>
                <!-- Settings Section -->
                <section id="settings" class="content-section">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-tierra-oscuro">Configuración</h2>
                                <p class="text-sm text-gray-500 mt-1">Ajustes de tu cuenta y preferencias</p>
                            </div>
                        </div>
                        
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
                
                </div>
            </div>
            </main>
        </div>
    </div>


    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>
    </body>
</html>
