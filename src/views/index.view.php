<?php 
$page_title = "VIVA | Artesanías Colombianas - Conecta con nuestras raíces";
$body_class = "font-sans bg-white scroll-smooth";
$extra_css = '<link rel="stylesheet" href="' . base_url_path('src/styles/responsive.css') . '">';
require_once __DIR__ . '/partials/base_head.php'; 
?>
    <!-- Prueba de GGA-->
    <!-- Header -->
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>
    <!-- Hero Section -->
    <section id="inicio" class="relative min-h-screen flex items-center overflow-hidden">
        <!-- Hero Background -->
        <div class="absolute inset-0 z-0">
            <picture>
                <source media="(max-width: 640px)" srcset="<?= !empty($pmtros["foto_hero"]) ? base_url_path($pmtros["foto_hero"]) : '' ?>">
                <img src="<?= !empty($pmtros["foto_hero"]) ? base_url_path($pmtros["foto_hero"]) : '' ?>" 
                     alt="Artesanías Colombianas" 
                     class="w-full h-full object-cover">
            </picture>
            <div class="absolute inset-0 bg-black/50"></div>
        </div>

        <div class="container mx-auto px-4 sm:px-8 md:px-16 lg:px-32 xl:px-40 flex items-center justify-start text-center md:text-left text-white relative z-10">
            <div class="w-full max-w-3xl fade-in">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
                    <?php 
                        $rawTitle = $pmtros['landing_hero_titulo'] ?? 'Conecta con nuestro {mercado real}';
                        $safeTitle = htmlspecialchars($rawTitle);
                        $formattedTitle = str_replace(['{', '}'], ['<span class="text-yellow-300">', '</span>'], $safeTitle);
                        echo $formattedTitle;
                    ?>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-2xl">
                    <?= htmlspecialchars($pmtros['landing_hero_subtitulo'] ?? 'Conoce los productos de naturaleza autoctona y artesanal de Colombia.') ?>
                </p>
                <button data-action="scroll-to" data-target="categorias" class="btn-primary text-white px-8 py-4 rounded-full text-lg font-semibold hover:shadow-xl inline-flex items-center space-x-3">
                    <span>Explorar ahora</span>
                    <i class="fas fa-arrow-right"></i>
                </button>

                <!-- Panel de Métricas (Glassmorphism) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 shadow-2xl transform transition-all hover:scale-[1.02] duration-300">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-users text-3xl text-yellow-300 mb-3"></i>
                        <span class="text-3xl font-bold">+500</span>
                        <span class="text-xs font-medium opacity-90 tracking-wider uppercase mt-1">Productores</span>
                    </div>
                    <div class="flex flex-col items-center relative">
                        <div class="hidden md:block w-px h-16 bg-white/20 absolute -left-3 top-1/2 -translate-y-1/2"></div>
                        <i class="fas fa-hand-holding-heart text-3xl text-yellow-300 mb-3"></i>
                        <span class="text-3xl font-bold">15</span>
                        <span class="text-xs font-medium opacity-90 tracking-wider uppercase mt-1">Comunidades</span>
                        <div class="hidden md:block w-px h-16 bg-white/20 absolute -right-3 top-1/2 -translate-y-1/2"></div>
                    </div>
                    <div class="flex flex-col items-center">
                        <i class="fas fa-box-open text-3xl text-yellow-300 mb-3"></i>
                        <span class="text-3xl font-bold">+10k</span>
                        <span class="text-xs font-medium opacity-90 tracking-wider uppercase mt-1">Vendidos</span>
                    </div>
                </div>
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
                    <h3 class="font-semibold text-tierra-oscuro mb-2"><?= htmlspecialchars($pmtros['landing_conf_1_tit'] ?? 'Envíos seguros') ?></h3>
                    <p class="text-gray-600 text-sm"><?= htmlspecialchars($pmtros['landing_conf_1_sub'] ?? 'Entrega protegida en todo el mundo') ?></p>
                </div>
                <div class="text-center trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro mb-2"><?= htmlspecialchars($pmtros['landing_conf_2_tit'] ?? 'Pago protegido') ?></h3>
                    <p class="text-gray-600 text-sm"><?= htmlspecialchars($pmtros['landing_conf_2_sub'] ?? 'Transacciones 100% seguras') ?></p>
                </div>
                <div class="text-center trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3 class="font-semibold text-tierra-oscuro mb-2"><?= htmlspecialchars($pmtros['landing_conf_3_tit'] ?? 'Apoyo directo') ?></h3>
                    <p class="text-gray-600 text-sm"><?= htmlspecialchars($pmtros['landing_conf_3_sub'] ?? 'Beneficia directamente a las comunidades') ?></p>
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
                        // Habilitar enlace a la página de detalle
                        $show_link = true;
                        $stand_url = base_url_path('stand?id=' . $stand['id_stand']);
                        require __DIR__ . '/../views/partials/card_stand.php'; 
                        ?>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-10">
                    <a href="<?= base_url_path('stands') ?>" 
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
                <!-- Category Cards (Dynamic) -->
                <?php 
                if (!empty($categorias_destacadas)) {
                    // Tomamos un máximo de 8 para no romper el grid ideal
                    $categorias_mostrar = array_slice($categorias_destacadas, 0, 8);
                    foreach ($categorias_mostrar as $cat): 
                        // Usar la imagen de la BD, o la default si está vacía
                        $img_src = !empty($cat['img_cat']) 
                            ? base_url_path($cat['img_cat']) 
                            : base_url_path('images/default_category.webp'); 
                ?>
                    <a href="<?= base_url_path('catalogo?cat=' . $cat['id_categoria']) ?>" class="category-card card-hover rounded-2xl p-6 text-center cursor-pointer h-full flex flex-col items-center justify-center transition-all">
                        <div class="w-24 h-24 bg-white shadow-sm rounded-full mx-auto mb-4 flex items-center justify-center p-2">
                            <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($cat['nom_categoria']) ?>" class="max-w-full max-h-full object-contain">
                        </div>
                        <h3 class="font-semibold text-tierra-oscuro"><?= htmlspecialchars($cat['nom_categoria']) ?></h3>
                        <p class="text-xs text-gray-600 mt-1"><?= $cat['total'] ?> productos</p>
                    </a>
                <?php 
                    endforeach; 
                } else {
                    echo '<p class="col-span-full text-center text-gray-500 py-8">No se encontraron categorías activas.</p>';
                }
                ?>
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
                    $show_price = false; // Ocultar precio en el landing
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
                    <a href="<?= base_url_path('catalogo') ?>">Ver todos los productos</a>
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
                        <?= htmlspecialchars($pmtros['landing_filosofia_tit'] ?? 'Nuestra historia') ?>
                    </h2>
                    <p class="text-gray-700 text-lg mb-6 leading-relaxed">
                        <?= htmlspecialchars($pmtros['landing_filosofia_p1'] ?? 'VIVA nació del sueño de preservar y compartir la riqueza cultural de las comunidades indígenas colombianas. Creemos que cada artesanía cuenta una historia milenaria y conecta generaciones.') ?>
                    </p>
                    <p class="text-gray-700 text-lg mb-8 leading-relaxed">
                        <?= htmlspecialchars($pmtros['landing_filosofia_p2'] ?? 'A través de nuestra plataforma, los artesanos pueden compartir su talento con el mundo, mientras preservamos tradiciones ancestrales y generamos un impacto económico directo en sus comunidades.') ?>
                    </p>

                </div>
                <div class="fade-in">
                    <div class="relative">
                        <div class="w-full h-96 bg-gradient-to-br from-tierra-claro via-beige-suave to-verde-artesanal rounded-2xl overflow-hidden relative">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="text-center">
                                    <img src="<?= base_url_path('images/foot.jpeg') ?>" alt="">
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
            </div>
        </div>
    </section>
    <!-- Footer -->
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="fixed bottom-6 right-6 w-12 h-12 bg-gradient-to-r from-naranja-artesanal to-tierra-medio text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 invisible">
        <i class="fas fa-arrow-up"></i>
    </button>
    <!-- Drawer del Carrito -->
    <?php require_once __DIR__ . '/partials/carrito.php'; ?>
    <!-- Test GGA -->
</body>
</html>
