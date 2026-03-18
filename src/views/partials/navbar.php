<?php
require_once __DIR__ . '/../../functions/navbar_usuario.php';
cargar_datos_navbar();

$is_logged_in   = $GLOBALS['is_logged_in'] ?? false;
$nombre_usuario = $GLOBALS['nombre_usuario'] ?? '';
$email_usuario  = $GLOBALS['email_usuario'] ?? '';
$foto_usuario   = $GLOBALS['foto_usuario'] ?? 'images/default.jpg';
$navbar_menus   = $GLOBALS['navbar_menus'] ?? [];
$dropdown_menus = $GLOBALS['dropdown_menus'] ?? [];

// Mostrar errores globales de redirección
$error_msg = '';
if (isset($_GET['error']) && $_GET['error'] === 'access_denied') {
    $error_msg = "No tienes permisos de Administrador para acceder a ese módulo.";
}
?>
    <?php if ($error_msg): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: '<?= addslashes($error_msg) ?>',
                confirmButtonColor: '#f26e22'
            });
        }
    });
    </script>
    <?php endif; ?>
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50 font-sans">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between gap-4 lg:gap-8">
                <!-- Logo -->
                <a href="<?= BASE_URL ?>" class="flex items-center space-x-3 group min-w-max">
                    <div class="aspect-square w-8 sm:w-9 md:w-10 lg:w-11 bg-gradient-to-br from-tierra-oscuro to-verde-artesanal rounded-lg flex items-center justify-center group-hover:shadow-md transition-all">
                        <img src="<?= BASE_URL ?>images/Logo.png" alt="VIVA" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-tierra-oscuro group-hover:text-tierra-medio transition-colors">VIVA</h1>
                        <p class="text-xs text-tierra-medio hidden sm:block">Artesanías Colombianas</p>
                    </div>
                </a>
                
                <!-- Search Bar - Hidden on mobile, shown on md+ -->
                <form action="<?= BASE_URL ?>catalogo" method="GET" class="hidden md:flex flex-1 max-w-2xl">
                    <div class="relative w-full">
                        <input 
                            type="text" 
                            name="q"
                            value="<?= isset($search) ? htmlspecialchars($search) : '' ?>"
                            placeholder="Buscar artesanías, comunidades, materiales..." 
                            class="w-full px-4 py-3 pl-12 pr-16 border border-gray-300 rounded-full focus:outline-none search-focus"
                        >
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-naranja-artesanal to-tierra-medio text-white px-4 py-2 rounded-full hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- Navigation - Hidden on mobile -->
                <nav class="hidden lg:flex items-center space-x-8">
                    <?php if (!empty($navbar_menus)): ?>
                        <?php foreach ($navbar_menus as $nmenu): ?>
                            <a href="<?= BASE_URL . htmlspecialchars($nmenu['url_menu']) ?>" class="nav-item text-gray-700 hover:text-naranja-artesanal font-medium">
                                <?= htmlspecialchars($nmenu['nom_menu']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </nav>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <div id="user-section">
                        <?php if (isset($is_logged_in) && $is_logged_in): ?>
                            <!-- User Dropdown -->
                            <div class="relative user-menu group">
                                <button id="userMenuBtn" class="flex items-center space-x-2 hover:opacity-80 transition-opacity focus:outline-none">
                                    <div class="w-10 h-10 rounded-full overflow-hidden shadow-md border-2 border-transparent group-hover:border-naranja-artesanal transition-all">
                                        <img src="<?= BASE_URL . ($foto_usuario ?? 'images/default.jpg') ?>?v=<?= time() ?>" 
                                             alt="<?= htmlspecialchars($nombre_usuario ?? 'Usuario') ?>" 
                                             class="w-full h-full object-cover">
                                    </div>
                                    <span class="hidden md:block font-medium text-tierra-oscuro"><?= htmlspecialchars($nombre_usuario ?? 'Usuario') ?></span>
                                    <i class="fas fa-chevron-down text-sm text-gray-600 transition-transform group-hover:rotate-180"></i>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div id="userDropdown" class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transform origin-top-right transition-all duration-200 z-50">
                                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                                        <p class="text-sm font-bold text-tierra-oscuro truncate"><?= htmlspecialchars($nombre_usuario ?? '') ?></p>
                                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($email_usuario ?? '') ?></p>
                                    </div>
                                    <ul class="py-2">
                                        <?php if (!empty($dropdown_menus)): ?>
                                            <?php foreach ($dropdown_menus as $menu): ?>
                                                <?php if (in_array($menu['id_menu'], [4, 5, 6, 7])): ?>
                                                    <li>
                                                        <a href="<?= BASE_URL . htmlspecialchars($menu['url_menu']) ?>" class="flex items-center px-5 py-3 text-sm text-gray-700 hover:bg-orange-50 hover:text-naranja-artesanal transition-colors">
                                                            <i class="<?= htmlspecialchars($menu['icono_menu']) ?> w-6 text-center"></i>
                                                            <span class="ml-2"><?= htmlspecialchars($menu['nom_menu']) ?></span>
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="px-2 space-y-1 mt-1 mb-1">
                                                        <?php $menu_color = ($menu['id_menu'] == 9) ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-orange-50 text-naranja-artesanal hover:bg-orange-100'; ?>
                                                        <a href="<?= BASE_URL . htmlspecialchars($menu['url_menu']) ?>" class="flex items-center justify-between px-4 py-2 text-sm <?= $menu_color ?> font-semibold transition-colors rounded-lg group-menu-item">
                                                            <div class="flex items-center">
                                                                <i class="<?= htmlspecialchars($menu['icono_menu']) ?> w-6 text-center"></i>
                                                                <span class="ml-2"><?= htmlspecialchars($menu['nom_menu']) ?></span>
                                                            </div>
                                                            <i class="fas fa-arrow-right text-xs opacity-0 group-menu-item-hover:opacity-100"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        
                                        <li class="border-t border-gray-100 my-1"></li>

                                        <li>
                                            <a href="<?= BASE_URL ?>logout" class="flex items-center px-5 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors rounded-b-xl">
                                                <i class="fas fa-sign-out-alt w-6 text-center"></i>
                                                <span class="ml-2">Cerrar Sesión</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php
                                $login_url = BASE_URL . 'login';
                                $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                                    . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                                // Solo añadir redirect si no estamos ya en la página de login
                                if (strpos($_SERVER['REQUEST_URI'], '/login') === false) {
                                    $login_url .= '?redirect=' . urlencode($current_url);
                                }
                            ?>
                            <!-- Login Button -->
                            <a href="<?= $login_url ?>" class="btn-primary header-login text-white px-6 py-2 rounded-full font-medium hover:shadow-lg transform hover:-translate-y-0.5 transition-all">
                                Iniciar Sesión
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Botón Carrito -->
                    <button onclick="toggleCarrito()"
                            class="relative text-gray-700 hover:text-naranja-artesanal transition-colors p-2"
                            aria-label="Abrir carrito">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span class="navbar-carrito-badge hidden absolute top-0 right-0 bg-principal text-white
                                     text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full
                                     border-2 border-white">0</span>
                    </button>

                    <!-- Mobile menu button -->
                    <button class="lg:hidden text-gray-700 hover:text-naranja-artesanal outline-none" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Search -->
            <form action="<?= BASE_URL ?>catalogo" method="GET" class="md:hidden mt-4">
                <div class="relative">
                    <input 
                        type="text" 
                        name="q"
                        value="<?= isset($search) ? htmlspecialchars($search) : '' ?>"
                        placeholder="Buscar artesanías..." 
                        class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-full focus:outline-none search-focus"
                    >
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </form>

            <!-- Mobile Navigation Menu -->
            <div id="mobileMenu" class="lg:hidden hidden mt-4 pb-4 border-t border-gray-100 pt-4 animate-fade-in-down">
                <nav class="flex flex-col space-y-3">
                    <?php if (!empty($navbar_menus)): ?>
                        <?php foreach ($navbar_menus as $nmenu): ?>
                            <a href="<?= BASE_URL . htmlspecialchars($nmenu['url_menu']) ?>" class="block px-4 py-2 text-gray-700 hover:bg-orange-50 hover:text-naranja-artesanal rounded-lg font-medium transition-colors">
                                <?= htmlspecialchars($nmenu['nom_menu']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>
