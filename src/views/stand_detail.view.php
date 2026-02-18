<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($stand['nom_stand'] ?? 'Stand') ?> | VIVA</title>
    <?php require_once __DIR__ . '/partials/tailwind_head.php'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>src/styles/web.css">
</head>
<body class="bg-gray-50 font-sans antialiased">
    
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
    <div class="container mx-auto px-4 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Column - Description -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-tierra-oscuro mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-3 text-naranja-artesanal"></i>
                        Sobre este emprendimiento
                    </h2>
                    
                    <?php if (!empty($stand['descripcion_stand'])): ?>
                        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                            <?= nl2br(htmlspecialchars($stand['descripcion_stand'])) ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 italic">No hay descripción disponible.</p>
                    <?php endif; ?>
                </div>

                <!-- Products Section (Future) -->
                <!-- 
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 mt-8">
                    <h2 class="text-2xl font-bold text-tierra-oscuro mb-6 flex items-center">
                        <i class="fas fa-box-open mr-3 text-naranja-artesanal"></i>
                        Productos
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        Product cards here
                    </div>
                </div>
                -->
            </div>

            <!-- Sidebar - Additional Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-4">
                    <h3 class="text-xl font-bold text-tierra-oscuro mb-4">Información</h3>
                    
                    <div class="space-y-4">
                        <!-- Producer ID -->
                        <div class="border-b border-gray-200 pb-4">
                            <p class="text-sm text-gray-500 mb-1">ID del Productor</p>
                            <p class="text-lg font-semibold text-tierra-oscuro">#<?= $stand['id_productor'] ?></p>
                        </div>

                        <!-- Stand ID -->
                        <div class="border-b border-gray-200 pb-4">
                            <p class="text-sm text-gray-500 mb-1">ID del Stand</p>
                            <p class="text-lg font-semibold text-tierra-oscuro">#<?= $stand['id_stand'] ?></p>
                        </div>

                        <!-- Created Date -->
                        <div class="border-b border-gray-200 pb-4">
                            <p class="text-sm text-gray-500 mb-1">Fecha de creación</p>
                            <p class="text-lg font-semibold text-tierra-oscuro">
                                <?= date('d/m/Y', strtotime($stand['created_at'])) ?>
                            </p>
                        </div>

                        <?php if (!empty($stand['updated_at'])): ?>
                        <!-- Updated Date -->
                        <div class="pb-4">
                            <p class="text-sm text-gray-500 mb-1">Última actualización</p>
                            <p class="text-lg font-semibold text-tierra-oscuro">
                                <?= date('d/m/Y', strtotime($stand['updated_at'])) ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- CTA Button (placeholder for future contact/shop features) -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <a href="#" class="block w-full bg-naranja-artesanal text-white text-center py-3 rounded-lg hover:bg-tierra-oscuro transition-colors font-medium">
                            <i class="fas fa-envelope mr-2"></i>Contactar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
