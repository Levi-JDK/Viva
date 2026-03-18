'use strict';

let favoritosActivos = new Set();

// Cargar favoritos al inicio
document.addEventListener('DOMContentLoaded', () => {
    // Solo intentar si el usuario está logueado (variable inyectada por PHP en carrito.php)
    if (window.USER_IS_LOGGED_IN === true) {
        cargarFavoritos();
    }
});

async function cargarFavoritos() {
    const res = await fetch(BASE_URL + 'api/favoritos', {
        headers: { 'Accept': 'application/json' }
    });

    try {
        const res = await fetch(BASE_URL + 'api/favoritos', {
            headers: { 'Accept': 'application/json' }
        });

        // Si PHP devuelve 401 por AJAX, capturarlo sin romper
        if (res.status === 401 || res.status === 403) {
            console.warn('[Favoritos] Sesión caducada o no válida. Se ignoran favoritos.');
            return;
        }

        const data = await res.json();

        if (data.exito && data.favoritos) {
            favoritosActivos = new Set(data.favoritos.map(f => parseInt(f.id_producto)));
            sincronizarBotonesUI();
        }
    } catch (error) {
        console.error('[Favoritos] Error cargando lista:', error);
    }
}

// Sincronizar todos los botones de la página actual
function sincronizarBotonesUI() {
    const botones = document.querySelectorAll('.btn-favorito');
    botones.forEach(btn => {
        const idProd = parseInt(btn.dataset.idProducto);
        // Verificar si existe dataset.idProducto antes de tratar
        if (!isNaN(idProd)) {
            actualizarIcono(btn, favoritosActivos.has(idProd));
        }
    });
}

// Actualizar clases de FontAwesome para el corazón
function actualizarIcono(btn, esFavorito) {
    const icon = btn.querySelector('i');
    if (!icon) return;

    if (esFavorito) {
        icon.classList.remove('fa-regular', 'text-gray-400', 'text-gray-500');
        icon.classList.add('fa-solid', 'text-red-500');
    } else {
        icon.classList.remove('fa-solid', 'text-red-500');
        icon.classList.add('fa-regular', 'text-gray-400');
    }
}

// Modificar de manera optimista y enviar background request
async function toggleFavorito(id_producto, btnElement, eventObj) {
    if (eventObj) {
        eventObj.preventDefault();
        eventObj.stopPropagation();
    }

    // Si el usuario no está logueado, redirigir al login con la URL de regreso
    if (!window.USER_IS_LOGGED_IN) {
        window.location.href = window.LOGIN_URL + '?redirect=' + encodeURIComponent(window.location.href);
        return;
    }

    const esFavoritoActualmente = favoritosActivos.has(id_producto);
    const nuevaAccion = esFavoritoActualmente ? 'eliminar' : 'agregar';

    // UI Optimista
    if (nuevaAccion === 'agregar') {
        favoritosActivos.add(id_producto);
    } else {
        favoritosActivos.delete(id_producto);
    }
    actualizarIcono(btnElement, !esFavoritoActualmente);

    // Animación popup
    const icon = btnElement.querySelector('i');
    if (icon) {
        icon.classList.add('scale-125', 'transition-transform', 'duration-200');
        setTimeout(() => icon.classList.remove('scale-125'), 200);
    }

    try {
        const res = await fetch(BASE_URL + 'api/favoritos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ accion: nuevaAccion, id_producto: id_producto })
        });

        const data = await res.json();

        if (data.redirect) {
            // Revertir UI si no estaba logueado
            if (nuevaAccion === 'agregar') favoritosActivos.delete(id_producto);
            else favoritosActivos.add(id_producto);
            actualizarIcono(btnElement, esFavoritoActualmente);

            if (typeof showToast === 'function') showToast(data.mensaje, 'info');
            // setTimeout(() => { window.location.href = BASE_URL + 'login'; }, 1000);
            return;
        }

        if (data.exito) {
            if (typeof showToast === 'function') {
                showToast(nuevaAccion === 'agregar' ? 'Añadido a favoritos' : 'Eliminado de favoritos', 'info');
            }

            // Si estamos en perfil.php y nos quitamos un favorito, se debe quitar de la vista recargando la grid
            if (window.location.pathname.includes('perfil') && nuevaAccion === 'eliminar') {
                if (typeof window.cargarFavoritosDashboard === 'function') {
                    window.cargarFavoritosDashboard();
                }
            }
        } else {
            // Revertir UI por error del server
            if (nuevaAccion === 'agregar') favoritosActivos.delete(id_producto);
            else favoritosActivos.add(id_producto);
            actualizarIcono(btnElement, esFavoritoActualmente);
            if (typeof showToast === 'function') showToast(data.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error toggling favorito:', error);
        // Revertir UI por error de red
        if (nuevaAccion === 'agregar') favoritosActivos.delete(id_producto);
        else favoritosActivos.add(id_producto);
        actualizarIcono(btnElement, esFavoritoActualmente);

        if (typeof showToast === 'function') showToast('Error de conexión', 'error');
    }
}

window.toggleFavorito = toggleFavorito;

// Función para el dashboard (Perfil)
async function cargarFavoritosDashboard() {
    const grid = document.getElementById('favoritos-grid');
    const emptyState = document.getElementById('favoritos-vacio');

    if (!grid || !emptyState) return;

    try {
        const res = await fetch(BASE_URL + 'api/favoritos', {
            headers: { 'Accept': 'application/json' }
        });

        if (res.status === 401 || res.status === 403) return;

        const data = await res.json();

        if (data.exito && data.favoritos) {
            if (data.favoritos.length === 0) {
                grid.innerHTML = '';
                grid.classList.add('hidden');
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
                grid.classList.remove('hidden');
                renderizarFavoritos(data.favoritos, grid);

                // Actualizar nuestro Set global para que sincronice la UI general (opcional, pero buena idea)
                favoritosActivos = new Set(data.favoritos.map(f => parseInt(f.id_producto)));
                sincronizarBotonesUI(); // Para actualizar los botones de las cards recién inyectadas
            }
        }
    } catch (error) {
        console.error('Error cargando favoritos para el dashboard:', error);
    }
}

function renderizarFavoritos(favoritos, contenedor) {
    contenedor.innerHTML = '';

    favoritos.forEach(fav => {
        // Formatear precio (ej: 120000 -> $120.000)
        const precioOptions = { style: 'currency', currency: 'COP', minimumFractionDigits: 0 };
        let precioFormat = new Intl.NumberFormat('es-CO', precioOptions).format(fav.precio_producto);
        // Fallback por si Intl.NumberFormat no usa el símbolo $ local en todos los entornos
        if (!precioFormat.includes('$')) precioFormat = '$' + precioFormat;

        const imgUrl = fav.primera_imagen ? BASE_URL + fav.primera_imagen : BASE_URL + 'images/default_product.jpg';

        const cardHTML = `
            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all group relative bg-white flex flex-col h-full">
                <!-- Imagen -->
                <a href="${BASE_URL}producto?id=${fav.id_producto}" class="block relative flex-shrink-0 h-48 overflow-hidden bg-gray-50">
                    <img src="${imgUrl}" alt="${fav.nom_producto}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-all duration-300"></div>
                </a>
                
                <!-- Botón eliminar de favorito -->
                <button onclick="toggleFavorito(${fav.id_producto}, this, event)"
                        data-id-producto="${fav.id_producto}"
                        class="btn-favorito absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur rounded-full flex items-center justify-center hover:bg-red-50 shadow-sm hover:shadow-md transition-all z-10 hover:scale-110"
                        aria-label="Quitar de favoritos">
                    <i class="fa-solid fa-heart text-red-500 text-lg"></i>
                </button>
                
                <!-- Info -->
                <div class="p-4 flex flex-col flex-1">
                    <a href="${BASE_URL}producto?id=${fav.id_producto}" class="block mb-1">
                        <h3 class="font-semibold text-gray-800 line-clamp-2 hover:text-naranja-artesanal transition-colors leading-tight">
                            ${fav.nom_producto}
                        </h3>
                    </a>
                    
                    <a href="${BASE_URL}stand/${fav.id_productor || ''}" class="text-xs text-gray-500 mb-3 hover:text-tierra-oscuro transition-colors truncate block">
                        Vendido por <span class="font-medium">${fav.nom_stand || 'Stand artesanal'}</span>
                    </a>
                    
                    <!-- Footer card -->
                    <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between gap-2">
                        <span class="text-lg font-bold text-tierra-oscuro truncate">${precioFormat}</span>
                        <button onclick="agregarAlCarrito(${fav.id_producto}, 1, this)" class="bg-naranja-artesanal text-white px-3 py-1.5 rounded-lg text-sm hover:bg-tierra-oscuro active:scale-95 transition-all flex items-center gap-1.5 font-medium shadow-sm flex-shrink-0">
                            <i class="fas fa-shopping-cart text-xs"></i>
                            Agregar
                        </button>
                    </div>
                </div>
            </div>
        `;
        contenedor.insertAdjacentHTML('beforeend', cardHTML);
    });
}

window.cargarFavoritosDashboard = cargarFavoritosDashboard;

document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('perfil')) {
        cargarFavoritosDashboard();
    }
});
