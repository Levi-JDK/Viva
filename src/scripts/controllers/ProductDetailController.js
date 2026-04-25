import { ReviewService } from '../services/ReviewService.js';

export class ProductDetailController {
    init() {
        this.initZoom();
        this.initRating();
    }

    initZoom() {
        const mainImage = document.getElementById('mainImage');
        const imageContainer = document.getElementById('imageContainer');

        if (mainImage && imageContainer) {
            imageContainer.addEventListener('mousemove', e => {
                const rect = imageContainer.getBoundingClientRect();
                mainImage.style.transformOrigin = `${((e.clientX - rect.left) / rect.width) * 100}% ${((e.clientY - rect.top) / rect.height) * 100}%`;
                mainImage.style.transform = 'scale(2.5)';
            });
            imageContainer.addEventListener('mouseleave', () => {
                mainImage.style.transformOrigin = 'center';
                mainImage.style.transform = 'scale(1)';
            });
        }
    }

    initRating() {
        const starContainer = document.getElementById('star-rating');
        this.calificacionInput = document.getElementById('calificacion_input');
        
        if (starContainer && this.calificacionInput) {
            const stars = starContainer.querySelectorAll('i');
            let currentRating = 0;

            const updateStars = (rating) => stars.forEach(s => {
                const isActive = parseInt(s.dataset.value) <= rating;
                s.classList.toggle('text-gray-800', isActive);
                s.classList.toggle('text-gray-300', !isActive);
            });

            stars.forEach(star => {
                star.addEventListener('mouseover', function () { updateStars(parseInt(this.dataset.value)); });
                star.addEventListener('click', (e) => {
                    const val = parseInt(e.currentTarget.dataset.value);
                    this.calificacionInput.value = currentRating = val;
                    e.currentTarget.classList.add('scale-125');
                    setTimeout(() => e.currentTarget.classList.remove('scale-125'), 150);
                    updateStars(currentRating);
                });
            });

            starContainer.addEventListener('mouseleave', () => updateStars(currentRating));
        }
    }

    async submitReview(e, formResena) {
        e.preventDefault();
        const id_producto = formResena.dataset.productoId;
        const calificacionInput = document.getElementById('calificacion_input');
        const calificacion = calificacionInput ? calificacionInput.value : formResena.calificacion?.value;
        const texto = formResena.texto.value;

        if (!calificacion) {
            if (typeof showToast !== 'undefined') showToast('Por favor selecciona una calificación de 1 a 5 estrellas.', 'error');
            return;
        }

        try {
            const data = await ReviewService.submitReview(id_producto, calificacion, texto);
            if (typeof showToast !== 'undefined') showToast(data.mensaje, data.exito ? 'success' : 'error');
            if (data.exito) setTimeout(() => window.location.reload(), 1500);
        } catch (err) {
            if (typeof showToast !== 'undefined') showToast('Error de conexión con el servidor', 'error');
        }
    }

    scrollToReviews() {
        document.getElementById('reviews-section')?.scrollIntoView({behavior: 'smooth'});
    }

    toggleReviewForm() {
        document.getElementById('review-form-container')?.classList.toggle('hidden');
    }

    hideReviewForm() {
        document.getElementById('review-form-container')?.classList.add('hidden');
    }
}
export const productDetailController = new ProductDetailController();