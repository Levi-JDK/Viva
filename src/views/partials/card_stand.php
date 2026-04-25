<?php
/**
 * PARTIAL: TARJETA DE STAND — Componente Reutilizable
 * 
 * Muestra un stand como una tarjeta moderna con imagen de portada, logo, nombre, slogan y descripción.
 * 
 * ==========================================
 * ESTRUCTURA DE BASE DE DATOS (tab_stand):
 * ==========================================
 * 
 * El array $stand debe provenir directamente de una consulta a la tabla tab_stand:
 * 
 * SELECT * FROM tab_stand WHERE id_productor = :id;
 * 
 * Columnas esperadas:
 * - id_productor (DECIMAL)          - ID del productor
 * - id_stand (DECIMAL)              - ID del stand
 * - nom_stand (VARCHAR)             - Nombre del stand
 * - slogan_stand (VARCHAR)          - Slogan
 * - descripcion_stand (TEXT)        - Descripción
 * - img_stand (VARCHAR)             - Ruta del logo (relativa a BASE_URL)
 * - portada_stand (VARCHAR)         - Ruta de la portada (relativa a BASE_URL)
 * 
 * ==========================================
 * CÓMO USAR:
 * ==========================================
 * 
 * OPCIÓN 1 — Stand individual:
 * <?php
 * // $stmt = $db->ejecutar('SELECT * FROM tab_stand WHERE id_productor = :id', [':id' => $id]);
 * // $stand = $stmt->fetch(PDO::FETCH_ASSOC);
 * // require_once __DIR__ . '/partials/card_stand.php';
 * ?>
 * 
 * OPCIÓN 2 — Múltiples stands en una grilla:
 * <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
 * <?php
 * // $stmt = $db->ejecutar('SELECT * FROM tab_stand WHERE is_deleted = FALSE');
 * // while ($stand = $stmt->fetch(PDO::FETCH_ASSOC)) {
 * //     require __DIR__ . '/partials/card_stand.php';
 * // }
 * ?>
 * </div>
 * 
 * ==========================================
 * PERSONALIZACIÓN:
 * ==========================================
 * 
 * Variables opcionales que puedes definir antes de incluir el partial:
 * - $show_link: Si se muestra el botón "Ver Stand" (por defecto: false)
 * - $stand_url: URL del enlace al stand (si $show_link es true)
 * 
 */

// Valores por defecto si no se proporcionan
$show_link = $show_link ?? false;
$stand_url = $stand_url ?? '#';

// Verificar que $stand exista
if (!isset($stand) || empty($stand)) {
    echo '<div class="text-red-500 p-4 border border-red-300 rounded">Error: No se proporcionaron datos del stand</div>';
    return;
}

$stand_cover_url = !empty($stand['portada_stand']) ? base_url_path($stand['portada_stand']) : 'NULL';
$stand_logo_url = !empty($stand['img_stand']) ? base_url_path($stand['img_stand']) : base_url_path('images/profiles/default.webp');
?>

<!-- Componente: Tarjeta de Stand -->
<div class="w-full bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
    <!-- Imagen de Portada (con relación de aspecto adecuada) -->
    <div class="h-40 bg-gradient-to-r from-tierra-claro to-beige-suave relative overflow-hidden">
        <?php if (!empty($stand['portada_stand'])): ?>
            <img src="<?= $stand_cover_url ?>" 
                 alt="Portada de <?= htmlspecialchars($stand['nom_stand'] ?? 'Stand') ?>"
                 class="w-full h-full object-cover">
        <?php endif; ?>
    </div>
    
    <!-- Contenido del Stand -->
    <div class="relative px-6 pb-6">
        <!-- Logo (superpuesto sobre la portada) -->
        <div class="flex justify-center -mt-12 mb-4">
            <div class="w-24 h-24 bg-white rounded-full p-1 shadow-lg overflow-hidden">
                <img src="<?= $stand_logo_url ?>" 
                     alt="<?= htmlspecialchars($stand['nom_stand'] ?? 'Stand') ?>"
                     class="w-full h-full rounded-full object-cover">
            </div>
        </div>
        
        <!-- Información del Stand -->
        <div class="text-center">
            <h3 class="text-xl font-bold text-tierra-oscuro mb-1">
                <?= htmlspecialchars($stand['nom_stand'] ?? 'Sin nombre') ?>
            </h3>
            
            <?php if (!empty($stand['slogan_stand'])): ?>
                <p class="text-sm text-naranja-artesanal font-medium mb-4">
                    <?= htmlspecialchars($stand['slogan_stand']) ?>
                </p>
            <?php endif; ?>
            
            <?php if ($show_link): ?>
                <a href="<?= htmlspecialchars($stand_url) ?>" 
                   class="inline-block bg-naranja-artesanal text-white px-6 py-2 rounded-full hover:bg-tierra-oscuro transition-colors font-medium text-sm">
                    <i class="fas fa-store mr-2"></i>Ver Stand
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
