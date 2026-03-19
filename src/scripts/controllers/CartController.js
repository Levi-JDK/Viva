import { CartService } from '../services/CartService.js';
import { cartStore } from '../domain/CartStore.js';

/**
 * CartController.js - UI/Controller Layer
 * Handles DOM manipulation, event responses, and UI updates for the cart.
 */
class CartController {
    async init() {
        if (window.USER_IS_LOGGED_IN === true) {
            try {
                const data = await CartService.getCart();
                if (data && data.exito) {
                    cartStore.setState(data.carrito, data.resumen);
                    this.updateBadge(data.resumen.total_items);
                }
            } catch (error) {
                // User not logged in or offline - no action
            }
        }
    }

    toggleCart() {
        const drawer = document.getElementById('carrito-drawer');
        const overlay = document.getElementById('carrito-overlay');
        if (!drawer || !overlay) return;

        const estaAbierto = !drawer.classList.contains('translate-x-full');

        if (estaAbierto) {
            drawer.classList.add('translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            this.loadCart();
        }
    }

    async loadCart() {
        try {
            const data = await CartService.getCart();
            if (data && data.exito) {
                cartStore.setState(data.carrito, data.resumen);
                this.renderCart();
            }
        } catch (error) {
            console.error('[CartController] Error loading cart:', error);
        }
    }

    async addItem(btn) {
        const id_producto = btn.dataset.id;
        // Si hay data-qty explícito úsalo, si no busca el input de cantidad cercano
        let cantidad = parseInt(btn.dataset.qty);
        if (!cantidad || isNaN(cantidad)) {
            const qtyInput = document.getElementById('qty-input')
                           || btn.closest('.flex')?.querySelector('input[type="number"]');
            cantidad = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
        }

        if (!window.USER_IS_LOGGED_IN) {
            window.location.href = window.LOGIN_URL + '?redirect=' + encodeURIComponent(window.location.href);
            return;
        }

        // Optimistic UI
        const iconoOriginal = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-xs"></i> ¡Listo!';
        btn.disabled = true;
        btn.classList.add('bg-green-500');
        btn.classList.remove('bg-naranja-artesanal', 'hover:bg-tierra-oscuro');

        this.animateFlyToCart(btn);

        setTimeout(() => {
            btn.innerHTML = iconoOriginal;
            btn.disabled = false;
            btn.classList.remove('bg-green-500');
            btn.classList.add('bg-naranja-artesanal', 'hover:bg-tierra-oscuro');
        }, 1200);

        try {
            const data = await CartService.addItem(id_producto, cantidad);

            if (data && data.exito) {
                cartStore.setState(data.carrito, data.resumen);
                this.updateBadge(data.resumen.total_items);

                const drawer = document.getElementById('carrito-drawer');
                if (drawer && drawer.classList.contains('translate-x-full')) {
                    this.toggleCart();
                } else {
                    this.renderCart();
                }
            } else {
                if (window.showToast) window.showToast(data.mensaje || 'Error', 'error');
                btn.disabled = false;
            }
        } catch (error) {
            console.error('[CartController] Error adding item:', error);
            if (window.showToast) window.showToast('Error al conectar con el servidor', 'error');
        }
    }

    async removeItem(btn) {
        const id_producto = btn.dataset.id;
        try {
            const data = await CartService.removeItem(id_producto);
            if (data && data.exito) {
                cartStore.setState(data.carrito, data.resumen);
                this.updateBadge(data.resumen.total_items);
                this.renderCart();
            } else {
                if (window.showToast) window.showToast(data.mensaje, 'error');
            }
        } catch (error) {
            console.error('[CartController] Error removing item:', error);
        }
    }

    async updateQuantity(btn) {
        const id_producto = btn.dataset.id;
        const nueva_cantidad = parseInt(btn.dataset.qty);
        
        if (nueva_cantidad < 1) return;

        try {
            const data = await CartService.updateItem(id_producto, nueva_cantidad);
            if (data && data.exito) {
                cartStore.setState(data.carrito, data.resumen);
                this.updateBadge(data.resumen.total_items);
                this.renderCart();
            } else {
                if (window.showToast) window.showToast(data.mensaje, 'error');
            }
        } catch (error) {
            console.error('[CartController] Error updating quantity:', error);
        }
    }

    clearCartPrompt() {
        document.getElementById('btn-limpiar-carrito')?.classList.add('hidden');
        document.getElementById('confirmacion-limpiar')?.classList.remove('hidden');
        document.getElementById('confirmacion-limpiar')?.classList.add('flex');
    }

    cancelClearCart() {
        const confirmContainer = document.getElementById('confirmacion-limpiar');
        if (confirmContainer) {
            confirmContainer.classList.add('hidden');
            confirmContainer.classList.remove('flex');
        }
        document.getElementById('btn-limpiar-carrito')?.classList.remove('hidden');
    }

    async executeClearCart() {
        this.cancelClearCart();
        try {
            const data = await CartService.clearCart();
            if (data && data.exito) {
                cartStore.setState([], { total_items: 0, total_precio: 0 });
                this.updateBadge(0);
                this.renderCart();
                if (window.showToast) window.showToast('Carrito vaciado', 'info');
            }
        } catch (error) {
            console.error('[CartController] Error clearing cart:', error);
        }
    }

    renderCart() {
        const contenedor = document.getElementById('carrito-items');
        const vacio = document.getElementById('carrito-vacio');
        const footer = document.getElementById('carrito-footer');
        const badgeDrawer = document.getElementById('carrito-badge-drawer');

        if (!contenedor || !vacio || !footer) return;

        const { items, resumen } = cartStore.getState();

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

        document.getElementById('carrito-total-items').textContent = resumen.total_items;
        document.getElementById('carrito-total-precio').textContent = this.formatPrice(resumen.total_precio);

        items.forEach(item => {
            const card = document.createElement('article');
            card.className = 'flex gap-3 bg-white border border-gray-100 rounded-xl p-3 shadow-sm hover:shadow-md transition-shadow';
            card.dataset.idProducto = item.id_producto;

            const baseUrl = window.BASE_URL || '/';

            card.innerHTML = `
                <a href="${baseUrl}producto?id=${item.id_producto}" class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gray-50 block">
                    <img src="${baseUrl}${this.escapeHtml(item.imagen)}" alt="${this.escapeHtml(item.nom_producto)}" class="w-full h-full object-cover" onerror="this.src='${baseUrl}images/default_product.jpg'">
                </a>
                <div class="flex-1 min-w-0 flex flex-col gap-1.5">
                    <div class="flex items-start justify-between gap-1">
                        <a href="${baseUrl}producto?id=${item.id_producto}" class="text-xs font-semibold text-oscuro leading-tight line-clamp-2 hover:text-principal transition-colors">
                            ${this.escapeHtml(item.nom_producto)}
                        </a>
                        <button data-action="cart-remove" data-id="${item.id_producto}" class="flex-shrink-0 text-gray-300 hover:text-red-400 transition-colors ml-1" aria-label="Eliminar">
                            <i class="fas fa-times text-xs pointer-events-none"></i>
                        </button>
                    </div>
                    <span class="text-xs text-gray-400">$${this.formatPrice(item.precio_unitario)} c/u</span>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <button data-action="cart-update" data-id="${item.id_producto}" data-qty="${item.cantidad - 1}" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-red-100 hover:text-red-500 flex items-center justify-center text-gray-500 transition-colors font-bold text-sm leading-none">
                                −
                            </button>
                            <span class="w-5 text-center text-sm font-bold text-oscuro tabular-nums">${item.cantidad}</span>
                            <button data-action="cart-update" data-id="${item.id_producto}" data-qty="${item.cantidad + 1}" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-green-100 hover:text-green-600 flex items-center justify-center text-gray-500 transition-colors font-bold text-sm leading-none">
                                +
                            </button>
                        </div>
                        <span class="text-sm font-bold text-naranja-artesanal tabular-nums">$${this.formatPrice(item.subtotal)}</span>
                    </div>
                </div>
            `;
            contenedor.appendChild(card);
        });
    }

    updateBadge(cantidad) {
        document.querySelectorAll('.navbar-carrito-badge').forEach(badge => {
            if (cantidad > 0) {
                badge.textContent = cantidad > 99 ? '99+' : cantidad;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });
    }

    animateFlyToCart(origen) {
        const cartIcon = document.querySelector('button[data-action="cart-toggle"] .fa-shopping-cart');
        if (!origen || !cartIcon) return;

        const origenRect = origen.getBoundingClientRect();
        const destinoRect = cartIcon.getBoundingClientRect();

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

        particula.getBoundingClientRect();

        const deltaX = destinoRect.left + destinoRect.width / 2 - origenRect.left - origenRect.width / 2;
        const deltaY = destinoRect.top + destinoRect.height / 2 - origenRect.top - origenRect.height / 2;

        particula.style.transform = `translate(${deltaX}px, ${deltaY}px) scale(0.3)`;
        particula.style.opacity = '0.2';

        setTimeout(() => {
            document.querySelectorAll('.navbar-carrito-badge').forEach(b => {
                b.classList.add('scale-125');
                setTimeout(() => b.classList.remove('scale-125'), 200);
            });
            particula.remove();
        }, 620);
    }

    formatPrice(valor) {
        return Number(valor).toLocaleString('es-CO');
    }

    escapeHtml(texto) {
        if (texto === null || texto === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(texto);
        return div.innerHTML;
    }
}

export const cartController = new CartController();
