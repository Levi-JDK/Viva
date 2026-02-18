<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos | VIVA</title>
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/styles/web.css">
</head>
<body class="bg-gray-100 font-sans">
    
    <!-- Reuse Header (Ideally should be a partial, but copying structure for now as per plan) -->
    <!-- Header -->
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- Sidebar Filters -->
            <aside class="w-full lg:w-64 flex-shrink-0 space-y-8">
                <!-- Breadcrumbs/Title -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 capitalize">
                        <?= $search ? htmlspecialchars($search) : 'Todos los productos' ?>
                    </h1>
                    <p class="text-sm text-gray-500 mt-1"><?= count($productos) ?> resultados</p>
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="font-bold text-gray-800 mb-2">Categorías</h3>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="<?= BASE_URL ?>catalogo<?= $search ? '?q='.urlencode($search) : '' ?>" class="<?= !$categoria ? 'font-bold text-blue-600' : 'text-gray-600 hover:text-blue-500' ?>">
                                Todas
                            </a>
                        </li>
                        <?php foreach ($categorias_list as $cat): ?>
                            <li>
                                <a href="<?= BASE_URL ?>catalogo?cat=<?= $cat['id_categoria'] ?><?= $search ? '&q='.urlencode($search) : '' ?>" 
                                   class="<?= $categoria == $cat['id_categoria'] ? 'font-bold text-blue-600' : 'text-gray-600 hover:text-blue-500' ?> flex justify-between">
                                    <span><?= htmlspecialchars($cat['nom_categoria']) ?></span>
                                    <span class="text-gray-400 text-xs">(<?= $cat['total'] ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Price Filter -->
                <div>
                    <h3 class="font-bold text-gray-800 mb-2">Precio</h3>
                    <form action="<?= BASE_URL ?>catalogo" method="GET" class="space-y-2">
                        <?php if ($search): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
                        <?php if ($categoria): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($categoria) ?>"><?php endif; ?>
                        
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" placeholder="Mínimo" value="<?= $min_precio ?>" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500">
                            <span class="text-gray-400">-</span>
                            <input type="number" name="max_price" placeholder="Máximo" value="<?= $max_precio ?>" class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 rounded transition-colors">
                            Aplicar
                        </button>
                    </form>
                </div>

                <!-- Reset Filters -->
                <?php if ($search || $categoria || $min_precio || $max_precio): ?>
                    <a href="<?= BASE_URL ?>catalogo" class="inline-block text-sm text-blue-600 hover:underline">
                        Limpiar filtros
                    </a>
                <?php endif; ?>
            </aside>

            <!-- Product Grid -->
            <div class="flex-1">
                <?php if (empty($productos)): ?>
                    <div class="bg-white rounded-lg p-12 text-center shadow-sm">
                        <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">No encontramos publicaciones</h3>
                        <p class="text-gray-500 text-sm">Revisa que la palabra esté bien escrita o intenta con menos filtros.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php 
                        $show_price = true; // Show price in catalog
                        foreach ($productos as $product): 
                            require ROOT_PATH . 'src/views/partials/card_producto.php';
                        endforeach; 
                        ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
    
    <!-- Footer (Simplified) -->
    <footer class="bg-white border-t border-gray-200 mt-12 py-8">
        <div class="container mx-auto px-4 text-center text-sm text-gray-500">
            &copy; 2025 VIVA - Artesanías Colombianas
        </div>
    </footer>

</body>
</html>
