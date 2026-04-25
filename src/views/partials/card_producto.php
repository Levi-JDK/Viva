<?php
/**
 * PARTIAL: TARJETA DE PRODUCTO — Componente Reutilizable
 * 
 * Muestra un producto como una tarjeta con imagen, nombre, precio (opcional) e info del stand.
 * 
 * ==========================================
 * ESTRUCTURA DE BASE DE DATOS:
 * ==========================================
 * 
 * El array $product debe provenir de una consulta con JOIN:
 * 
 * SELECT 
 *     p.*,
 *     s.nom_stand,
 *     s.img_stand,
 *     (SELECT url_imagen FROM tab_imagenes WHERE id_producto = p.id_producto LIMIT 1) as primera_imagen
 * FROM tab_productos p
 * LEFT JOIN tab_stand s ON p.id_productor = s.id_productor
 * WHERE p.is_deleted = FALSE AND p.is_active = TRUE
 * 
 * Columnas esperadas:
 * - id_producto (DECIMAL)           - ID del producto
 * - nom_producto (VARCHAR)          - Nombre del producto
 * - precio_producto (DECIMAL)       - Precio del producto
 * - id_productor (DECIMAL)          - ID del productor
 * - nom_stand (VARCHAR)             - Nombre del stand
 * - img_stand (VARCHAR)             - Logo del stand
 * - primera_imagen (VARCHAR)        - URL de la primera imagen del producto
 * 
 * ==========================================
 * CÓMO USAR:
 * ==========================================
 * 
 * OPCIÓN 1 — Landing page (sin precio):
 * <?php
 * // $show_price = false; // Ocultar precio en el landing
 * // foreach ($products as $product) {
 * //     require __DIR__ . '/partials/card_producto.php';
 * // }
 * ?>
 * 
 * OPCIÓN 2 — Catálogo (con precio):
 * <?php
 * // $show_price = true; // Mostrar precio en el catálogo
 * // foreach ($products as $product) {
 * //     require __DIR__ . '/partials/card_producto.php';
 * // }
 * ?>
 * 
 * ==========================================
 * PERSONALIZACIÓN:
 * ==========================================
 * 
 * Variables opcionales que puedes definir antes de incluir el partial:
 * - $show_price: Si se muestra el precio (por defecto: true)
 * 
 */

// Valores por defecto si no se proporcionan
$show_price = $show_price ?? true;
// IMPORTANTE: NO usar ?? aquí — $product_url debe recalcularse en cada iteración del foreach.
// Usar ?? cachearía el URL del primer producto para todas las tarjetas siguientes.
$product_url = base_url_path('producto?id=' . ($product['id_producto'] ?? ''));

// Verificar que $product exista
if (!isset($product) || empty($product)) {
    echo '<div class="text-red-500 p-4 border border-red-300 rounded">Error: No se proporcionaron datos del producto</div>';
    return;
}

// Obtener primera imagen estandarizada o usar placeholder
$product_image_path = $product['primera_imagen'] ?? $product['imagen_principal'] ?? null;
$product_image = !empty($product_image_path)
    ? base_url_path($product_image_path)
    : '/var/www/html/viva/images/default.webp';
$stand_logo = !empty($product['img_stand']) ? base_url_path($product['img_stand']) : base_url_path('images/default.webp');
?>

<!-- Componente: Tarjeta de Producto -->
<div class="product-card bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 flex flex-col group h-full relative">
    <!-- Imagen del Producto — es el link principal -->
    <a href="<?= htmlspecialchars($product_url) ?>" class="block relative group/img">
        <div class="h-64 bg-gradient-to-br from-tierra-claro to-beige-suave relative overflow-hidden">
            <img src="<?= $product_image ?>"
                 alt="<?= htmlspecialchars($product['nom_producto'] ?? 'Producto') ?>"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
        </div>
        
        <!-- Botón de favorito superpuesto -->
        <button data-action="fav-toggle" data-id="<?= (int)($product['id_producto'] ?? 0) ?>"
                data-id-producto="<?= (int)($product['id_producto'] ?? 0) ?>"
                class="btn-favorito absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm hover:shadow-md transition-all z-10 hover:scale-110"
                aria-label="Alternar favorito">
            <i class="fa-regular fa-heart text-gray-400 text-lg"></i>
        </button>
    </a>

    <!-- Información del Producto -->
    <div class="p-5 flex-1 flex flex-col">
        <!-- Nombre del Producto -->
        <a href="<?= htmlspecialchars($product_url) ?>">
            <h3 class="font-bold text-lg text-tierra-oscuro mb-2 line-clamp-2 group-hover:text-naranja-artesanal transition-colors">
                <?= htmlspecialchars($product['nom_producto'] ?? 'Sin nombre') ?>
            </h3>
        </a>

        <!-- Información del Stand (Productor) -->
        <div class="flex items-center gap-2 mb-3">
            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-tierra-claro">
                <img src="<?= $stand_logo ?>"
                     alt="<?= htmlspecialchars($product['nom_stand'] ?? 'Stand') ?>"
                     class="w-full h-full object-cover">
            </div>
            <span class="text-sm text-gray-600 truncate">
                <?= htmlspecialchars($product['nom_stand'] ?? 'Stand artesanal') ?>
            </span>
        </div>

        <!-- Espaciador -->
        <div class="flex-1"></div>

        <!-- Precio (condicional) -->
        <?php if ($show_price): ?>
            <div class="mt-auto pt-3 border-t border-gray-100">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-2xl font-bold text-tierra-oscuro">
                        $<?= number_format($product['precio_producto'] ?? 0, 0, ',', '.') ?>
                    </span>
                    <!-- Botón Agregar al Carrito -->
                    <button
                        data-action="cart-add" data-id="<?= (int)($product['id_producto'] ?? 0) ?>" data-qty="1"
                        class="btn-agregar-carrito bg-naranja-artesanal text-white px-4 py-2 rounded-lg text-sm font-medium
                               hover:bg-tierra-oscuro active:scale-95 transition-all flex items-center gap-1.5">
                        <i class="fas fa-shopping-cart text-xs"></i>
                        Agregar
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- Botón "Ver más" para el Landing -->
            <div class="mt-auto pt-3">
                <a href="<?= htmlspecialchars($product_url) ?>" class="block text-center">
                    <span class="inline-flex items-center text-naranja-artesanal font-medium group-hover:text-tierra-oscuro transition-colors">
                        Ver más
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
