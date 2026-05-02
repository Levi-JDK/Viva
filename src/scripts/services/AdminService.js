export class AdminService {
    static async updateParameters(formData) {
        const response = await fetch(BASE_URL + '/admin?action=actualizar_parametros', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });
        return response.json();
    }

    static async deleteProduct(idProducto) {
        const response = await fetch(BASE_URL + '/api/delete_product', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_producto: idProducto })
        });
        return response.json();
    }

    static async saveProduct(formData, isEditMode) {
        const apiEndpoint = isEditMode ? '/api/update_product' : '/api/upload_product';
        const response = await fetch(BASE_URL + apiEndpoint, {
            method: 'POST',
            body: formData
        });
        return response.json();
    }

    static async updateStand(formData) {
        const response = await fetch(BASE_URL + '/mis_productos?view=stand', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        return response.json();
    }
}