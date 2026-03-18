/**
 * carrito.js — Módulo de Carrito de Compras
 *
 * Responsabilidades:
 *   - Abrir/cerrar el drawer del carrito
 *   - Comunicarse con /api/carrito mediante fetch
 *   - Renderizar mini-cards en tiempo real
 *   - Animación fly-to-cart al agregar productos
 *   - Actualizar el badge de cantidad en el navbar
 */

'use strict';

// ─────────────────────────────────────────────────
// ESTADO GLOBAL DEL CARRITO
// ─────────────────────────────────────────────────
let estadoCarrito = {
    items: [],
    resumen: { total_items: 0, total_precio: 0 }
};


// ─────────────────────────────────────────────────
// ABRIR / CERRAR DRAWER
// ─────────────────────────────────────────────────

function toggleCarrito() {
    const drawer = document.getElementById('carrito-drawer');
    const overlay = document.getElementById('carrito-overlay');
    const estaAbierto = !drawer.classList.contains('translate-x-full');

    if (estaAbierto) {
        drawer.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    } else {
        drawer.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        cargarCarrito();
    }
}
window.toggleCarrito = toggleCarrito;

// ─────────────────────────────────────────────────
// PETICIÓN GENÉRICA A LA API
// ─────────────────────────────────────────────────

async function peticionCarrito(accion, id_producto = null, cantidad = null) {
    const respuesta = await fetch(BASE_URL + 'api/carrito', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ accion, id_producto, cantidad })
    });

    // Si la sesión expiró
    if (respuesta.status === 401 || respuesta.status === 403) {
        if (typeof showToast === 'function') showToast('Sesión caducada. Inicia sesión de nuevo.', 'warning');
        if (window.LOGIN_URL) window.location.href = window.LOGIN_URL;
        return { exito: false, mensaje: 'No autorizado' };
    }

    if (!respuesta.ok) throw new Error('Error de red: ' + respuesta.status);
    return respuesta.json();
}


// ─────────────────────────────────────────────────
// ANIMACIÓN FLY-TO-CART
// ─────────────────────────────────────────────────

/**
 * Crea una partícula que "vuela" desde el botón hasta el ícono del carrito.
 * @param {HTMLElement} origen - El botón que disparó la acción
 */
function animarFlyToCart(origen) {
    // Buscar el ícono de carrito en el navbar
    const cartIcon = document.querySelector('button[onclick="toggleCarrito()"] .fa-shopping-cart');
    if (!origen || !cartIcon) return;

    const origenRect = origen.getBoundingClientRect();
    const destinoRect = cartIcon.getBoundingClientRect();

    // Crear la partícula
    const particula = document.createElement('div');
    particula.innerHTML = '<i class="fas fa-shopping-cart"></i>';
    particula.style.cssText = `
        position: fixed;
        z-index: 9999;
        left: ${origenRect.left + origenRect.width / 2}px;
        top:  ${origenRect.top + origenRect.height / 2}px;
        width: 32px;
        height: 32px;
        background: #b15b0a;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        pointer-events: none;
        transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        transform: scale(1);
        opacity: 1;
    `;
    document.body.appendChild(particula);

    // Forzar repaint antes de animar
    particula.getBoundingClientRect();

    // Animar hacia el ícono del carrito
    const deltaX = destinoRect.left + destinoRect.width / 2 - origenRect.left - origenRect.width / 2;
    const deltaY = destinoRect.top + destinoRect.height / 2 - origenRect.top - origenRect.height / 2;

    particula.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(0.3)`;
    particula.style.opacity = '0.2';

    // Animación del badge al recibir la partícula
    setTimeout(() => {
        animarBadge();
        particula.remove();
    }, 620);
}

/**
 * Hace un pequeño "pulso" en el badge del carrito para confirmar el agregado.
 */
function animarBadge() {
    document.querySelectorAll('.navbar-carrito-badge').forEach(badge => {
        badge.classList.add('scale-125');
        setTimeout(() => badge.classList.remove('scale-125'), 200);
    });
}

// ─────────────────────────────────────────────────
// CARGAR CARRITO
// ─────────────────────────────────────────────────

async function cargarCarrito() {
    try {
        const data = await peticionCarrito('obtener');
        if (data.exito) {
            estadoCarrito = { items: data.carrito, resumen: data.resumen };
            renderizarCarrito();
        }
    } catch (error) {
        console.error('[Carrito] Error al cargar:', error);
    }
}

// ─────────────────────────────────────────────────
// AGREGAR AL CARRITO
// ─────────────────────────────────────────────────

/**
 * Agrega un producto al carrito.
 * @param {number}      id_producto  - ID del producto
 * @param {number}      cantidad     - Cantidad a agregar
 * @param {HTMLElement} boton        - El botón que disparó la acción (para la animación)
 */
async function agregarAlCarrito(id_producto, cantidad = 1, boton = null) {
    // Si el usuario no está logueado, redirigir al login con la URL de regreso
    if (!window.USER_IS_LOGGED_IN) {
        window.location.href = window.LOGIN_URL + '?redirect=' + encodeURIComponent(window.location.href);
        return;
    }

    // Animación inmediata (optimistic UI)
    if (boton) {
        const iconoOriginal = boton.innerHTML;
        boton.innerHTML = '<i class="fas fa-check text-xs"></i> ¡Listo!';
        boton.disabled = true;
        boton.classList.add('bg-green-500');
        boton.classList.remove('bg-naranja-artesanal', 'hover:bg-tierra-oscuro');

        // Animación fly-to-cart
        animarFlyToCart(boton);

        setTimeout(() => {
            boton.innerHTML = iconoOriginal;
            boton.disabled = false;
            boton.classList.remove('bg-green-500');
            boton.classList.add('bg-naranja-artesanal', 'hover:bg-tierra-oscuro');
        }, 1200);
    }

    try {
        const data = await peticionCarrito('agregar', id_producto, cantidad);

        if (data.exito) {
            estadoCarrito = { items: data.carrito, resumen: data.resumen };
            actualizarBadge(data.resumen.total_items);

            // Abrir drawer si está cerrado
            const drawer = document.getElementById('carrito-drawer');
            if (drawer.classList.contains('translate-x-full')) {
                toggleCarrito();
            } else {
                renderizarCarrito();
            }
        } else {
            showToast(data.mensaje, 'error');
            // Revertir el botón si falla
            if (boton) boton.disabled = false;
        }
    } catch (error) {
        console.error('[Carrito] Error al agregar:', error);
        showToast('Error al conectar con el servidor', 'error');
    }
}
window.agregarAlCarrito = agregarAlCarrito;

// ─────────────────────────────────────────────────
// ELIMINAR ÍTEM DEL CARRITO
// ─────────────────────────────────────────────────

async function eliminarDelCarrito(id_producto) {
    try {
        const data = await peticionCarrito('eliminar', id_producto);
        if (data.exito) {
            estadoCarrito = { items: data.carrito, resumen: data.resumen };
            actualizarBadge(data.resumen.total_items);
            renderizarCarrito();
        } else {
            showToast(data.mensaje, 'error');
        }
    } catch (error) {
        console.error('[Carrito] Error al eliminar:', error);
    }
}
window.eliminarDelCarrito = eliminarDelCarrito;

// ─────────────────────────────────────────────────
// ACTUALIZAR CANTIDAD
// ─────────────────────────────────────────────────

async function actualizarCantidad(id_producto, nueva_cantidad) {
    if (nueva_cantidad < 1) return; // No bajar de 1, usar el botón de eliminar para quitar

    try {
        const data = await peticionCarrito('actualizar', id_producto, nueva_cantidad);
        if (data.exito) {
            estadoCarrito = { items: data.carrito, resumen: data.resumen };
            actualizarBadge(data.resumen.total_items);
            renderizarCarrito();
        } else {
            showToast(data.mensaje, 'error');
        }
    } catch (error) {
        console.error('[Carrito] Error al actualizar:', error);
    }
}
window.actualizarCantidad = actualizarCantidad;

// ─────────────────────────────────────────────────
// LIMPIAR CARRITO
// ─────────────────────────────────────────────────

function limpiarCarrito() {
    document.getElementById('btn-limpiar-carrito').classList.add('hidden');
    document.getElementById('confirmacion-limpiar').classList.remove('hidden');
    document.getElementById('confirmacion-limpiar').classList.add('flex');
}
window.limpiarCarrito = limpiarCarrito;

function cancelarLimpiar() {
    const confirmContainer = document.getElementById('confirmacion-limpiar');
    if (confirmContainer) {
        confirmContainer.classList.add('hidden');
        confirmContainer.classList.remove('flex');
    }
    const btnLimpiar = document.getElementById('btn-limpiar-carrito');
    if (btnLimpiar) btnLimpiar.classList.remove('hidden');
}
window.cancelarLimpiar = cancelarLimpiar;

async function ejecutarLimpiar() {
    cancelarLimpiar();
    try {
        const data = await peticionCarrito('limpiar');
        if (data.exito) {
            estadoCarrito = { items: [], resumen: { total_items: 0, total_precio: 0 } };
            actualizarBadge(0);
            renderizarCarrito();
            showToast('Carrito vaciado', 'info');
        }
    } catch (error) {
        console.error('[Carrito] Error al limpiar:', error);
    }
}
window.ejecutarLimpiar = ejecutarLimpiar;

// ─────────────────────────────────────────────────
// RENDERIZAR MINI-CARDS EN EL DRAWER
// ─────────────────────────────────────────────────

function renderizarCarrito() {
    const contenedor = document.getElementById('carrito-items');
    const vacio = document.getElementById('carrito-vacio');
    const footer = document.getElementById('carrito-footer');
    const badgeDrawer = document.getElementById('carrito-badge-drawer');

    const items = estadoCarrito.items || [];
    const resumen = estadoCarrito.resumen || { total_items: 0, total_precio: 0 };

    // Limpiar tarjetas anteriores (mantener #carrito-vacio)
    Array.from(contenedor.children).forEach(hijo => {
        if (hijo.id !== 'carrito-vacio') hijo.remove();
    });

    if (items.length === 0) {
        vacio.classList.remove('hidden');
        footer.classList.add('hidden');
        if (badgeDrawer) badgeDrawer.classList.add('hidden');
        return;
    }

    vacio.classList.add('hidden');
    footer.classList.remove('hidden');
    if (badgeDrawer) {
        badgeDrawer.classList.remove('hidden');
        badgeDrawer.textContent = resumen.total_items;
    }

    // Actualizar resumen del footer
    document.getElementById('carrito-total-items').textContent = resumen.total_items;
    document.getElementById('carrito-total-precio').textContent = formatearPrecio(resumen.total_precio);

    // Renderizar mini-card por cada ítem
    items.forEach(item => {
        const card = document.createElement('article');
        card.className = 'flex gap-3 bg-white border border-gray-100 rounded-xl p-3 shadow-sm hover:shadow-md transition-shadow';
        card.dataset.idProducto = item.id_producto;

        card.innerHTML = `
            <!-- Imagen -->
            <a href="${BASE_URL}producto?id=${item.id_producto}"
               class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gray-50 block">
                <img src="${BASE_URL}${escaparHtml(item.imagen)}"
                     alt="${escaparHtml(item.nom_producto)}"
                     class="w-full h-full object-cover"
                     onerror="this.src='${BASE_URL}images/default_product.jpg'">
            </a>

            <!-- Info + controles -->
            <div class="flex-1 min-w-0 flex flex-col gap-1.5">

                <!-- Fila 1: Nombre + botón eliminar -->
                <div class="flex items-start justify-between gap-1">
                    <a href="${BASE_URL}producto?id=${item.id_producto}"
                       class="text-xs font-semibold text-oscuro leading-tight line-clamp-2 hover:text-principal transition-colors">
                        ${escaparHtml(item.nom_producto)}
                    </a>
                    <button onclick="eliminarDelCarrito(${item.id_producto})"
                            class="flex-shrink-0 text-gray-300 hover:text-red-400 transition-colors ml-1"
                            aria-label="Eliminar">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                <!-- Fila 2: Precio c/u (debajo del nombre) -->
                <span class="text-xs text-gray-400">$${formatearPrecio(item.precio_unitario)} c/u</span>

                <!-- Fila 3: Controles de cantidad | Subtotal -->
                <div class="flex items-center justify-between">
                    <!-- Barra de cantidad -->
                    <div class="flex items-center gap-1.5">
                        <button onclick="actualizarCantidad(${item.id_producto}, ${item.cantidad - 1})"
                                class="w-6 h-6 rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-500
                                       flex items-center justify-center text-gray-500 transition-colors font-bold text-sm leading-none">
                            −
                        </button>
                        <span class="w-5 text-center text-sm font-bold text-oscuro tabular-nums">
                            ${item.cantidad}
                        </span>
                        <button onclick="actualizarCantidad(${item.id_producto}, ${item.cantidad + 1})"
                                class="w-6 h-6 rounded-full bg-gray-100 hover:bg-green-100 hover:text-green-600
                                       flex items-center justify-center text-gray-500 transition-colors font-bold text-sm leading-none">
                            +
                        </button>
                    </div>

                    <!-- Subtotal -->
                    <span class="text-sm font-bold text-naranja-artesanal tabular-nums">
                        $${formatearPrecio(item.subtotal)}
                    </span>
                </div>

            </div>
        `;

        contenedor.appendChild(card);
    });
}

// ─────────────────────────────────────────────────
// BADGE DEL NAVBAR
// ─────────────────────────────────────────────────

function actualizarBadge(cantidad) {
    document.querySelectorAll('.navbar-carrito-badge').forEach(badge => {
        if (cantidad > 0) {
            badge.textContent = cantidad > 99 ? '99+' : cantidad;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });
}

// ─────────────────────────────────────────────────
// UTILIDADES
// ─────────────────────────────────────────────────

function formatearPrecio(valor) {
    return Number(valor).toLocaleString('es-CO');
}

function escaparHtml(texto) {
    if (texto === null || texto === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(texto);
    return div.innerHTML;
}

// ─────────────────────────────────────────────────
// INICIALIZACIÓN — Cargar badge al abrir la página
// ─────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    if (window.USER_IS_LOGGED_IN === true) {
        peticionCarrito('obtener')
            .then(data => {
                if (data.exito) {
                    estadoCarrito = { items: data.carrito, resumen: data.resumen };
                    actualizarBadge(data.resumen.total_items);
                }
            })
            .catch(() => {
                // Usuario no logueado o sin conexión — sin acción
            });
    }
});
