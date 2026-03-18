<?php 
$page_title = "Comunidad de Artesanos | VIVA";
$body_class = "bg-gray-50 font-sans antialiased min-h-screen flex flex-col";
require_once __DIR__ . '/partials/base_head.php'; 
?>
    
    <!-- Navbar -->
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow flex flex-col">
        
        <!-- Hero Section -->
        <section class="relative bg-black text-white py-20 px-4">
            <!-- Background Image overlay -->
            <div class="absolute inset-0 opacity-20 bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>images/default_stands.jpg');"></div>
            
            <div class="relative container mx-auto text-center z-10 max-w-3xl">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight">
                    Directorio VIVA
                </h1>
                <p class="text-lg md:text-xl opacity-90 mb-8 max-w-2xl mx-auto line-clamp-3">
                    Conoce a los creadores detrás de la magia. Cada stand es una ventana a la tradición, pasión y dedicación de nuestras comunidades ancestrales.
                </p>
                
                <!-- Search Bar -->
                <form action="<?= BASE_URL ?>stands" method="GET" class="relative max-w-xl mx-auto flex items-center">
                    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Buscar por nombre o descripción..." class="block w-full pl-8 pr-32 py-4 border-none rounded-full text-gray-900 focus:ring-4 focus:ring-naranja-artesanal/50 shadow-lg transition-shadow text-base">
                    <button type="submit" class="absolute right-2 top-2 bottom-2 bg-naranja-artesanal hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-full transition-colors flex items-center gap-2">
                        Buscar
                    </button>
                </form>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="container mx-auto px-4 py-16 flex-grow">
            
            <?php if (!empty($_GET['q'])): ?>
            <div class="mb-8 flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <p class="text-gray-600 font-medium">Resultados para: <span class="text-tierra-oscuro font-bold">"<?= htmlspecialchars($_GET['q']) ?>"</span></p>
                <a href="<?= BASE_URL ?>stands" class="text-naranja-artesanal hover:underline text-sm font-semibold flex items-center gap-1">
                    <i class="fas fa-times-circle"></i> Limpiar búsqueda
                </a>
            </div>
            <?php endif; ?>

            <?php if (empty($stands)): ?>
                <!-- Empty State -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-16 text-center max-w-2xl mx-auto mt-8">
                    <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-store-slash text-naranja-artesanal text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">No encontramos emprendimientos</h2>
                    <p class="text-gray-500 mb-8">
                        <?php if(!empty($_GET['q'])): ?>
                            No hay resultados que coincidan con tu búsqueda. Intenta con otros términos.
                        <?php else: ?>
                            Actualmente no hay stands registrados en la plataforma. ¡Pronto tendremos nuevos artesanos!
                        <?php endif; ?>
                    </p>
                    <a href="<?= BASE_URL ?>" class="bg-tierra-oscuro text-white px-8 py-3 rounded-full hover:bg-tierra-medio transition-all shadow-md hover:shadow-lg inline-flex items-center gap-2 font-medium">
                        <i class="fas fa-home"></i> Volver al inicio
                    </a>
                </div>
            <?php else: ?>
                <!-- Grid de Stands -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <?php foreach ($stands as $stand): ?>
                        <?php 
                        // Habilitar enlace a la página de detalle
                        $show_link = true;
                        $stand_url = BASE_URL . 'stand?id=' . $stand['id_stand'];
                        require __DIR__ . '/partials/card_stand.php'; 
                        ?>
                    <?php endforeach; ?>
                </div>

                <!-- Footer Summary / Pagination placeholder -->
                <div class="mt-16 text-center border-t border-gray-200 pt-8">
                    <p class="text-gray-500 bg-white inline-block px-6 py-2 rounded-full shadow-sm border border-gray-100">
                        Mostrando <span class="font-bold text-naranja-artesanal"><?= count($stands) ?></span> emprendimiento<?= count($stands) !== 1 ? 's' : '' ?> activo<?= count($stands) !== 1 ? 's' : '' ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>
        
    </main>

    <!-- Footer -->
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <!-- Drawer del Carrito -->
    <?php require_once __DIR__ . '/partials/carrito.php'; ?>
    <script src="<?= BASE_URL ?>src/scripts/carrito.js"></script>
</body>
</html>
