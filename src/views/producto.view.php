<?php // if (isset($_GET['debug'])) echo "View Loaded...<br>"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($producto && isset($producto['nom_producto'])) ? htmlspecialchars($producto['nom_producto']) : 'Producto no encontrado' ?> | VIVA</title>
    <?php require_once __DIR__ . '/partials/tailwind_head.php'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>src/styles/web.css">
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">

    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="container mx-auto px-4 py-8 mt-20">
        <?php if ($error_message): ?>
            <div class="max-w-2xl mx-auto text-center py-20">
                <i class="fas fa-exclamation-circle text-6xl text-gray-300 mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-700 mb-2">Lo sentimos</h1>
                <p class="text-gray-500 mb-6"><?= htmlspecialchars($error_message) ?></p>
                <a href="<?= BASE_URL ?>catalogo" class="px-6 py-2 bg-naranja-artesanal text-white rounded-full hover:bg-orange-600 transition-colors">
                    Volver al Catálogo
                </a>
            </div>
        <?php else: ?>
            
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-0">
                    
                    <!-- Image Gallery Column -->
                    <div class="lg:col-span-7 bg-gray-100 p-6 flex flex-col items-center justify-center relative">
                        <!-- Main Image -->
                        <div class="w-full h-[400px] md:h-[500px] flex items-center justify-center overflow-hidden rounded-xl bg-white shadow-sm mb-4">
                            <?php if (!empty($producto['imagen_principal'])): ?>
                                <img id="mainImage" src="<?= BASE_URL . $producto['imagen_principal'] ?>" alt="<?= htmlspecialchars($producto['nom_producto']) ?>" class="object-contain max-h-full max-w-full">
                            <?php else: ?>
                                <i class="fas fa-image text-gray-300 text-6xl"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Thumbnails (if multiple images) -->
                         <?php if (!empty($producto['imagenes']) && count($producto['imagenes']) > 1): ?>
                            <div class="flex space-x-2 overflow-x-auto pb-2 w-full justify-center">
                                <?php foreach ($producto['imagenes'] as $img): ?>
                                    <button onclick="document.getElementById('mainImage').src='<?= BASE_URL . $img['url'] ?>'" class="w-20 h-20 border-2 border-transparent hover:border-naranja-artesanal rounded-lg overflow-hidden transition-all focus:outline-none focus:border-naranja-artesanal">
                                        <img src="<?= BASE_URL . $img['url'] ?>" class="w-full h-full object-cover">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Info Column -->
                    <div class="lg:col-span-5 p-8 lg:p-10 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                    <?= htmlspecialchars($producto['nom_categoria']) ?>
                                </span>
                                <?php if (!$producto['stock_productor']): ?>
                                    <span class="text-sm font-bold text-red-600 bg-red-50 px-2 py-1 rounded">Agotado</span>
                                <?php endif; ?>
                            </div>

                            <h1 class="text-3xl md:text-4xl font-extrabold text-tierra-oscuro mb-2 leading-tight">
                                <?= htmlspecialchars($producto['nom_producto']) ?>
                            </h1>

                            <!-- Stand Card -->
                            <div class="mt-4 mb-6 p-4 bg-orange-50/50 rounded-xl border border-orange-100 flex items-start gap-4">
                                <a href="<?= BASE_URL ?>stand/<?= $producto['id_productor'] ?>" class="flex-shrink-0 group">
                                    <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-white shadow-md group-hover:shadow-lg transition-all">
                                        <img src="<?= !empty($producto['img_stand']) ? BASE_URL . $producto['img_stand'] : BASE_URL . 'images/default_store.png' ?>" 
                                             alt="<?= htmlspecialchars($producto['nom_stand'] ?? 'Stand') ?>" 
                                             class="w-full h-full object-cover">
                                    </div>
                                </a>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-naranja-artesanal uppercase tracking-wider mb-1">Vendido por</p>
                                    <a href="<?= BASE_URL ?>stand/<?= $producto['id_productor'] ?>" class="block group">
                                        <h3 class="text-lg font-bold text-tierra-oscuro truncate group-hover:text-naranja-artesanal transition-colors">
                                            <?= htmlspecialchars($producto['nom_stand'] ?? $producto['nom_productor']) ?>
                                        </h3>
                                    </a>
                                    <?php if (!empty($producto['slogan_stand'])): ?>
                                        <p class="text-xs text-gray-500 italic truncate">"<?= htmlspecialchars($producto['slogan_stand']) ?>"</p>
                                    <?php endif; ?>
                                    <div class="flex items-center mt-2 text-xs text-gray-500">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <span class="truncate"><?= htmlspecialchars($producto['ubicacion'] ?? 'Colombia') ?></span>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <a href="<?= BASE_URL ?>stand/<?= $producto['id_productor'] ?>" class="text-blue-600 hover:underline font-medium">
                                            Ver perfil
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="text-3xl font-bold text-naranja-artesanal mb-6">
                                $<?= number_format($producto['precio_producto'], 0, ',', '.') ?>
                            </div>

                            <div class="prose prose-sm text-gray-600 mb-8 max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                                <p><?= nl2br(htmlspecialchars($producto['descripcion_producto'])) ?></p>
                            </div>
                            
                            <!-- Attributes (Color, Material, Craft) -->
                            <div class="grid grid-cols-2 gap-4 mb-8">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <span class="block text-xs text-gray-400 uppercase tracking-wider">Materia Prima</span>
                                    <span class="font-medium"><?= htmlspecialchars($producto['nom_materia']) ?></span>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <span class="block text-xs text-gray-400 uppercase tracking-wider">Técnica/Oficio</span>
                                    <span class="font-medium"><?= htmlspecialchars($producto['nom_oficio']) ?></span>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <span class="block text-xs text-gray-400 uppercase tracking-wider">Color</span>
                                    <span class="font-medium"><?= htmlspecialchars($producto['nom_color']) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="border-t border-gray-100 pt-6">
                            <div class="flex gap-4">
                                <div class="w-1/3">
                                    <label class="sr-only">Cantidad</label>
                                    <div class="flex items-center border border-gray-300 rounded-full overflow-hidden">
                                        <button class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors" onclick="if(this.nextElementSibling.value > 1) this.nextElementSibling.value--">-</button>
                                        <input type="number" value="1" min="1" max="<?= $producto['stock_productor'] ?>" class="w-full text-center border-none focus:ring-0 text-gray-700 font-semibold h-10" readonly>
                                        <button class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors" onclick="if(this.previousElementSibling.value < <?= $producto['stock_productor'] ?>) this.previousElementSibling.value++">+</button>
                                    </div>
                                </div>
                                <button class="flex-1 bg-tierra-oscuro text-white font-bold rounded-full hover:bg-opacity-90 transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                    <i class="fas fa-shopping-cart"></i>
                                    Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Related Products Section (Placeholder) -->
             <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">También te podría gustar</h2>
                <!-- Implement related products grid here later -->
                 <p class="text-gray-400 italic">Próximamente...</p>
             </div>

        <?php endif; ?>
    </main>

    <script src="<?= BASE_URL ?>src/scripts/web.js"></script>
</body>
</html>
