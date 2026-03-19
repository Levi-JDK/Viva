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

        // Si estamos en la vista de perfil
        if (window.location.pathname.includes('perfil')) {
            this.renderFavoritesDashboard();
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
                    this.renderFavoritesDashboard();
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
            console.error('[FavoritesController] Error rendering dashboard:', error);
        }
    }

    renderFavoritesList(favoritos, contenedor) {
        contenedor.innerHTML = '';
        const baseUrl = window.BASE_URL || '/';

        favoritos.forEach(fav => {
            const precioOptions = { style: 'currency', currency: 'COP', minimumFractionDigits: 0 };
            let precioFormat = new Intl.NumberFormat('es-CO', precioOptions).format(fav.precio_producto);
            if (!precioFormat.includes('$')) precioFormat = '$' + precioFormat;

            const imgUrl = fav.primera_imagen ? baseUrl + fav.primera_imagen : baseUrl + 'images/default_product.jpg';

            const cardHTML = `
                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all group relative bg-white flex flex-col h-full">
                    <a href="${baseUrl}producto?id=${fav.id_producto}" class="block relative flex-shrink-0 h-48 overflow-hidden bg-gray-50">
                        <img src="${imgUrl}" alt="${this.escapeHtml(fav.nom_producto)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-all duration-300"></div>
                    </a>
                    
                    <button data-action="fav-toggle" data-id="${fav.id_producto}" class="btn-favorito absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur rounded-full flex items-center justify-center hover:bg-red-50 shadow-sm hover:shadow-md transition-all z-10 hover:scale-110" aria-label="Quitar de favoritos">
                        <i class="fa-solid fa-heart text-red-500 text-lg pointer-events-none"></i>
                    </button>
                    
                    <div class="p-4 flex flex-col flex-1">
                        <a href="${baseUrl}producto?id=${fav.id_producto}" class="block mb-1">
                            <h3 class="font-semibold text-gray-800 line-clamp-2 hover:text-naranja-artesanal transition-colors leading-tight">
                                ${this.escapeHtml(fav.nom_producto)}
                            </h3>
                        </a>
                        
                        <a href="${baseUrl}stand/${fav.id_productor || ''}" class="text-xs text-gray-500 mb-3 hover:text-tierra-oscuro transition-colors truncate block">
                            Vendido por <span class="font-medium">${this.escapeHtml(fav.nom_stand || 'Stand artesanal')}</span>
                        </a>
                        
                        <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                            <span class="text-lg font-bold text-tierra-oscuro truncate">${precioFormat}</span>
                            <button data-action="cart-add" data-id="${fav.id_producto}" data-qty="1" class="bg-naranja-artesanal text-white px-3 py-1.5 rounded-lg text-sm hover:bg-tierra-oscuro active:scale-95 transition-all flex items-center gap-1.5 font-medium shadow-sm flex-shrink-0">
                                <i class="fas fa-shopping-cart text-xs pointer-events-none"></i>
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
            `;
            contenedor.insertAdjacentHTML('beforeend', cardHTML);
        });
    }

    escapeHtml(texto) {
        if (texto === null || texto === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(texto);
        return div.innerHTML;
    }
}

export const favoritesController = new FavoritesController();
