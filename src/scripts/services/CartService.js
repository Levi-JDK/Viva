/**
 * CartService.js - Service Layer
 * Responsible ONLY for HTTP requests to the cart API.
 * No DOM manipulation or UI logic here.
 */
export class CartService {
    static async request(accion, id_producto = null, cantidad = null) {
        const url = (window.BASE_URL || '/') + 'api/carrito';
        
        try {
            const respuesta = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ accion, id_producto, cantidad })
            });

            if (respuesta.status === 401 || respuesta.status === 403) {
                return { exito: false, mensaje: 'No autorizado', status: respuesta.status };
            }

            if (!respuesta.ok) throw new Error('Error de red: ' + respuesta.status);
            return await respuesta.json();
        } catch (error) {
            console.error('[CartService] Error:', error);
            throw error;
        }
    }

    static getCart() { return this.request('obtener'); }
    static addItem(id, cantidad) { return this.request('agregar', id, cantidad); }
    static removeItem(id) { return this.request('eliminar', id); }
    static updateItem(id, cantidad) { return this.request('actualizar', id, cantidad); }
    static clearCart() { return this.request('limpiar'); }
}
