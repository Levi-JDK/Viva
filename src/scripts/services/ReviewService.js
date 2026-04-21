export class ReviewService {
    static async submitReview(idProducto, calificacion, texto) {
        const response = await fetch(`${BASE_URL}resenas`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_producto: idProducto, calificacion, texto })
        });
        return response.json();
    }
}