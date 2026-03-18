<?php // if (isset($_GET['debug'])) echo "View Loaded...<br>"; ?>
<?php 
$page_title = ($producto && isset($producto['nom_producto'])) ? htmlspecialchars($producto['nom_producto']) . " | VIVA" : "Producto no encontrado | VIVA";
$body_class = "bg-gray-50 font-sans antialiased text-gray-800";
require_once __DIR__ . '/partials/base_head.php'; 
?>    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

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
            
            <!-- Breadcrumbs -->
            <nav class="flex text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="<?= BASE_URL ?>" class="hover:text-naranja-artesanal transition-colors">
                            <i class="fas fa-home mr-2"></i>Inicio
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-xs mx-2"></i>
                            <a href="<?= BASE_URL ?>catalogo?cat=<?= $producto['id_categoria'] ?>" class="hover:text-naranja-artesanal transition-colors">
                                <?= htmlspecialchars($producto['nom_categoria']) ?>
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-xs mx-2"></i>
                            <span class="text-gray-800 font-medium truncate max-w-[200px]"><?= htmlspecialchars($producto['nom_producto']) ?></span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-0">
                    
                    <!-- Image Gallery Column -->
                    <div class="lg:col-span-7 bg-gray-100 p-6 flex flex-col items-center justify-center relative">
                        <!-- Main Image -->
                        <div class="w-full h-[400px] md:h-[500px] mb-4">
                            <?php if (!empty($producto['imagen_principal'])): ?>
                                <div id="imageContainer" class="w-full h-full rounded-2xl overflow-hidden shadow-sm bg-white flex items-center justify-center p-4 relative group cursor-zoom-in">
                                    <img id="mainImage" src="<?= BASE_URL . $producto['imagen_principal'] ?>" alt="<?= htmlspecialchars($producto['nom_producto']) ?>" class="max-w-full max-h-full object-contain transition-transform duration-200">
                                </div>
                            <?php else: ?>
                                <div class="w-full h-full rounded-2xl overflow-hidden shadow-sm bg-white flex items-center justify-center p-4 relative group">
                                    <i class="fas fa-image text-gray-300 text-6xl"></i>
                                </div>
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

                            <!-- Review Stars Summary -->
                            <div class="flex items-center mb-4 cursor-pointer hover:opacity-80 transition-opacity" onclick="document.getElementById('reviews-section').scrollIntoView({behavior: 'smooth'})">
                                <div class="flex text-yellow-400 text-sm mr-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= floor($promedio_estrellas)): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif($i == ceil($promedio_estrellas) && $promedio_estrellas - floor($promedio_estrellas) > 0): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-gray-300"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-sm font-bold text-gray-700"><?= number_format($promedio_estrellas, 1) ?></span>
                                <span class="text-xs text-blue-600 underline ml-2">(<?= $total_resenas ?> reseñas)</span>
                            </div>

                            <!-- Stand o Perfil de Vendedor -->
                            <?php 
                                $has_stand = !empty($producto['nom_stand']);
                                $vendedor_nombre = $has_stand ? $producto['nom_stand'] : $producto['nom_productor'];
                                $vendedor_imagen = $has_stand && !empty($producto['img_stand']) ? $producto['img_stand'] : ($producto['foto_user'] ?? 'images/default_user.jpg');
                                $vendedor_slogan = $has_stand ? $producto['slogan_stand'] : 'Artesano Colombiano';
                            ?>
                            <div class="mt-4 mb-6 p-4 bg-orange-50/50 rounded-xl border border-orange-100 flex items-start gap-4">
                                <a href="<?= BASE_URL ?>stand?id=<?= $producto['id_stand'] ?>" class="flex-shrink-0 group">
                                    <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-white shadow-md group-hover:shadow-lg transition-all">
                                        <img src="<?= BASE_URL . $vendedor_imagen ?>" 
                                             alt="<?= htmlspecialchars($vendedor_nombre) ?>" 
                                             class="w-full h-full object-cover">
                                    </div>
                                </a>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-naranja-artesanal uppercase tracking-wider mb-1">Vendido por</p>
                                    <a href="<?= BASE_URL ?>stand?id=<?= $producto['id_stand'] ?>" class="block group">
                                        <h3 class="text-lg font-bold text-tierra-oscuro truncate group-hover:text-naranja-artesanal transition-colors">
                                            <?= htmlspecialchars($vendedor_nombre) ?>
                                        </h3>
                                    </a>
                                    <?php if (!empty($vendedor_slogan)): ?>
                                        <p class="text-xs text-gray-500 italic truncate">"<?= htmlspecialchars($vendedor_slogan) ?>"</p>
                                    <?php endif; ?>
                                    <div class="flex items-center mt-2 text-xs text-gray-500">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <span class="truncate"><?= htmlspecialchars($producto['ubicacion'] ?? 'Colombia') ?></span>
                                        <span class="mx-2 text-gray-300">|</span>
                                        <a href="<?= BASE_URL ?>stand?id=<?= $producto['id_stand'] ?>" class="text-blue-600 hover:underline font-medium">
                                            Ver <?= $has_stand ? 'Stand' : 'Perfil' ?>
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
                                <button onclick="agregarAlCarrito(<?= (int)$producto['id_producto'] ?>, parseInt(this.closest('div').querySelector('input').value))"
                                        <?= !$producto['stock_productor'] ? 'disabled' : '' ?>
                                        class="flex-1 bg-tierra-oscuro text-white font-bold rounded-full hover:bg-opacity-90 transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-shopping-cart"></i>
                                    <?= $producto['stock_productor'] ? 'Agregar al Carrito' : 'Sin stock' ?>
                                </button>
                                
                                <button onclick="toggleFavorito(<?= (int)$producto['id_producto'] ?>, this, event)"
                                        data-id-producto="<?= (int)$producto['id_producto'] ?>"
                                        class="btn-favorito w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:text-red-500 hover:border-red-200 hover:bg-red-50 transition-all flex-shrink-0"
                                        aria-label="Alternar favorito">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>

                            <!-- Trust Signals -->
                            <div class="mt-6 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4">
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="fas fa-shield-alt text-green-500 mr-2 text-base"></i>
                                    <span>Pago 100% seguro</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="fas fa-truck text-blue-500 mr-2 text-base"></i>
                                    <span>Envíos a todo el país</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="fas fa-undo text-orange-500 mr-2 text-base"></i>
                                    <span>Devolución garantizada</span>
                                </div>
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="fas fa-award text-purple-500 mr-2 text-base"></i>
                                    <span>Artesanía auténtica</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Reviews Section -->
            <div id="reviews-section" class="mt-12 bg-white rounded-2xl shadow-xl p-8">
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Global Rating -->
                    <div class="w-full md:w-1/3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200 pb-8 md:pb-0">
                        <h2 class="text-2xl text-gray-800 font-bold mb-4">Opiniones</h2>
                        <div class="text-6xl font-extrabold text-tierra-oscuro"><?= number_format($promedio_estrellas, 1) ?></div>
                        <div class="flex text-yellow-400 text-2xl my-3">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= floor($promedio_estrellas)): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif($i == ceil($promedio_estrellas) && $promedio_estrellas - floor($promedio_estrellas) > 0): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-gray-300"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <p class="text-gray-500"><?= $total_resenas ?> reseñas de clientes</p>

                        <?php if($is_logged_in): ?>
                            <!-- Escribir reseña Button -->
                            <button onclick="document.getElementById('review-form-container').classList.toggle('hidden')" class="mt-6 px-6 py-2 border-2 border-naranja-artesanal text-naranja-artesanal rounded-full font-bold hover:bg-naranja-artesanal hover:text-white transition-colors">
                                Escribir una reseña
                            </button>
                        <?php else: ?>
                            <p class="mt-4 text-sm text-gray-500">Debes <a href="<?= BASE_URL ?>login" class="text-blue-600 hover:underline">iniciar sesión</a> para opinar.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Reviews List -->
                    <div class="w-full md:w-2/3">
                        
                        <!-- Review Form (Hidden by default) -->
                        <div id="review-form-container" class="hidden mb-8 bg-gray-50 p-6 rounded-xl border border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Tu Reseña</h3>
                            <form id="formResena" onsubmit="enviarResena(event, <?= (int)$producto['id_producto'] ?>)">
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-2">Calificación</label>
                                    <div class="flex gap-2 text-2xl cursor-pointer" id="star-rating">
                                        <i class="fas fa-star text-gray-300 transition-colors duration-200" data-value="1"></i>
                                        <i class="fas fa-star text-gray-300 transition-colors duration-200" data-value="2"></i>
                                        <i class="fas fa-star text-gray-300 transition-colors duration-200" data-value="3"></i>
                                        <i class="fas fa-star text-gray-300 transition-colors duration-200" data-value="4"></i>
                                        <i class="fas fa-star text-gray-300 transition-colors duration-200" data-value="5"></i>
                                    </div>
                                    <input type="hidden" name="calificacion" id="calificacion_input" required>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-2">Tu opinión</label>
                                    <textarea name="texto" rows="3" class="w-full rounded-lg border-gray-300 p-3 focus:ring-naranja-artesanal" required placeholder="¿Qué te pareció el producto?"></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="button" onclick="document.getElementById('review-form-container').classList.add('hidden')" class="px-4 py-2 text-gray-500 hover:text-gray-700 mr-2">Cancelar</button>
                                    <button type="submit" class="px-6 py-2 bg-naranja-artesanal text-white rounded-lg font-bold hover:bg-orange-600">Enviar</button>
                                </div>
                            </form>
                        </div>

                        <?php if (empty($resenas)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="far fa-comment-alt text-4xl mb-3 text-gray-300"></i>
                                <p>Aún no hay reseñas para este producto. ¡Sé el primero en opinar!</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-6 max-h-[400px] overflow-y-auto pr-4 custom-scrollbar">
                                <?php foreach ($resenas as $r): ?>
                                    <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 shadow-sm border border-gray-100">
                                                    <img src="<?= BASE_URL . $r['foto_user'] ?>" alt="Foto" class="w-full h-full object-cover">
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-gray-800 text-sm"><?= htmlspecialchars($r['nom_user'] . ' ' . $r['ape_user']) ?></h4>
                                                    <div class="text-xs text-gray-400"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                                                </div>
                                            </div>
                                            <div class="flex text-yellow-400 text-xs">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $r['calificacion'] ? '' : 'text-gray-300' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="text-gray-600 text-sm mt-2"><?= nl2br(htmlspecialchars($r['texto_resena'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($productos_relacionados)): ?>
             <div class="mt-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3"><i class="fas fa-heart text-naranja-artesanal"></i>También te podría gustar</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php 
                    $show_price = true;
                    foreach ($productos_relacionados as $product):
                        require __DIR__ . '/partials/card_producto.php';
                    endforeach; 
                    ?>
                </div>
             </div>
             <?php endif; ?>

        <?php endif; ?>
    </main>

    <script src="<?= BASE_URL ?>src/scripts/producto_detalle.js"></script>
    <!-- Drawer del Carrito -->
    <?php require_once __DIR__ . '/partials/carrito.php'; ?>
    <script src="<?= BASE_URL ?>src/scripts/carrito.js"></script>
</body>
</html>
