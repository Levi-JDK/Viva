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
$stand_cover_thumb_url = !empty($stand['portada_stand']) ? base_url_path(str_replace('_full_', '_thumb_', $stand['portada_stand'])) : '';
$stand_cover_medium_url = !empty($stand['portada_stand']) ? base_url_path(str_replace('_full_', '_medium_', $stand['portada_stand'])) : '';
$stand_logo_url = !empty($stand['img_stand']) ? base_url_path($stand['img_stand']) : base_url_path('images/profiles/default.webp');
?>

<!-- Componente: Tarjeta de Stand -->
<!-- h-full + flex-col permiten que el grid iguale alturas aunque algunos stands tengan slogan y otros no. -->
<div class="w-full h-full bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col">
    <!-- aspect-[5/1] compacta el banner en mobile sin px; desktop conserva el formato panorámico. -->
    <div class="aspect-[5/1] sm:aspect-[21/9] bg-gradient-to-r from-tierra-claro to-beige-suave relative overflow-hidden">
        <?php if (!empty($stand['portada_stand'])): ?>
            <!-- srcset reduce descarga en mobile y aspect-ratio reserva espacio para evitar CLS. -->
            <img src="<?= $stand_cover_url ?>" 
                 srcset="<?= htmlspecialchars($stand_cover_thumb_url) ?> 320w, <?= htmlspecialchars($stand_cover_medium_url) ?> 640w, <?= htmlspecialchars($stand_cover_url) ?> 1024w"
                 sizes="(min-width: 1024px) 33vw, (min-width: 768px) 50vw, 100vw"
                 alt="Portada de <?= htmlspecialchars($stand['nom_stand'] ?? 'Stand') ?>"
                 loading="lazy"
                 class="w-full h-full object-cover">
        <?php endif; ?>
    </div>
    
    <!-- Contenido del Stand -->
    <!-- Padding menor en mobile reduce scroll; desde sm se mantiene la respiración visual original. -->
    <div class="relative px-4 pb-4 sm:px-6 sm:pb-6 flex-1 flex flex-col">
        <!-- Logo (superpuesto sobre la portada) -->
        <!-- Logo compacto en mobile evita que la superposición domine la card; desktop mantiene el tamaño original. -->
        <div class="flex justify-center -mt-8 sm:-mt-12 mb-2 sm:mb-4">
            <div class="w-16 h-16 sm:w-24 sm:h-24 bg-white rounded-full p-1 shadow-lg overflow-hidden">
                <!-- Logo con dimensiones de contenedor conocidas; lazy evita coste inicial fuera del viewport. -->
                <img src="<?= $stand_logo_url ?>" 
                     alt="<?= htmlspecialchars($stand['nom_stand'] ?? 'Stand') ?>"
                     loading="lazy"
                     class="w-full h-full rounded-full object-cover">
            </div>
        </div>
        
        <!-- Información del Stand: flex-1 permite que el botón se ancle abajo aunque falte slogan. -->
        <div class="text-center flex-1 flex flex-col">
            <!-- Título más chico en mobile mejora densidad sin perder jerarquía en desktop. -->
            <h3 class="text-lg sm:text-xl font-bold text-tierra-oscuro mb-1">
                <?= htmlspecialchars($stand['nom_stand'] ?? 'Sin nombre') ?>
            </h3>
            
            <?php if (!empty($stand['slogan_stand'])): ?>
                <!-- El slogan se oculta en mobile porque aporta menos que el espacio vertical que consume. -->
                <p class="hidden sm:block text-sm text-naranja-artesanal font-medium mb-2 sm:mb-4">
                    <?= htmlspecialchars($stand['slogan_stand']) ?>
                </p>
            <?php endif; ?>
            
            <?php if ($show_link): ?>
                <!-- mt-auto empuja el CTA al final de la card; pt-4 evita que quede pegado al texto. -->
                <div class="mt-auto pt-4">
                    <a href="<?= htmlspecialchars($stand_url) ?>" 
                       class="inline-block bg-naranja-artesanal text-white px-4 py-1.5 sm:px-6 sm:py-2 rounded-full hover:bg-tierra-oscuro transition-colors font-medium text-xs sm:text-sm">
                        <i class="fas fa-store mr-2"></i>Ver Stand
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
