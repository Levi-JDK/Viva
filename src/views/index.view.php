<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIVA | Artesanías Colombianas - Conecta con nuestras raíces</title>
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/styles/web.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/styles/responsive.css">
</head>
<body class="font-sans bg-white scroll-smooth">
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-tierra-oscuro to-verde-artesanal rounded-lg flex items-center justify-center">
                        <img src="<?= BASE_URL ?>images/Logo.png" alt="">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-tierra-oscuro">VIVA</h1>
                        <p class="text-xs text-tierra-medio">Artesanías Colombianas</p>
                    </div>
                </div>
                <!-- Search Bar - Hidden on mobile, shown on md+ -->
                <form action="<?= BASE_URL ?>catalogo" method="GET" class="hidden md:flex flex-1 max-w-2xl mx-8">
                    <div class="relative w-full">
                        <input 
                            type="text" 
                            name="q"
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
                <nav class="hidden lg:flex items-center space-x-8 pr-10">
                    <a href="#inicio" class="nav-item text-gray-700 hover:text-naranja-artesanal font-medium">Inicio</a>
                    <a href="#categorias" class="nav-item text-gray-700 hover:text-naranja-artesanal font-medium">Categorías</a>
                    <a href="#ofertas" class="nav-item text-gray-700 hover:text-naranja-artesanal font-medium">Ofertas</a>
                </nav>
                <!-- Login Button -->
                <div class="flex items-center space-x-4 pr-10">
                    <div id="user-section">
                        <?php if ($is_logged_in): ?>
                            <!-- User Dropdown -->
                            <div class="relative user-menu">
                                <button id="userMenuBtn" class="flex items-center space-x-2 hover:opacity-80 transition-opacity focus:outline-none">
                                    <div class="w-10 h-10 rounded-full overflow-hidden shadow-md">
                                        <img src="<?= BASE_URL . $foto_usuario ?>?v=<?= time() ?>" 
                                             alt="<?= htmlspecialchars($nombre_usuario) ?>" 
                                             class="w-full h-full object-cover">
                                    </div>
                                    <span class="hidden md:block font-medium text-tierra-oscuro"><?= htmlspecialchars($nombre_usuario) ?></span>
        
                                    <i class="fas fa-chevron-down text-sm text-gray-600"></i>
                                </button>
                                <!-- Dropdown Menu -->
                                <div id="userDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible transform scale-95 transition-all duration-200 z-50">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-tierra-oscuro"><?= htmlspecialchars($nombre_usuario) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($email_usuario) ?></p>
                                    </div>
                                    <ul class="py-2">
                                        <li>
                                            <a href="<?= BASE_URL ?>perfil#profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-beige-suave transition-colors">
                                                <i class="fas fa-user w-5"></i>
                                                <span class="ml-3">Mi Perfil</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= BASE_URL ?>perfil#orders" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-beige-suave transition-colors">
                                                <i class="fas fa-shopping-bag w-5"></i>
                                                <span class="ml-3">Mis Pedidos</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= BASE_URL ?>perfil#favorites" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-beige-suave transition-colors">
                                                <i class="fas fa-heart w-5"></i>
                                                <span class="ml-3">Favoritos</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= BASE_URL ?>perfil#settings" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-beige-suave transition-colors">
                                                <i class="fas fa-cog w-5"></i>
                                                <span class="ml-3">Configuración</span>
                                            </a>
                                        </li>
                                        <li class="border-t border-gray-100 mt-2 pt-2">
                                        <li class="border-t border-gray-100 mt-2 pt-2">
                                            <?php if (!empty($es_productor)): ?>
                                                <a href="<?= BASE_URL ?>mis_productos" class="flex items-center px-4 py-2.5 text-sm text-naranja-artesanal font-semibold hover:bg-orange-50 transition-colors rounded-lg group">
                                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                                                        <i class="fas fa-box text-naranja-artesanal"></i>
                                                    </div>
                                                    <span class="ml-3">Mis productos</span>
                                                    <i class="fas fa-arrow-right ml-auto text-naranja-artesanal opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= BASE_URL ?>vender" class="flex items-center px-4 py-2.5 text-sm text-green-700 font-semibold hover:bg-green-50 transition-colors rounded-lg group">
                                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition-colors">
                                                        <i class="fas fa-store text-green-600"></i>
                                                    </div>
                                                    <span class="ml-3">Vender productos</span>
                                                    <i class="fas fa-arrow-right ml-auto text-green-600 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                </a>
                                            <?php endif; ?>
                                        </li>
                                        </li>
                                        <li class="border-t border-gray-100 mt-2 pt-2">
                                            <a href="<?= BASE_URL ?>logout" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                <i class="fas fa-sign-out-alt w-5"></i>
                                                <span class="ml-3">Cerrar Sesión</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Login Button -->
                            <a href="<?= BASE_URL ?>login" class="btn-primary header-login text-white px-6 py-2 rounded-full font-medium hover:shadow-lg">
                                Iniciar Sesión
                            </a>
                        <?php endif; ?>
                    </div>
                    <button id="cartBtn" class="relative text-gray-700 pl-10">
                         <i class="fas fa-shopping-cart text-xl "></i>
                        <span id="cartCount" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 rounded-full">0</span>
                    </button>
                    <!-- Mobile menu button -->
                    <button class="lg:hidden text-gray-700" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile Search -->
            <div class="md:hidden mt-4">
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="Buscar artesanías..." 
                        class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-full focus:outline-none search-focus"
                    >
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <!-- Mobile Navigation Menu -->
            <div id="mobileMenu" class="lg:hidden hidden mt-4 pb-4 border-t pt-4">
                <nav class="flex flex-col space-y-3">
                    <a href="#inicio" class="text-gray-700 hover:text-naranja-artesanal font-medium">Inicio</a>
                    <a href="#categorias" class="text-gray-700 hover:text-naranja-artesanal font-medium">Categorías</a>
                    <a href="#ofertas" class="text-gray-700 hover:text-naranja-artesanal font-medium">Ofertas</a>
                </nav>
            </div>
        </div>
    </header>
    <!-- Hero Section -->
    <section id="inicio" class="relative min-h-screen flex items-center overflow-hidden">
        <!-- Hero Background -->
        <div class="absolute inset-0 z-0">
            <picture>
                <source media="(max-width: 640px)" srcset="https://artesaniasdecolombia.com.co/Documentos/Contenido/25859_risaralda-artesanias-colombia-2017-g.jpg">
                <img src="https://artesaniasdecolombia.com.co/Documentos/Contenido/25859_risaralda-artesanias-colombia-2017-g.jpg" 
                     alt="Artesanías Colombianas" 
                     class="w-full h-full object-cover">
            </picture>
            <div class="absolute inset-0 bg-black/50"></div>
        </div>

        <div class="container mx-auto px-4 text-center text-white relative z-10">
            <div class="max-w-4xl mx-auto fade-in">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
                    Conecta con nuestro <span class="text-yellow-300">mercado real</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-2xl mx-auto">
                    Conoce los productos de naturaleza autoctona y artesanal de Colombia.
                </p>
                <button onclick="scrollToSection('categorias')" class="btn-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:shadow-xl inline-flex items-center space-x-3">
                    <span>Explorar ahora</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </section>
    <!-- Trust Section -->
    <section class="bg-beige-suave py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro mb-2">Envíos seguros</h3>
                    <p class="text-gray-600 text-sm">Entrega protegida en todo el mundo</p>
                </div>
                <div class="text-center trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro mb-2">Pago protegido</h3>
                    <p class="text-gray-600 text-sm">Transacciones 100% seguras</p>
                </div>
                <div class="text-center trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro mb-2">Apoyo directo</h3>
                    <p class="text-gray-600 text-sm">Beneficia directamente a las comunidades</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Affiliates Section -->
    <section id="categorias" class="py-16 bg-gradient-to-b from-tierra-claro to-beige-suave/30">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-3xl md:text-4xl font-bold text-[#4F270B] mb-4">
                    Nuestros afiliados
                </h2>
                <p class="text-[#4F270B] text-lg max-w-2xl mx-auto">
                    Conoce a los emprendedores que hacen parte de nuestra comunidad artesanal
                </p>
            </div>

            <?php if (!empty($featured_stands)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <?php foreach ($featured_stands as $stand): ?>
                        <?php 
                        // Enable link to detail page
                        $show_link = true;
                        $stand_url = BASE_URL . 'stand?id=' . $stand['id_productor'];
                        require __DIR__ . '/../views/partials/card_stand.php'; 
                        ?>
                    <?php endforeach; ?>
                </div>

                <!-- View All Link -->
                <div class="text-center mt-10">
                    <a href="<?= BASE_URL ?>test-stands" 
                            class="btn-primary text-white px-8 py-3 rounded-full font-medium text-lg hover:shadow-xl inline-flex items-center transition-all">
                        Ver todos los emprendimientos
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-store text-5xl mb-4 text-gray-300"></i>
                    <p class="text-lg">Próximamente nuevos emprendimientos</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- Categories Section -->
    <section id="categorias" class="py-16 bg-gradient-to-b from-blanco-/30 to-tierra-claro">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-3xl md:text-4xl font-bold text-tierra-oscuro mb-4">
                    Categorías destacadas
                </h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Descubre la rica diversidad de nuestras artesanías tradicionales
                </p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <!-- Category Cards -->
                <div class="category-card card-hover rounded-2xl p-6 text-center cursor-pointer h-full flex flex-col items-center justify-center">
                    <div class="w-24 h-24 bg-white rounded-full mx-auto mb-4 flex items-center justify-center">
                        <img src="<?= BASE_URL ?>images/cejesteria.png"></img>
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro">Cestería</h3>
                    <p class="text-xs text-gray-600 mt-1">148 productos</p>
                </div>

                <div class="category-card card-hover rounded-2xl p-6 text-center cursor-pointer h-full flex flex-col items-center justify-center">
                    <div class="w-24 h-24 bg-white rounded-full mx-auto mb-4 flex items-center justify-center">
                        <img src="<?= BASE_URL ?>images/tejidos.png" alt="">
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro">Tejidos</h3>
                    <p class="text-xs text-gray-600 mt-1">203 productos</p>
                </div>

                <div class="category-card card-hover rounded-2xl p-6 text-center cursor-pointer h-full flex flex-col items-center justify-center">
                    <div class="w-24 h-24 bg-white rounded-full mx-auto mb-4 flex items-center justify-center">
                        <img src="<?= BASE_URL ?>images/ceramica.png" alt="">
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro">Cerámica</h3>
                    <p class="text-xs text-gray-600 mt-1">87 productos</p>
                </div>
                <div class="category-card card-hover rounded-2xl p-6 text-center cursor-pointer h-full flex flex-col items-center justify-center">
                    <div class="w-24 h-24 bg-white rounded-full mx-auto mb-4 flex items-center justify-center">
                        <img src="<?= BASE_URL ?>images/joyeria.png" alt="">
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro">Joyería</h3>
                    <p class="text-xs text-gray-600 mt-1">156 productos</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Featured Products Section -->
    <section id="ofertas" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 fade-in">
                <h2 class="text-3xl md:text-4xl font-bold text-tierra-oscuro mb-4">
                    Productos destacados
                </h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Las mejores creaciones de nuestros artesanos, seleccionadas especialmente para ti
                </p>
            </div>

            <?php if (!empty($featured_products)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php 
                    $show_price = false; // Hide price on landing page
                    foreach ($featured_products as $product): 
                        require __DIR__ . '/partials/card_producto.php';
                    endforeach; 
                    ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-box-open text-5xl mb-4 text-gray-300"></i>
                    <p class="text-lg">Próximamente nuevos productos</p>
                </div>
            <?php endif; ?>

            <div class="text-center mt-8">
                <button class="btn-primary text-white px-8 py-3 rounded-full font-medium hover:shadow-xl">
                    <a href="<?= BASE_URL ?>catalogo">Ver todos los productos</a>
                </button>
            </div>
        </div>
    </section>
    <!-- Our Story Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="fade-in">
                    <h2 class="text-3xl md:text-4xl font-bold text-tierra-oscuro mb-6">
                        Nuestra historia
                    </h2>
                    <p class="text-gray-700 text-lg mb-6 leading-relaxed">
                        VIVA nació del sueño de preservar y compartir la riqueza cultural de las comunidades indígenas colombianas. 
                        Creemos que cada artesanía cuenta una historia milenaria y conecta generaciones.
                    </p>
                    <p class="text-gray-700 text-lg mb-8 leading-relaxed">
                        A través de nuestra plataforma, los artesanos pueden compartir su talento con el mundo, 
                        mientras preservamos tradiciones ancestrales y generamos un impacto económico directo en sus comunidades.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center p-4 bg-beige-suave rounded-lg">
                            <div class="text-2xl font-bold text-tierra-oscuro">500+</div>
                            <div class="text-sm text-gray-600">Artesanos activos</div>
                        </div>
                        <div class="text-center p-4 bg-beige-suave rounded-lg">
                            <div class="text-2xl font-bold text-tierra-oscuro">15</div>
                            <div class="text-sm text-gray-600">Comunidades aliadas</div>
                        </div>
                    </div>
                </div>
                <div class="fade-in">
                    <div class="relative">
                        <div class="w-full h-96 bg-gradient-to-br from-tierra-claro via-beige-suave to-verde-artesanal rounded-2xl overflow-hidden relative">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <img src="<?= BASE_URL ?>images/foot.jpeg" alt="">
                                    <p class="text-tierra-oscuro font-medium">Artesanos trabajando</p>
                                    <p class="text-sm text-gray-600">Preservando tradiciones ancestrales</p>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-gradient-to-br from-naranja-artesanal to-tierra-medio rounded-full flex items-center justify-center">
                            <i class="fas fa-heart text-white text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Newsletter Section -->
    <section class="py-16 bg-gradient-to-r from-tierra-oscuro to-verde-artesanal">
        <div class="container mx-auto px-4 text-center text-white">
            <div class="max-w-2xl mx-auto fade-in">
                <h2 class="text-3xl font-bold mb-4">
                    Mantente conectado con nuestras raíces
                </h2>
                <p class="text-xl mb-8 opacity-90">
                    Recibe noticias sobre nuevos productos, historias de artesanos y ofertas especiales
                </p>
                <div class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                    <input 
                        type="email" 
                        placeholder="Tu correo electrónico" 
                        class="flex-1 px-4 py-3 rounded-full text-gray-900 focus:outline-none focus:ring-2 focus:ring-yellow-300"
                    >
                    <button class="bg-gradient-to-r from-naranja-artesanal to-tierra-medio text-white px-6 py-3 rounded-full font-medium hover:shadow-lg transition-all">
                        Suscribirse
                    </button>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo and Description -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-tierra-oscuro to-verde-artesanal rounded-lg flex items-center justify-center">
                            <img src="<?= BASE_URL ?>images/Logo.png" alt="">
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">VIVA</h3>
                            <p class="text-sm text-gray-400">Artesanías Colombianas</p>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-4 max-w-md">
                        Conectando tradiciones milenarias con el mundo moderno. 
                        Apoyamos a las comunidades indígenas colombianas a través del comercio justo.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-naranja-artesanal transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-naranja-artesanal transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-naranja-artesanal transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                <!-- Quick Links -->
                <div>
                    <h4 class="font-semibold text-lg mb-4">Enlaces rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Políticas de privacidad</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Términos y condiciones</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Contacto</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Preguntas frecuentes</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Blog</a></li>
                    </ul>
                </div>
                <!-- Support -->
                <div>
                    <h4 class="font-semibold text-lg mb-4">Soporte</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Centro de ayuda</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Cómo comprar</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Cómo vender</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-naranja-artesanal transition-colors">Envíos y devoluciones</a></li>
                    </ul>
                </div>
            </div>
            <!-- Bottom Footer -->
            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © 2025 VIVA Artesanías Colombianas. Todos los derechos reservados.
                </p>
                <div class="flex items-center space-x-4 mt-4 md:mt-0">
                    <span class="text-gray-400 text-sm">Métodos de pago:</span>
                    <div class="flex space-x-2">
                        <div class="w-8 h-6 bg-gray-700 rounded flex items-center justify-center ">
                            <i class="fab fa-cc-visa text-xs"></i>
                        </div>
                        <div class="w-8 h-6 bg-gray-700 rounded flex items-center justify-center">
                            <i class="fab fa-cc-mastercard text-xs"></i>
                        </div>
                        <div class="w-8 h-6 bg-gray-700 rounded flex items-center justify-center">
                            <i class="fab fa-cc-paypal text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="fixed bottom-6 right-6 w-12 h-12 bg-gradient-to-r from-naranja-artesanal to-tierra-medio text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 invisible">
        <i class="fas fa-arrow-up"></i>
    </button>
    <!-- Cart Overlay -->
    <div id="cartOverlay" class="fixed inset-0 bg-black opacity-0 invisible transition-opacity duration-300 z-40"></div>
    <!-- Cart Sidebar -->
<aside id="cartSidebar" class="fixed top-0 right-0 w-80 h-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-bold text-tierra-oscuro">Carrito</h2>
        <button onclick="toggleCart()" class="text-gray-500 hover:text-red-500">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto p-4">
        <ul id="cartItems" class="space-y-4 text-sm text-gray-700">
            <li class="text-center text-gray-500 py-8">
                <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
                <p>Tu carrito está vacío</p>
            </li>
        </ul>
    </div>
    <div class="p-4 border-t">
        <p class="font-bold text-tierra-oscuro mb-2">Total: <span id="cartTotal">$0</span></p>
        <button class="btn-primary w-full py-2 text-white rounded-lg">Finalizar compra</button>
    </div>
</aside>
    <script src="<?= BASE_URL ?>src/scripts/script1.js?v=4"></script>
    <script src="<?= BASE_URL ?>src/scripts/script_landing.js?v=4"></script>
</body>
</html>