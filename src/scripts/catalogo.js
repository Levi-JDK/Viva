document.addEventListener('DOMContentLoaded', () => {
    // 1. Elementos del DOM
    const inputSearch = document.getElementById('filtro-buscar');
    const inputMinPrice = document.getElementById('filtro-min-price');
    const inputMaxPrice = document.getElementById('filtro-max-price');
    const btnLimpiar = document.getElementById('btn-limpiar-filtros');
    const contenedorProductos = document.getElementById('contenedor-productos');
    const loaderProductos = document.getElementById('loader-productos');
    const textoBusqueda = document.getElementById('texto-busqueda');

    // 2. Estado global de filtros (inyectado desde PHP en window.VIVACatalogo.filtros)
    let filtrosActuales = { ...window.VIVACatalogo.filtros };
    const baseURL = window.VIVACatalogo.baseUrl;

    let debounceTimer;

    // =========================================================
    // 3. TEMPLATE: Construye el HTML de UNA tarjeta de producto
    //    a partir de un objeto JSON limpio devuelto por la API.
    // =========================================================
    const renderTarjetaProducto = (p) => {
        const precio = new Intl.NumberFormat('es-CO').format(p.precio_producto);

        return `
            <a href="${p.url_producto}"
               class="product-card bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl
                      transition-all duration-300 flex flex-col group h-full">

                <!-- Imagen del producto -->
                <div class="h-64 bg-gradient-to-br from-tierra-claro to-beige-suave relative overflow-hidden">
                    <img src="${p.imagen_producto}"
                         alt="${escapeHtml(p.nom_producto)}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         onerror="this.src='${baseURL}images/default_product.jpg'">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
                </div>

                <!-- Información -->
                <div class="p-5 flex-1 flex flex-col">

                    <!-- Nombre -->
                    <h3 class="font-bold text-lg text-tierra-oscuro mb-2 line-clamp-2
                               group-hover:text-naranja-artesanal transition-colors">
                        ${escapeHtml(p.nom_producto)}
                    </h3>

                    <!-- Stand del productor -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-tierra-claro">
                            <img src="${p.img_stand}"
                                 alt="${escapeHtml(p.nom_stand)}"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='${baseURL}images/default.jpg'">
                        </div>
                        <span class="text-sm text-gray-600 truncate">${escapeHtml(p.nom_stand)}</span>
                    </div>

                    <div class="flex-1"></div>

                    <!-- Precio y botón de compra -->
                    <div class="mt-auto pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-tierra-oscuro">
                                $${precio}
                            </span>
                            <button class="bg-naranja-artesanal text-white px-4 py-2 rounded-lg
                                           text-sm font-medium hover:bg-tierra-oscuro transition-colors">
                                <i class="fas fa-shopping-cart mr-1"></i>Comprar
                            </button>
                        </div>
                    </div>

                </div>
            </a>
        `;
    };

    // Template: estado vacío (sin resultados)
    const renderVacio = () => `
        <div class="col-span-1 sm:col-span-2 xl:col-span-3 flex flex-col items-center
                    justify-center bg-white rounded-2xl p-16 shadow-sm border border-gray-100 text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-5">
                <i class="fas fa-search text-gray-300 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-700 mb-2">Sin resultados</h3>
            <p class="text-gray-400 text-sm max-w-xs">
                Intenta eliminar algunos filtros o usar otros términos de búsqueda.
            </p>
        </div>
    `;

    // Template: error de red/servidor
    const renderError = (mensaje = '') => `
        <div class="col-span-1 sm:col-span-2 xl:col-span-3 p-6 bg-red-50
                    text-red-600 rounded-xl text-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            ${mensaje || 'Hubo un error de conexión al cargar los productos. Por favor intenta de nuevo.'}
        </div>
    `;

    // Escapa caracteres HTML para evitar XSS al insertar texto de la API
    const escapeHtml = (str) => {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str ?? ''));
        return div.innerHTML;
    };

    // =========================================================
    // 4. Función principal: llama a la API y actualiza el DOM
    // =========================================================
    const actualizarCatalogo = async () => {
        loaderProductos.classList.remove('hidden');
        loaderProductos.classList.add('flex');
        contenedorProductos.style.opacity = '0.5';

        const params = new URLSearchParams();
        for (const [key, value] of Object.entries(filtrosActuales)) {
            if (value !== null && value !== '') {
                let paramKey = key;
                if (key === 'search') paramKey = 'q';
                if (key === 'categoria') paramKey = 'cat';
                params.append(paramKey, value);
            }
        }

        const queryString = params.toString();

        // Actualizar URL sin recargar la página
        const nuevaURL = baseURL + 'catalogo' + (queryString ? '?' + queryString : '');
        window.history.pushState({ path: nuevaURL }, '', nuevaURL);

        actualizarUI(queryString);

        try {
            const response = await fetch(
                `${baseURL}api/productos${queryString ? '?' + queryString : ''}`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                }
            );

            // La API ahora devuelve JSON — lo parseamos
            const resultado = await response.json();

            if (!response.ok || !resultado.success) {
                throw new Error(resultado.message || 'Error en el servidor');
            }

            if (resultado.total === 0) {
                contenedorProductos.innerHTML = renderVacio();
            } else {
                // Construir el HTML de cada tarjeta y unirlas
                contenedorProductos.innerHTML = resultado.data
                    .map(renderTarjetaProducto)
                    .join('');
            }

        } catch (error) {
            console.error('Error al obtener productos:', error);
            contenedorProductos.innerHTML = renderError(error.message);
        } finally {
            loaderProductos.classList.add('hidden');
            loaderProductos.classList.remove('flex');
            contenedorProductos.style.opacity = '1';
        }
    };

    // =========================================================
    // 5. UI auxiliar y event listeners
    // =========================================================
    const actualizarUI = (queryString) => {
        btnLimpiar.classList.toggle('hidden', !queryString);
        textoBusqueda.textContent = filtrosActuales.search
            ? `Resultados para "${filtrosActuales.search}"`
            : 'Todos los productos';
    };

    const triggerUpdate = (delay = 500) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(actualizarCatalogo, delay);
    };

    if (inputSearch) {
        inputSearch.addEventListener('input', (e) => {
            filtrosActuales.search = e.target.value.trim();
            triggerUpdate();
        });
    }

    document.querySelectorAll('input[type="radio"][name^="filtro_"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const nombreFiltro = e.target.name.replace('filtro_', '');
            filtrosActuales[nombreFiltro] = e.target.value;
            triggerUpdate(100);
        });
    });

    const handlePriceInput = () => {
        filtrosActuales.min_price = inputMinPrice.value;
        filtrosActuales.max_price = inputMaxPrice.value;
        triggerUpdate(800);
    };

    if (inputMinPrice) inputMinPrice.addEventListener('input', handlePriceInput);
    if (inputMaxPrice) inputMaxPrice.addEventListener('input', handlePriceInput);

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            filtrosActuales = { search: '', categoria: '', oficio: '', materia: '', min_price: '', max_price: '' };
            if (inputSearch) inputSearch.value = '';
            if (inputMinPrice) inputMinPrice.value = '';
            if (inputMaxPrice) inputMaxPrice.value = '';
            document.querySelectorAll('input[type="radio"][name^="filtro_"]')
                .forEach(r => { r.checked = false; });
            triggerUpdate(10);
        });
    }

    // Inicializar UI con filtros actuales (sin lanzar fetch)
    const qsInicial = new URLSearchParams(window.location.search).toString();
    actualizarUI(qsInicial);
});
