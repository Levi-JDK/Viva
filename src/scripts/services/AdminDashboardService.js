/**
 * AdminDashboardService.js
 * Service Layer — HTTP requests ONLY for Admin Dashboard panels.
 * ZERO DOM knowledge.
 */
export class AdminDashboardService {

    static async fetchUsers() {
        const response = await fetch(`${BASE_URL}admin?ajax=1&accion=list_users&_=${Date.now()}`);
        if (!response.ok) throw new Error('Failed to fetch users');
        return response.json();
    }

    static async fetchProducts() {
        const response = await fetch(`${BASE_URL}admin?ajax=1&accion=list_products&_=${Date.now()}`);
        if (!response.ok) throw new Error('Failed to fetch products');
        return response.json();
    }

    static async toggleUser(idUser, isActive) {
        const response = await fetch(`${BASE_URL}admin`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=toggle_user&id_user=${idUser}&is_active=${isActive}`
        });
        if (!response.ok) throw new Error('Failed to toggle user');
        return response.json();
    }

    static async toggleProduct(idProducto, isActive) {
        const response = await fetch(`${BASE_URL}admin`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `accion=toggle_product&id_producto=${idProducto}&is_active=${isActive}`
        });
        if (!response.ok) throw new Error('Failed to toggle product');
        return response.json();
    }

    static async crudRead(entidad) {
        const response = await fetch(`${BASE_URL}admin?ajax=1&accion=read&entidad=${entidad}&_=${Date.now()}`);
        if (!response.ok) throw new Error('Failed to read entity');
        return response.json();
    }

    static async crudWrite(accion, entidad, datos) {
        const urlEncoded = new URLSearchParams();
        urlEncoded.append('accion', accion);
        urlEncoded.append('entidad', entidad);
        for (const [k, v] of Object.entries(datos)) {
            urlEncoded.append(k, v);
        }
        const response = await fetch(`${BASE_URL}admin`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: urlEncoded.toString()
        });
        if (!response.ok) throw new Error('CRUD operation failed');
        return response.json();
    }
}
