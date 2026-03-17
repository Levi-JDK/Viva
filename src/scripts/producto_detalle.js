// Efecto Zoom en Imagen Principal
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

// Interacción de Estrellas en Formulario de Reseñas
const starContainer = document.getElementById('star-rating');
const calificacionInput = document.getElementById('calificacion_input');

if (starContainer) {
    const stars = starContainer.querySelectorAll('i');
    let currentRating = 0;

    const updateStars = (rating) => stars.forEach(s => {
        const isActive = parseInt(s.dataset.value) <= rating;
        s.classList.toggle('text-gray-800', isActive);
        s.classList.toggle('text-gray-300', !isActive);
    });

    stars.forEach(star => {
        star.addEventListener('mouseover', function () { updateStars(parseInt(this.dataset.value)); });
        star.addEventListener('click', function () {
            calificacionInput.value = currentRating = parseInt(this.dataset.value);
            this.classList.add('scale-125');
            setTimeout(() => this.classList.remove('scale-125'), 150);
            updateStars(currentRating);
        });
    });

    starContainer.addEventListener('mouseleave', () => updateStars(currentRating));
}

// Enviar Reseña al Backend
async function enviarResena(e, id_producto) {
    e.preventDefault();
    const form = e.target;
    const calificacion = calificacionInput.value;
    const texto = form.texto.value;

    if (!calificacion) return showToast('Por favor selecciona una calificación de 1 a 5 estrellas.', 'error');

    try {
        const res = await fetch(`${BASE_URL}api/resenas`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_producto, calificacion, texto })
        });

        const data = await res.json();
        showToast(data.mensaje, data.exito ? 'success' : 'error');
        if (data.exito) setTimeout(() => window.location.reload(), 1500);
    } catch (err) {
        showToast('Error de conexión con el servidor', 'error');
    }
}
