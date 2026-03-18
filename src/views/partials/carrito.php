<?php
/**
 * Partial: Drawer del Carrito de Compras
 *
 * Incluir este partial en todas las páginas donde el carrito deba estar disponible.
 * El drawer se abre/cierra mediante JS a través de la función toggleCarrito().
 *
 * Requiere:
 *   - BASE_URL definido (se carga desde base_head.php)
 *   - toast.js cargado antes
 *   - carrito.js cargado después de este partial
 */
?>
<script>
    window.USER_IS_LOGGED_IN = <?= isset($is_logged_in) && $is_logged_in ? 'true' : 'false' ?>;
    window.LOGIN_URL = '<?= BASE_URL ?>login';
</script>

<!-- ═══════════════════════════════════════════════════════
     OVERLAY: Fondo oscuro al abrir el carrito
════════════════════════════════════════════════════════ -->
<div id="carrito-overlay"
     class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-300"
     onclick="toggleCarrito()">
</div>

<!-- ═══════════════════════════════════════════════════════
     DRAWER: Panel lateral derecho del carrito
════════════════════════════════════════════════════════ -->
<aside id="carrito-drawer"
       class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl z-50 flex flex-col
              translate-x-full transition-transform duration-300 ease-in-out">

    <!-- Encabezado del carrito -->
    <header class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-white">
        <div class="flex items-center gap-3">
            <i class="fas fa-shopping-cart text-principal text-lg"></i>
            <h2 class="text-lg font-bold text-oscuro">Mi Carrito</h2>
            <!-- Badge con el total de ítems -->
            <span id="carrito-badge-drawer"
                  class="bg-principal text-white text-xs font-bold px-2 py-0.5 rounded-full hidden">
                0
            </span>
        </div>
        <button onclick="toggleCarrito()"
                class="text-gray-400 hover:text-oscuro transition-colors p-1 rounded-full hover:bg-gray-100"
                aria-label="Cerrar carrito">
            <i class="fas fa-times text-lg"></i>
        </button>
    </header>

    <!-- Lista de ítems (se renderiza dinámicamente con JS) -->
    <div id="carrito-items"
         class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

        <!-- Estado vacío (visible por defecto hasta que se carguen los ítems) -->
        <div id="carrito-vacio" class="flex flex-col items-center justify-center h-full text-center py-16">
            <div class="w-20 h-20 rounded-full bg-orange-50 flex items-center justify-center mb-4">
                <i class="fas fa-shopping-cart text-3xl text-naranja-artesanal/40"></i>
            </div>
            <p class="text-gray-500 font-medium">Tu carrito está vacío</p>
            <p class="text-sm text-gray-400 mt-1">Agrega productos para comenzar</p>
        </div>

        <!-- Los ítems del carrito se insertan aquí dinámicamente -->
    </div>

    <!-- Resumen y acciones (visible solo si hay ítems) -->
    <footer id="carrito-footer" class="hidden border-t border-gray-100 px-5 py-4 bg-white space-y-3">

        <!-- Resumen de precio -->
        <div class="flex justify-between items-center text-sm text-gray-500">
            <span>Subtotal (<span id="carrito-total-items">0</span> productos)</span>
            <span class="font-bold text-lg text-oscuro">
                $<span id="carrito-total-precio">0</span>
            </span>
        </div>

        <!-- Contenedor confirmación limpiar (oculto por defecto) -->
        <div id="confirmacion-limpiar" class="hidden flex-col items-center justify-center p-3 rounded-lg bg-red-50 border border-red-100 my-2">
            <p class="text-xs font-semibold text-red-600 mb-2 text-center"><i class="fas fa-exclamation-triangle mr-1"></i> ¿Vaciar todo el carrito?</p>
            <div class="flex gap-2 w-full">
                <button onclick="cancelarLimpiar()" class="flex-1 bg-white border border-red-200 hover:bg-gray-50 text-gray-600 text-xs py-1.5 rounded-md transition-colors font-semibold">Cancelar</button>
                <button onclick="ejecutarLimpiar()" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-xs py-1.5 rounded-md transition-colors font-bold shadow-sm">Sí, vaciar</button>
            </div>
        </div>

        <!-- Botón limpiar principal -->
        <button id="btn-limpiar-carrito" onclick="limpiarCarrito()"
                class="w-full text-sm text-gray-400 hover:text-red-500 transition-colors py-1 block">
            <i class="fas fa-trash-alt mr-1"></i> Vaciar carrito
        </button>

        <!-- Botón checkout -->
        <a href="<?= BASE_URL ?>checkout"
           class="block w-full text-center bg-principal hover:bg-secundario text-white font-bold
                  py-3 rounded-full transition-all hover:-translate-y-0.5 shadow hover:shadow-lg">
            Proceder al Pago <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </footer>

</aside>
