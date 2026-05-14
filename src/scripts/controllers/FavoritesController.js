import { FavoritesService } from '../services/FavoritesService.js';
import { favoritesStore } from '../domain/FavoritesStore.js';

/**
 * FavoritesController.js - UI/Controller Layer
 * Handles DOM manipulation and UI states for favorite products.
 */
class FavoritesController {
    async init() {
        if (window.USER_IS_LOGGED_IN === true) {
            try {
                const data = await FavoritesService.getFavorites();
                if (data && data.exito && data.favoritos) {
                    const ids = data.favoritos.map(f => f.id_producto);
                    favoritesStore.setFavorites(ids);
                    this.syncButtonsUI();
                }
            } catch (error) {
                // User not logged in or offline - no action
            }
        }

        // Si estamos en la vista de perfil y NO hay cards servidas por PHP
        if (window.location.pathname.includes('perfil')) {
            const grid = document.getElementById('favoritos-grid');
            if (grid && grid.children.length === 0) {
                this.renderFavoritesDashboard();
            }
        }
    }

    syncButtonsUI() {
        const botones = document.querySelectorAll('.btn-favorito');
        botones.forEach(btn => {
            const idProd = parseInt(btn.dataset.id);
            if (!isNaN(idProd)) {
                this.updateIcon(btn, favoritesStore.has(idProd));
            }
        });
    }

    updateIcon(btn, esFavorito) {
        const icon = btn.querySelector('i');
        if (!icon) return;

        if (esFavorito) {
            icon.classList.remove('fa-regular', 'text-gray-400', 'text-gray-500');
            icon.classList.add('fa-solid', 'text-red-500');
        } else {
            icon.classList.remove('fa-solid', 'text-red-500');
            icon.classList.add('fa-regular', 'text-gray-400');
        }
    }

    async toggleFavorite(btn, eventObj) {
        if (eventObj) {
            eventObj.preventDefault();
            eventObj.stopPropagation();
        }

        if (!window.USER_IS_LOGGED_IN) {
            window.location.href = window.LOGIN_URL + '?redirect=' + encodeURIComponent(window.location.href);
            return;
        }

        const id_producto = parseInt(btn.dataset.id);
        if (isNaN(id_producto)) return;

        const esFavoritoActualmente = favoritesStore.has(id_producto);
        const nuevaAccion = esFavoritoActualmente ? 'eliminar' : 'agregar';

        // Optimistic UI
        if (nuevaAccion === 'agregar') {
            favoritesStore.add(id_producto);
        } else {
            favoritesStore.remove(id_producto);
        }
        this.updateIcon(btn, !esFavoritoActualmente);

        // Animation
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.add('scale-125', 'transition-transform', 'duration-200');
            setTimeout(() => icon.classList.remove('scale-125'), 200);
        }

        try {
            const data = await FavoritesService.toggleFavorite(nuevaAccion, id_producto);

            if (data.redirect) {
                // Revert
                if (nuevaAccion === 'agregar') favoritesStore.remove(id_producto);
                else favoritesStore.add(id_producto);
                this.updateIcon(btn, esFavoritoActualmente);
                if (window.showToast) window.showToast(data.mensaje, 'info');
                return;
            }

            if (data.exito) {
                if (window.showToast) {
                    window.showToast(nuevaAccion === 'agregar' ? 'Añadido a favoritos' : 'Eliminado de favoritos', 'info');
                }

                if (window.location.pathname.includes('perfil') && nuevaAccion === 'eliminar') {
                    // Quitar la card del DOM directamente en lugar de re-renderizar todo
                    const card = btn.closest('.product-card');
                    if (card) {
                        card.remove();
                        // Mostrar empty state si no quedan cards
                        const grid = document.getElementById('favoritos-grid');
                        const emptyState = document.getElementById('favoritos-vacio');
                        if (grid && emptyState && grid.children.length === 0) {
                            grid.classList.add('hidden');
                            emptyState.classList.remove('hidden');
                        }
                    }
                }
            } else {
                // Revert
                if (nuevaAccion === 'agregar') favoritesStore.remove(id_producto);
                else favoritesStore.add(id_producto);
                this.updateIcon(btn, esFavoritoActualmente);
                if (window.showToast) window.showToast(data.mensaje, 'error');
            }
        } catch (error) {
            console.error('[FavoritesController] Error toggling favorite:', error);
            // Revert
            if (nuevaAccion === 'agregar') favoritesStore.remove(id_producto);
            else favoritesStore.add(id_producto);
            this.updateIcon(btn, esFavoritoActualmente);
            if (window.showToast) window.showToast('Error de conexión', 'error');
        }
    }

    async renderFavoritesDashboard() {
        const grid = document.getElementById('favoritos-grid');
        const emptyState = document.getElementById('favoritos-vacio');

        if (!grid || !emptyState) return;

        try {
            const data = await FavoritesService.getFavorites();

            if (data && data.exito && data.favoritos) {
                if (data.favoritos.length === 0) {
                    grid.innerHTML = '';
                    grid.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden');
                    grid.classList.remove('hidden');
                    this.renderFavoritesList(data.favoritos, grid);

                    const ids = data.favoritos.map(f => f.id_producto);
                    favoritesStore.setFavorites(ids);
                    this.syncButtonsUI();
                }
            }
        } catch (error) {
            if (window.showToast) window.showToast(error.message || 'Error al cargar favoritos', 'error');
            else console.error('[FavoritesController] Error rendering dashboard:', error);
        }
    }

    renderFavoritesList(favoritos, contenedor) {
        contenedor.innerHTML = '';

        favoritos.forEach(fav => {
            const precioOptions = { style: 'currency', currency: 'COP', minimumFractionDigits: 0 };
            let precioFormat = new Intl.NumberFormat('es-CO', precioOptions).format(fav.precio_producto);
            if (!precioFormat.includes('$')) precioFormat = '$' + precioFormat;

            const imgUrl = this.resolveAppUrl(fav.primera_imagen || 'images/default_product.jpg');
            const productUrl = this.resolveAppUrl(`producto?id=${fav.id_producto}`);
            const standId = fav.id_stand || fav.id_productor || '';
            const standUrl = standId ? this.resolveAppUrl(`stand?id=${standId}`) : '#';

            const cardHTML = `
                <div class="product-card bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 flex flex-col group h-full relative">
                    <a href="${productUrl}" class="block relative group/img">
                        <div class="aspect-[4/3] bg-gradient-to-br from-tierra-claro to-beige-suave relative overflow-hidden">
                            <img src="${imgUrl}" alt="${this.escapeHtml(fav.nom_producto)}"
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.src='${this.resolveAppUrl('images/default_product.jpg')}'">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
                        </div>
                        
                        <button data-action="fav-toggle" data-id="${fav.id_producto}"
                                class="btn-favorito absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm hover:shadow-md transition-all z-10 hover:scale-110"
                                aria-label="Quitar de favoritos">
                            <i class="fa-solid fa-heart text-red-500 text-lg pointer-events-none"></i>
                        </button>
                    </a>

                    <div class="p-3 sm:p-5 flex-1 flex flex-col">
                        <a href="${productUrl}">
                            <h3 class="font-bold text-sm sm:text-lg text-tierra-oscuro mb-1 sm:mb-2 line-clamp-2 group-hover:text-naranja-artesanal transition-colors">
                                ${this.escapeHtml(fav.nom_producto)}
                            </h3>
                        </a>

                        <div class="flex items-center gap-1 sm:gap-2 mb-1 sm:mb-3">
                            <div class="w-6 h-6 sm:w-10 sm:h-10 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-tierra-claro bg-white">
                                <img src="${this.resolveAppUrl(fav.img_stand || 'images/default.webp')}"
                                     alt="${this.escapeHtml(fav.nom_stand || 'Stand')}"
                                     loading="lazy"
                                     class="w-full h-full object-contain">
                            </div>
                            <span class="text-xs sm:text-sm text-gray-600 truncate">
                                ${this.escapeHtml(fav.nom_stand || 'Stand artesanal')}
                            </span>
                        </div>

                        <div class="flex-1"></div>

                        <div class="mt-auto pt-2 sm:pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between gap-1 sm:gap-2">
                                <span class="text-base sm:text-2xl font-bold text-tierra-oscuro">${precioFormat}</span>
                                <button data-action="cart-add" data-id="${fav.id_producto}" data-qty="1"
                                        data-name="${this.escapeHtml(fav.nom_producto)}"
                                        data-price="${Number(fav.precio_producto || 0)}"
                                        data-image="${imgUrl}"
                                        class="btn-agregar-carrito bg-naranja-artesanal text-white px-2 py-1 sm:px-4 sm:py-2 rounded-lg text-xs sm:text-sm font-medium hover:bg-tierra-oscuro active:scale-95 transition-all flex items-center gap-1 sm:gap-1.5">
                                    <i class="fas fa-shopping-cart text-[10px] sm:text-xs"></i>
                                    <span class="hidden sm:inline">Agregar</span>
                                    <span class="sm:hidden"><i class="fas fa-plus"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            contenedor.insertAdjacentHTML('beforeend', cardHTML);
        });
    }

    resolveAppUrl(path = '') {
        if (!path) {
            return typeof window.buildAppUrl === 'function' ? window.buildAppUrl('') : (window.BASE_URL || '/');
        }

        if (/^https?:\/\//i.test(path)) {
            return path;
        }

        if (typeof window.buildAppUrl === 'function') {
            return window.buildAppUrl(path);
        }

        const baseUrl = String(window.BASE_URL || '').replace(/\/+$/, '');
        return `${baseUrl}/${String(path).replace(/^\/+/, '')}`;
    }

    escapeHtml(texto) {
        if (texto === null || texto === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(texto);
        return div.innerHTML;
    }
}

export const favoritesController = new FavoritesController();
