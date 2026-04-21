export class CatalogService {
    static async getProducts(queryString) {
        const baseUrl = (typeof window.VIVACatalogo !== 'undefined' ? window.VIVACatalogo.baseUrl : (typeof BASE_URL !== 'undefined' ? BASE_URL : ''));
        const response = await fetch(`${baseUrl}catalogo${queryString ? '?' + queryString : ''}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Error en el servidor');
        }
        return data;
    }
}