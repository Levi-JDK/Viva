import { CartService } from '../services/CartService.js';
import { cartStore } from '../domain/CartStore.js';

/**
 * CartController.js - UI/Controller Layer
 * Handles DOM manipulation, event responses, and UI updates for the cart.
 */
class CartController {
    constructor() {
        this.flushListenersBound = false;
        this.syncDebounceMs = 500;
        this.syncDebounceId = null;
    }

    async init() {
        if (window.USER_IS_LOGGED_IN === true) {
            this.bindFlushListeners();

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

            if (cartStore.hasPendingActions()) {
                this.renderCart();
            } else {
                this.loadCart();
            }
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

    addItem(btn) {
        const id_producto = btn.dataset.id;
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

        this.applyOptimisticAdd(btn, id_producto, cantidad);
        this.enqueuePendingAction('agregar', id_producto, cantidad);
        this.schedulePendingSync();

        const drawer = document.getElementById('carrito-drawer');
        if (drawer && drawer.classList.contains('translate-x-full')) {
            this.toggleCart();
        } else {
            this.renderCart();
        }
    }

    removeItem(btn) {
        const id_producto = btn.dataset.id;

        this.applyOptimisticRemove(id_producto);
        this.enqueuePendingAction('eliminar', id_producto, null);
        this.schedulePendingSync();
        this.renderCart();
    }

    updateQuantity(btn) {
        const id_producto = btn.dataset.id;
        const nueva_cantidad = parseInt(btn.dataset.qty);
        
        if (nueva_cantidad < 1) return;

        this.applyOptimisticUpdate(id_producto, nueva_cantidad);
        this.enqueuePendingAction('actualizar', id_producto, nueva_cantidad);
        this.schedulePendingSync();
        this.renderCart();
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

    executeClearCart() {
        this.cancelClearCart();

        this.applyOptimisticClear();
        this.enqueuePendingAction('limpiar', null, null);
        this.schedulePendingSync();
        this.renderCart();

        if (window.showToast) window.showToast('Carrito vaciado', 'info');
    }

    bindFlushListeners() {
        if (this.flushListenersBound) return;

        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                this.flushToPostgresOnClose();
            }
        });

        window.addEventListener('beforeunload', () => {
            this.flushToPostgresOnClose();
        });

        this.flushListenersBound = true;
    }

    schedulePendingSync() {
        if (!window.USER_IS_LOGGED_IN) {
            return;
        }

        if (this.syncDebounceId) {
            clearTimeout(this.syncDebounceId);
        }

        this.syncDebounceId = window.setTimeout(() => {
            this.syncDebounceId = null;
            this.flushPendingActions();
        }, this.syncDebounceMs);
    }

    async flushPendingActions() {
        if (!window.USER_IS_LOGGED_IN || !cartStore.hasPendingActions() || cartStore.getState().isFlushing) {
            return false;
        }

        const pendingActions = cartStore.getPendingActions();
        cartStore.setFlushing(true);

        try {
            await CartService.sendPendingActions(pendingActions);
            cartStore.clearPendingActions();
            cartStore.markSynced();
            return true;
        } catch (error) {
            console.error('[CartController] Debounced sync failed:', error);
            return false;
        } finally {
            cartStore.setFlushing(false);
        }
    }

    flushPendingActionsKeepalive() {
        if (!window.USER_IS_LOGGED_IN || !cartStore.hasPendingActions()) {
            return false;
        }

        const pendingActions = cartStore.getPendingActions();
        const result = CartService.sendPendingActionsKeepalive(pendingActions);

        if (!result.ok) {
            return false;
        }

        cartStore.clearPendingActions();
        cartStore.markSynced();

        if (result.promise) {
            result.promise.catch(error => {
                console.error('[CartController] Keepalive redis sync failed:', error);
            });
        }

        return true;
    }

    flushToPostgresOnClose() {
        if (!window.USER_IS_LOGGED_IN) {
            return false;
        }

        if (this.syncDebounceId) {
            clearTimeout(this.syncDebounceId);
            this.syncDebounceId = null;
        }

        const pendingActions = cartStore.getPendingActions();
        const result = CartService.flushToPostgresKeepalive(false, pendingActions);

        if (result.promise) {
            result.promise.catch(error => {
                console.error('[CartController] Keepalive postgres flush failed:', error);
            });
        }

        if (result.ok && pendingActions.length > 0) {
            cartStore.clearPendingActions();
            cartStore.markSynced();
        }

        return result.ok;
    }

    enqueuePendingAction(accion, id_producto, cantidad) {
        let action;

        if (accion === 'agregar') {
            action = CartService.addItem(Number(id_producto), Number(cantidad));
        } else if (accion === 'eliminar') {
            action = CartService.removeItem(Number(id_producto));
        } else if (accion === 'actualizar') {
            action = CartService.updateItem(Number(id_producto), Number(cantidad));
        } else {
            action = CartService.clearCart();
        }

        cartStore.enqueuePendingAction(action);
    }

    applyOptimisticAdd(btn, idProducto, cantidad) {
        const { items } = cartStore.getState();
        const nextItems = items.map(item => ({ ...item }));
        const existingItem = nextItems.find(item => Number(item.id_producto) === Number(idProducto));

        if (existingItem) {
            existingItem.cantidad += cantidad;
            existingItem.subtotal = Number(existingItem.precio_unitario || 0) * existingItem.cantidad;
        } else {
            nextItems.push(this.buildOptimisticItem(btn, idProducto, cantidad));
        }

        this.commitOptimisticItems(nextItems);
    }

    applyOptimisticRemove(idProducto) {
        const { items } = cartStore.getState();
        const nextItems = items
            .filter(item => Number(item.id_producto) !== Number(idProducto))
            .map(item => ({ ...item }));

        this.commitOptimisticItems(nextItems);
    }

    applyOptimisticUpdate(idProducto, cantidad) {
        const { items } = cartStore.getState();
        const nextItems = items.map(item => {
            if (Number(item.id_producto) !== Number(idProducto)) {
                return { ...item };
            }

            const precioUnitario = Number(item.precio_unitario || 0);

            return {
                ...item,
                cantidad,
                subtotal: precioUnitario * cantidad
            };
        });

        this.commitOptimisticItems(nextItems);
    }

    applyOptimisticClear() {
        this.commitOptimisticItems([]);
    }

    commitOptimisticItems(items) {
        const resumen = this.buildResumen(items);
        cartStore.setState(items, resumen);
        this.updateBadge(resumen.total_items);
    }

    buildResumen(items) {
        return items.reduce((acc, item) => {
            const cantidad = Number(item.cantidad || 0);
            const subtotal = Number(item.subtotal || 0);

            acc.total_items += cantidad;
            acc.total_precio += subtotal;

            return acc;
        }, { total_items: 0, total_precio: 0 });
    }

    buildOptimisticItem(btn, idProducto, cantidad) {
        const productCard = btn.closest('.product-card');
        const detailView = document.getElementById('mainImage');
        const nameFromCard = productCard?.querySelector('h3')?.textContent?.trim();
        const nameFromDetail = document.querySelector('h1')?.textContent?.trim();
        const priceText = productCard?.querySelector('.text-2xl.font-bold')?.textContent
            || document.querySelector('[data-product-price]')?.textContent
            || '';
        const imageSrc = productCard?.querySelector('img')?.getAttribute('src')
            || detailView?.getAttribute('src')
            || 'images/default_product.jpg';
        const precioUnitario = this.parsePrice(priceText);

        return {
            id_producto: Number(idProducto),
            nom_producto: nameFromCard || nameFromDetail || 'Producto agregado',
            precio_unitario: precioUnitario,
            cantidad,
            subtotal: precioUnitario * cantidad,
            imagen: this.normalizeImagePath(imageSrc)
        };
    }

    parsePrice(value) {
        const normalized = String(value || '').replace(/[^0-9]/g, '');
        return normalized ? Number(normalized) : 0;
    }

    normalizeImagePath(imagePath) {
        if (!imagePath) return 'images/default_product.jpg';

        const baseUrl = window.BASE_URL || '/';
        const absoluteBase = new URL(baseUrl, window.location.origin).href;

        if (imagePath.startsWith(absoluteBase)) {
            return imagePath.slice(absoluteBase.length);
        }

        if (imagePath.startsWith(window.location.origin)) {
            return imagePath.slice(window.location.origin.length).replace(/^\//, '');
        }

        return imagePath.replace(/^\//, '');
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
                    <img src="${baseUrl}${this.escapeHtml(item.imagen)}" alt="${this.escapeHtml(item.nom_producto)}" class="w-full h-full object-cover" data-fallback-src="${baseUrl}images/default_product.jpg">
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

            const image = card.querySelector('img[data-fallback-src]');
            image?.addEventListener('error', () => {
                image.src = image.dataset.fallbackSrc;
            }, { once: true });
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
