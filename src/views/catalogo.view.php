<?php 
$page_title = "Catálogo de Productos | VIVA";
$body_class = "bg-gray-100 font-sans min-h-screen flex flex-col";
require_once __DIR__ . '/partials/base_head.php';

// Variables de filtro para la vista
$search = $filtros['search'] ?? null;
$categoria = $filtros['categoria'] ?? null;
$oficio = $filtros['oficio'] ?? null;
$materia = $filtros['materia'] ?? null;
$min_precio = $filtros['min_price'] ?? null;
$max_precio = $filtros['max_price'] ?? null;
?>
    <style>
        @media(min-width:768px){#catalog-sidebar{width:280px;flex-shrink:0;max-width:280px!important;}}
    </style>
    
    <!-- Reuse Header -->
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="container mx-auto px-6 py-10 flex-1">

        <!-- Encabezado del catálogo -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800 capitalize">
                    <?= $search ? htmlspecialchars($search) : 'Todos los productos' ?>
                </h1>
                <p class="text-sm text-gray-400 mt-1"><?= count($productos) ?> resultados encontrados</p>
            </div>
            <?php if ($search || $categoria || $oficio || $materia || $min_precio || $max_precio): ?>
                <a href="<?= BASE_URL ?>catalogo" class="inline-flex items-center gap-2 text-sm text-naranja-artesanal border border-naranja-artesanal px-4 py-2 rounded-full hover:bg-orange-50 transition-colors self-start sm:self-auto">
                    <i class="fas fa-times-circle"></i> Limpiar filtros
                </a>
            <?php endif; ?>
        </div>

        <!-- Layout: Sidebar + Productos -->
        <div class="flex flex-col md:flex-row gap-8">

            <!-- ── SIDEBAR DE FILTROS ── -->
            <aside class="w-full md:sticky md:top-24 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8" style="flex:0 0 auto;max-width:100%;" id="catalog-sidebar">

                <!-- Categorías -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Categorías</h3>
                    <ul class="space-y-1 text-sm">
                        <li>
                            <a href="<?= BASE_URL ?>catalogo<?= $search ? '?q='.urlencode($search) : '' ?>"
                               class="flex items-center justify-between px-3 py-2 rounded-lg transition-colors <?= !$categoria ? 'bg-orange-50 text-naranja-artesanal font-semibold' : 'text-gray-600 hover:bg-gray-50' ?>">
                                <span>Todas</span>
                            </a>
                        </li>
                        <?php foreach ($categorias_list as $cat): ?>
                            <li>
                                <a href="<?= BASE_URL ?>catalogo?cat=<?= $cat['id_categoria'] ?><?= $search ? '&q='.urlencode($search) : '' ?>"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg transition-colors <?= $categoria == $cat['id_categoria'] ? 'bg-orange-50 text-naranja-artesanal font-semibold' : 'text-gray-600 hover:bg-gray-50' ?>">
                                    <span><?= htmlspecialchars($cat['nom_categoria']) ?></span>
                                    <span class="text-xs text-gray-300 bg-gray-100 rounded-full px-2 py-0.5"><?= $cat['total'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <hr class="border-gray-100">

                <!-- Oficios -->
                <?php if (!empty($oficios_list)): ?>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Oficios</h3>
                    <ul class="space-y-1 text-sm">
                        <?php foreach ($oficios_list as $ofi): ?>
                            <li>
                                <a href="<?= BASE_URL ?>catalogo?oficio=<?= $ofi['id_oficio'] ?><?= $search ? '&q='.urlencode($search) : '' ?><?= $categoria ? '&cat='.$categoria : '' ?>"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg transition-colors <?= $oficio == $ofi['id_oficio'] ? 'bg-orange-50 text-naranja-artesanal font-semibold' : 'text-gray-600 hover:bg-gray-50' ?>">
                                    <span><?= htmlspecialchars($ofi['nom_oficio']) ?></span>
                                    <span class="text-xs text-gray-300 bg-gray-100 rounded-full px-2 py-0.5"><?= $ofi['total'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <hr class="border-gray-100">
                <?php endif; ?>

                <!-- Materias Primas -->
                <?php if (!empty($materias_list)): ?>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Materia Prima</h3>
                    <ul class="space-y-1 text-sm">
                        <?php foreach ($materias_list as $mat): ?>
                            <li>
                                <a href="<?= BASE_URL ?>catalogo?materia=<?= $mat['id_materia'] ?><?= $search ? '&q='.urlencode($search) : '' ?><?= $categoria ? '&cat='.$categoria : '' ?>"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg transition-colors <?= $materia == $mat['id_materia'] ? 'bg-orange-50 text-naranja-artesanal font-semibold' : 'text-gray-600 hover:bg-gray-50' ?>">
                                    <span><?= htmlspecialchars($mat['nom_materia']) ?></span>
                                    <span class="text-xs text-gray-300 bg-gray-100 rounded-full px-2 py-0.5"><?= $mat['total'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <hr class="border-gray-100">
                <?php endif; ?>

                <!-- Filtro de Precio -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Precio</h3>
                    <form action="<?= BASE_URL ?>catalogo" method="GET" class="space-y-3">
                        <?php if ($search): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
                        <?php if ($categoria): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($categoria) ?>"><?php endif; ?>
                        <?php if ($oficio): ?><input type="hidden" name="oficio" value="<?= htmlspecialchars($oficio) ?>"><?php endif; ?>
                        <?php if ($materia): ?><input type="hidden" name="materia" value="<?= htmlspecialchars($materia) ?>"><?php endif; ?>

                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label class="text-xs text-gray-400 mb-1 block">Mínimo</label>
                                <input type="number" name="min_price" placeholder="$ 0" value="<?= $min_precio ?>"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-naranja-artesanal/30 focus:border-naranja-artesanal">
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-gray-400 mb-1 block">Máximo</label>
                                <input type="number" name="max_price" placeholder="$ ∞" value="<?= $max_precio ?>"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-naranja-artesanal/30 focus:border-naranja-artesanal">
                            </div>
                        </div>
                        <button type="submit"
                                class="w-full bg-naranja-artesanal hover:bg-tierra-oscuro text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                            Aplicar filtro
                        </button>
                    </form>
                </div>

            </aside>

            <!-- ── ÁREA DE PRODUCTOS ── -->
            <div class="flex-1 min-w-0 flex flex-col gap-6">

                <?php if (empty($productos)): ?>
                    <!-- Estado vacío -->
                    <div class="flex flex-col items-center justify-center bg-white rounded-2xl p-16 shadow-sm border border-gray-100 text-center">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-5">
                            <i class="fas fa-search text-gray-300 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">Sin resultados</h3>
                        <p class="text-gray-400 text-sm max-w-xs">Revisa que la palabra esté bien escrita o intenta con menos filtros.</p>
                        <a href="<?= BASE_URL ?>catalogo" class="mt-6 px-6 py-2 bg-naranja-artesanal text-white rounded-full text-sm font-semibold hover:bg-tierra-oscuro transition-colors">
                            Ver todos los productos
                        </a>
                    </div>

                <?php else: ?>
                    <!-- Grilla de productos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php
                        $show_price = true;
                        foreach ($productos as $product):
                            require ROOT_PATH . 'src/views/partials/card_producto.php';
                        endforeach;
                        ?>
                    </div>
                <?php endif; ?>

            </div>
            <!-- ── FIN ÁREA DE PRODUCTOS ── -->

        </div>
    </main>
    
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <?php require_once __DIR__ . '/partials/carrito.php'; ?>
</body>
</html>
