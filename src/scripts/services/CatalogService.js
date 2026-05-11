export class CatalogService {
    static async getProducts(queryString) {
        const baseUrl = (typeof window.VIVACatalogo !== 'undefined' ? window.VIVACatalogo.baseUrl : (typeof BASE_URL !== 'undefined' ? BASE_URL : ''));
        const response = await fetch(`${baseUrl}/catalogo${queryString ? '?' + queryString : ''}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        if (!response.ok) throw new Error(response.statusText);

        const data = await response.json();
        if (!data.exito) {
            throw new Error(data.mensaje || 'Error en el servidor');
        }
        return data;
    }
}
