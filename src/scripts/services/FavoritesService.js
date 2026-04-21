/**
 * FavoritesService.js - Service Layer
 * Network requests for favorites. No UI interactions.
 */
export class FavoritesService {
    static get baseUrl() {
        return (window.BASE_URL || '/') + 'favoritos';
    }

    static async getFavorites() {
        try {
            const res = await fetch(this.baseUrl, {
                headers: { 'Accept': 'application/json' }
            });

            if (res.status === 401 || res.status === 403) {
                return { exito: false, mensaje: 'No autorizado', status: res.status };
            }

            return await res.json();
        } catch (error) {
            console.error('[FavoritesService] Error fetching favorites:', error);
            throw error;
        }
    }

    static async toggleFavorite(accion, id_producto) {
        try {
            const res = await fetch(this.baseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ accion, id_producto })
            });
            
            return await res.json();
        } catch (error) {
            console.error('[FavoritesService] Error toggling favorite:', error);
            throw error;
        }
    }
}
