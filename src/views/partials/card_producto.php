<?php
/**
 * PRODUCT CARD PARTIAL - Reusable Product Display Component
 * 
 * This partial displays a product as a card with image, name, price (optional), and stand info.
 * 
 * ==========================================
 * DATABASE STRUCTURE:
 * ==========================================
 * 
 * The $product array should come from a JOIN query:
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
 * Expected columns:
 * - id_producto (DECIMAL)           - Product ID
 * - nom_producto (VARCHAR)          - Product name
 * - precio_producto (DECIMAL)       - Product price
 * - id_productor (DECIMAL)          - Producer ID
 * - nom_stand (VARCHAR)             - Stand name
 * - img_stand (VARCHAR)             - Stand logo
 * - primera_imagen (VARCHAR)        - First product image URL
 * 
 * ==========================================
 * HOW TO USE:
 * ==========================================
 * 
 * OPTION 1 - Landing page (without price):
 * <?php
 * // $show_price = false; // Hide price for landing
 * // foreach ($products as $product) {
 * //     require __DIR__ . '/partials/card_producto.php';
 * // }
 * ?>
 * 
 * OPTION 2 - Catalog (with price):
 * <?php
 * // $show_price = true; // Show price in catalog
 * // foreach ($products as $product) {
 * //     require __DIR__ . '/partials/card_producto.php';
 * // }
 * ?>
 * 
 * ==========================================
 * CUSTOMIZATION:
 * ==========================================
 * 
 * Optional variables you can define before including:
 * - $show_price: Whether to show the price (default: true)
 * - $product_url: Custom URL for the product link (default: BASE_URL . 'producto?id=' . $product['id_producto'])
 * 
 */

// Set default values if not provided
$show_price = $show_price ?? true;
$product_url = $product_url ?? (BASE_URL . 'producto?id=' . ($product['id_producto'] ?? ''));

// Ensure $product exists
if (!isset($product) || empty($product)) {
    echo '<div class="text-red-500 p-4 border border-red-300 rounded">Error: Product data not provided</div>';
    return;
}

// Get first image or use placeholder
$product_image = !empty($product['primera_imagen']) ? BASE_URL . $product['primera_imagen'] : BASE_URL . 'images/default_product.jpg';
$stand_logo = !empty($product['img_stand']) ? BASE_URL . $product['img_stand'] : BASE_URL . 'images/default.jpg';
?>

<!-- Product Card Component -->
<a href="<?= htmlspecialchars($product_url) ?>" class="product-card bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 flex flex-col group">
    <!-- Product Image -->
    <div class="h-64 bg-gradient-to-br from-tierra-claro to-beige-suave relative overflow-hidden">
        <img src="<?= $product_image ?>" 
             alt="<?= htmlspecialchars($product['nom_producto'] ?? 'Producto') ?>"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        
        <!-- Overlay on hover -->
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
    </div>
    
    <!-- Product Info -->
    <div class="p-5 flex-1 flex flex-col">
        <!-- Product Name -->
        <h3 class="font-bold text-lg text-tierra-oscuro mb-2 line-clamp-2 group-hover:text-naranja-artesanal transition-colors">
            <?= htmlspecialchars($product['nom_producto'] ?? 'Sin nombre') ?>
        </h3>
        
        <!-- Stand Info (Producer) -->
        <div class="flex items-center gap-2 mb-3">
            <div class="w-6 h-6 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-tierra-claro">
                <img src="<?= $stand_logo ?>" 
                     alt="<?= htmlspecialchars($product['nom_stand'] ?? 'Stand') ?>"
                     class="w-full h-full object-cover">
            </div>
            <span class="text-sm text-gray-600 truncate">
                <?= htmlspecialchars($product['nom_stand'] ?? 'Stand artesanal') ?>
            </span>
        </div>
        
        <!-- Spacer -->
        <div class="flex-1"></div>
        
        <!-- Price (conditional) -->
        <?php if ($show_price): ?>
            <div class="mt-auto pt-3 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-2xl font-bold text-tierra-oscuro">
                        $<?= number_format($product['precio_producto'] ?? 0, 0, ',', '.') ?>
                    </span>
                    <button class="bg-naranja-artesanal text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-tierra-oscuro transition-colors">
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Comprar
                    </button>
                </div>
            </div>
        <?php else: ?>
            <!-- View More Button for Landing -->
            <div class="mt-auto pt-3">
                <div class="text-center">
                    <span class="inline-flex items-center text-naranja-artesanal font-medium group-hover:text-tierra-oscuro transition-colors">
                        Ver más
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</a>
