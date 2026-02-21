<?php 
$page_title = htmlspecialchars($stand['nom_stand'] ?? 'Stand') . " | VIVA";
$body_class = "bg-gray-50 font-sans antialiased";
require_once __DIR__ . '/partials/base_head.php'; 
?>
    
    <!-- Navbar -->
    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- Stand Hero Section -->
    <div class="relative">
        <!-- Cover Image -->
        <div class="h-64 md:h-80 bg-gradient-to-r from-tierra-claro to-beige-suave overflow-hidden">
            <?php if (!empty($stand['portada_stand'])): ?>
                <img src="<?= BASE_URL . $stand['portada_stand'] ?>" 
                     alt="Portada de <?= htmlspecialchars($stand['nom_stand']) ?>"
                     class="w-full h-full object-cover">
            <?php endif; ?>
        </div>

        <!-- Logo and Basic Info (Overlapping) -->
        <div class="container mx-auto px-4">
            <div class="relative -mt-20 pb-8">
                <div class="bg-white rounded-xl shadow-xl p-6 md:p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 md:w-40 md:h-40 bg-white rounded-full p-2 shadow-lg overflow-hidden border-4 border-white">
                                <img src="<?= !empty($stand['img_stand']) ? BASE_URL . $stand['img_stand'] : BASE_URL . 'images/default.jpg' ?>" 
                                     alt="<?= htmlspecialchars($stand['nom_stand']) ?>"
                                     class="w-full h-full rounded-full object-cover">
                            </div>
                        </div>

                        <!-- Stand Info -->
                        <div class="flex-1 text-center md:text-left">
                            <h1 class="text-3xl md:text-4xl font-bold text-tierra-oscuro mb-2">
                                <?= htmlspecialchars($stand['nom_stand'] ?? 'Sin nombre') ?>
                            </h1>
                            
                            <!-- Stars -->
                            <?php if($total_resenas_stand > 0): ?>
                            <div class="flex items-center justify-center md:justify-start mb-2">
                                <div class="flex text-yellow-400 text-sm mr-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= round($promedio_estrellas_stand) ? '' : 'text-gray-300' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-sm font-bold text-gray-700"><?= number_format($promedio_estrellas_stand, 1) ?></span>
                                <span class="text-xs text-blue-600 ml-2">(<?= $total_resenas_stand ?> reseñas globales)</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($stand['slogan_stand'])): ?>
                                <p class="text-lg text-naranja-artesanal font-medium mb-4">
                                    <?= htmlspecialchars($stand['slogan_stand']) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Stand Metadata -->
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt mr-2 text-naranja-artesanal"></i>
                                    <span>Creado el <?= date('d/m/Y', strtotime($stand['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 pb-16">
        <div class="max-w-4xl mx-auto space-y-12">
            <!-- Main Column - Description -->
            <div>
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-tierra-oscuro mb-4 flex items-center">
                        Descripción
                    </h2>
                    
                    <?php if (!empty($stand['descripcion_stand'])): ?>
                        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                            <?= nl2br(htmlspecialchars($stand['descripcion_stand'])) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 italic">No hay descripción disponible.</p>
                    <?php endif; ?>
                </div>

                <!-- Products Section -->
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 mt-8">
                    <h2 class="text-2xl font-bold text-tierra-oscuro mb-6 flex items-center">
                        <i class="fas fa-box-open mr-3 text-naranja-artesanal"></i>
                        Catálogo del Stand
                    </h2>
                    
                    <?php if (empty($productos_stand)): ?>
                        <div class="text-center py-10 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                            <i class="fas fa-store-slash text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Este artesano aún no tiene productos publicados.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php 
                            $show_price = true;
                            foreach ($productos_stand as $product): 
                                require __DIR__ . '/partials/card_producto.php';
                            endforeach; 
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
