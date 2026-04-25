export class ReviewService {
    static async submitReview(idProducto, calificacion, texto) {
        const response = await fetch((window.BASE_URL || '') + '/api/resenas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_producto: idProducto, calificacion, texto })
        });
        return response.json();
    }
}