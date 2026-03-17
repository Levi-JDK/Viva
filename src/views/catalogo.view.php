<?php 
$page_title = "Catálogo de Productos | VIVA";
$body_class = "bg-gray-100 font-sans min-h-screen flex flex-col";
require_once __DIR__ . '/partials/base_head.php'; 
?>
    
    <!-- Reuse Header -->
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- Estado principal para almacenar filtros actuales (se usará en JS) -->
    <script>
        window.VIVACatalogo = {
            baseUrl: "<?= BASE_URL ?>",
            filtros: {
                search: "<?= !empty($filtros['search']) ? htmlspecialchars($filtros['search']) : '' ?>",
                categoria: "<?= !empty($filtros['categoria']) ? htmlspecialchars($filtros['categoria']) : '' ?>",
                oficio: "<?= !empty($filtros['oficio']) ? htmlspecialchars($filtros['oficio']) : '' ?>",
                materia: "<?= !empty($filtros['materia']) ? htmlspecialchars($filtros['materia']) : '' ?>",
                min_price: "<?= !empty($filtros['min_price']) ? htmlspecialchars($filtros['min_price']) : '' ?>",
                max_price: "<?= !empty($filtros['max_price']) ? htmlspecialchars($filtros['max_price']) : '' ?>"
            }
        };
    </script>

    <main class="container mx-auto px-6 py-10 flex-1">

        <!-- Encabezado del catálogo -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800 capitalize" id="texto-busqueda">
                    <?= !empty($filtros['search']) ? htmlspecialchars($filtros['search']) : 'Todos los productos' ?>
                </h1>
            </div>
            <button id="btn-limpiar-filtros" class="hidden inline-flex items-center gap-2 text-sm text-naranja-artesanal border border-naranja-artesanal px-4 py-2 rounded-full hover:bg-orange-50 transition-colors self-start sm:self-auto">
                <i class="fas fa-times-circle"></i> Limpiar filtros
            </button>
        </div>

        <!-- Layout Grid: Sidebar + Productos -->
        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8 items-start">

            <!-- ── SIDEBAR DE FILTROS ── -->
            <aside class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8 lg:sticky lg:top-24 self-start">
                
                <!-- Buscador de texto (opcional en sidebar también) -->
                <div>
                     <div class="relative">
                        <input type="text" id="filtro-buscar" placeholder="Buscar producto..." value="<?= !empty($filtros['search']) ? htmlspecialchars($filtros['search']) : '' ?>" class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-naranja-artesanal/30 transition-colors">
                        <i class="fas fa-search absolute left-3.5 top-3 text-gray-400 text-sm"></i>
                    </div>
                </div>

                <!-- Categorías -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Categorías</h3>
                    <div class="space-y-2">
                        <?php foreach ($categorias_list as $cat): ?>
                            <label class="flex items-center justify-between cursor-pointer group">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="filtro_categoria" value="<?= $cat['id_categoria'] ?>" <?= (!empty($filtros['categoria']) && $filtros['categoria'] == $cat['id_categoria']) ? 'checked' : '' ?> class="w-4 h-4 text-naranja-artesanal border-gray-300 focus:ring-naranja-artesanal">
                                    <span class="text-sm text-gray-600 group-hover:text-naranja-artesanal transition-colors"><?= htmlspecialchars($cat['nom_categoria']) ?></span>
                                </div>
                                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full"><?= $cat['total'] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Oficios -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Oficio Artesanal</h3>
                    <div class="space-y-2">
                        <?php foreach ($oficios_list as $oficio): ?>
                            <label class="flex items-center justify-between cursor-pointer group">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="filtro_oficio" value="<?= $oficio['id_oficio'] ?>" <?= (isset($_GET['oficio']) && $_GET['oficio'] == $oficio['id_oficio']) ? 'checked' : '' ?> class="w-4 h-4 text-naranja-artesanal border-gray-300 focus:ring-naranja-artesanal">
                                    <span class="text-sm text-gray-600 group-hover:text-naranja-artesanal transition-colors"><?= htmlspecialchars($oficio['nom_oficio']) ?></span>
                                </div>
                                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full"><?= $oficio['total'] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Materias Primas -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Materia Prima</h3>
                    <div class="space-y-2">
                        <?php foreach ($materias_list as $mat): ?>
                            <label class="flex items-center justify-between cursor-pointer group">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="filtro_materia" value="<?= $mat['id_materia'] ?>" <?= (isset($_GET['materia']) && $_GET['materia'] == $mat['id_materia']) ? 'checked' : '' ?> class="w-4 h-4 text-naranja-artesanal border-gray-300 focus:ring-naranja-artesanal">
                                    <span class="text-sm text-gray-600 group-hover:text-naranja-artesanal transition-colors"><?= htmlspecialchars($mat['nom_materia']) ?></span>
                                </div>
                                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full"><?= $mat['total'] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr class="border-gray-100">

                <!-- Filtro de Precio -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Rango de Precio</h3>
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <label class="text-xs text-gray-400 mb-1 block">Mínimo</label>
                            <input type="number" id="filtro-min-price" placeholder="$ 0" value="<?= !empty($filtros['min_price']) ? htmlspecialchars($filtros['min_price']) : '' ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-naranja-artesanal/30 focus:border-naranja-artesanal">
                        </div>
                        <div class="flex-1">
                            <label class="text-xs text-gray-400 mb-1 block">Máximo</label>
                            <input type="number" id="filtro-max-price" placeholder="$ ∞" value="<?= !empty($filtros['max_price']) ? htmlspecialchars($filtros['max_price']) : '' ?>" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-naranja-artesanal/30 focus:border-naranja-artesanal">
                        </div>
                    </div>
                </div>

            </aside>

            <!-- ── ÁREA DE PRODUCTOS ── -->
            <div class="flex flex-col gap-6 relative min-h-[400px]">
                
                <!-- Overlay de carga (oculto por defecto) -->
                <div id="loader-productos" class="absolute inset-0 bg-white/70 backdrop-blur-sm z-10 hidden flex-col items-center justify-center rounded-2xl">
                    <div class="w-12 h-12 border-4 border-orange-100 border-t-naranja-artesanal rounded-full animate-spin"></div>
                    <p class="mt-4 text-sm font-semibold text-gray-500">Buscando artesanias...</p>
                </div>

                <div id="contenedor-productos" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php if (empty($productos)): ?>
                        <!-- Estado vacío inicial -->
                        <div class="col-span-1 sm:col-span-2 xl:col-span-3 flex flex-col items-center justify-center bg-white rounded-2xl p-16 shadow-sm border border-gray-100 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-5">
                                <i class="fas fa-search text-gray-300 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-700 mb-2">Sin resultados</h3>
                            <p class="text-gray-400 text-sm max-w-xs">Intenta eliminar algunos filtros o usar otros términos de búsqueda.</p>
                        </div>
                    <?php else: ?>
                        <?php
                        $show_price = true;
                        foreach ($productos as $product):
                            require ROOT_PATH . 'src/views/partials/card_producto.php';
                        endforeach;
                        ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>
    
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <!-- Lógica de Filtrado Asíncrono (Fetch API) -->
    <script src="<?= BASE_URL ?>src/scripts/catalogo.js"></script>
    <!-- Drawer del Carrito -->
    <?php require_once __DIR__ . '/partials/carrito.php'; ?>
    <script src="<?= BASE_URL ?>src/scripts/carrito.js"></script>
</body>
</html>
