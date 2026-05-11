import { CatalogService } from '../services/CatalogService.js';

export class CatalogController {
    init() {
        this.inputSearch = document.getElementById('filtro-buscar');
        this.inputMinPrice = document.getElementById('filtro-min-price');
        this.inputMaxPrice = document.getElementById('filtro-max-price');
        this.btnLimpiar = document.getElementById('btn-limpiar-filtros');
        this.contenedorProductos = document.getElementById('contenedor-productos');
        this.loaderProductos = document.getElementById('loader-productos');
        this.textoBusqueda = document.getElementById('texto-busqueda');

        // Solo ejecutar si estamos en la página de catálogo
        if (!this.contenedorProductos) return;

        this.filtrosActuales = typeof window.VIVACatalogo !== 'undefined' ? { ...window.VIVACatalogo.filtros } : { search: '', categoria: '', oficio: '', materia: '', min_price: '', max_price: '' };
        this.baseURL = typeof window.VIVACatalogo !== 'undefined' ? window.VIVACatalogo.baseUrl : (typeof BASE_URL !== 'undefined' ? BASE_URL : '');
        this.debounceTimer = null;

        this.bindEvents();
        const qsInicial = new URLSearchParams(window.location.search).toString();
        this.actualizarUI(qsInicial);
    }

    bindEvents() {
        if (this.inputSearch) {
            this.inputSearch.addEventListener('input', (e) => {
                this.filtrosActuales.search = e.target.value.trim();
                this.triggerUpdate();
            });
        }

        document.querySelectorAll('input[type="radio"][name^="filtro_"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const nombreFiltro = e.target.name.replace('filtro_', '');
                this.filtrosActuales[nombreFiltro] = e.target.value;
                this.triggerUpdate(100);
            });
        });

        const handlePriceInput = () => {
            this.filtrosActuales.min_price = this.inputMinPrice.value;
            this.filtrosActuales.max_price = this.inputMaxPrice.value;
            this.triggerUpdate(800);
        };

        if (this.inputMinPrice) this.inputMinPrice.addEventListener('input', handlePriceInput);
        if (this.inputMaxPrice) this.inputMaxPrice.addEventListener('input', handlePriceInput);

        if (this.btnLimpiar) {
            this.btnLimpiar.addEventListener('click', () => {
                this.filtrosActuales = { search: '', categoria: '', oficio: '', materia: '', min_price: '', max_price: '' };
                if (this.inputSearch) this.inputSearch.value = '';
                if (this.inputMinPrice) this.inputMinPrice.value = '';
                if (this.inputMaxPrice) this.inputMaxPrice.value = '';
                document.querySelectorAll('input[type="radio"][name^="filtro_"]')
                    .forEach(r => { r.checked = false; });
                this.triggerUpdate(10);
            });
        }
    }

    triggerUpdate(delay = 500) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => this.actualizarCatalogo(), delay);
    }

    async actualizarCatalogo() {
        if (this.loaderProductos) {
            this.loaderProductos.classList.remove('hidden');
            this.loaderProductos.classList.add('flex');
        }
        if (this.contenedorProductos) this.contenedorProductos.style.opacity = '0.5';

        const params = new URLSearchParams();
        for (const [key, value] of Object.entries(this.filtrosActuales)) {
            if (value !== null && value !== '') {
                let paramKey = key;
                if (key === 'search') paramKey = 'q';
                if (key === 'categoria') paramKey = 'cat';
                params.append(paramKey, value);
            }
        }

        const queryString = params.toString();
        const nuevaURL = this.baseURL + 'catalogo' + (queryString ? '?' + queryString : '');
        window.history.pushState({ path: nuevaURL }, '', nuevaURL);

        this.actualizarUI(queryString);

        try {
            const resultado = await CatalogService.getProducts(queryString);
            
            if (resultado.total === 0) {
                this.contenedorProductos.innerHTML = this.renderVacio();
            } else {
                this.contenedorProductos.innerHTML = resultado.data.map(p => this.renderTarjetaProducto(p)).join('');
            }
        } catch (error) {
            if (typeof showToast !== 'undefined') showToast(error.message || 'Error al obtener productos', 'error');
            else console.error('Error al obtener productos:', error);
            if (this.contenedorProductos) this.contenedorProductos.innerHTML = this.renderError(error.message);
        } finally {
            if (this.loaderProductos) {
                this.loaderProductos.classList.add('hidden');
                this.loaderProductos.classList.remove('flex');
            }
            if (this.contenedorProductos) this.contenedorProductos.style.opacity = '1';
        }
    }

    actualizarUI(queryString) {
        if (this.btnLimpiar) this.btnLimpiar.classList.toggle('hidden', !queryString);
        if (this.textoBusqueda) {
            this.textoBusqueda.textContent = this.filtrosActuales.search
                ? `Resultados para "${this.filtrosActuales.search}"`
                : 'Todos los productos';
        }
    }

    escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str ?? ''));
        return div.innerHTML;
    }

    renderTarjetaProducto(p) {
        const precio = new Intl.NumberFormat('es-CO').format(p.precio_producto);
        const productImage = this.resolveAppUrl(p.primera_imagen || p.imagen_producto || 'images/default_product.jpg');
        const standImage = this.resolveAppUrl(p.img_stand || 'images/default.webp');
        const productUrl = this.resolveAppUrl(p.url_producto || `producto?id=${p.id_producto}`);
        return `
            <a href="${productUrl}"
               class="product-card bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl
                      transition-all duration-300 flex flex-col group h-full">
                <div class="h-64 bg-gradient-to-br from-tierra-claro to-beige-suave relative overflow-hidden">
                    <img src="${productImage}"
                         alt="${this.escapeHtml(p.nom_producto)}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         onerror="this.src='${this.resolveAppUrl('images/default_product.jpg')}'">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
                </div>
                <div class="p-5 flex-1 flex flex-col">
                    <h3 class="font-bold text-lg text-tierra-oscuro mb-2 line-clamp-2
                               group-hover:text-naranja-artesanal transition-colors">
                        ${this.escapeHtml(p.nom_producto)}
                    </h3>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-tierra-claro">
                            <img src="${standImage}"
                                 alt="${this.escapeHtml(p.nom_stand)}"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='${this.resolveAppUrl('images/default.jpg')}'">
                        </div>
                        <span class="text-sm text-gray-600 truncate">${this.escapeHtml(p.nom_stand)}</span>
                    </div>
                    <div class="flex-1"></div>
                    <div class="mt-auto pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-tierra-oscuro">
                                $${precio}
                            </span>
                            <button class="bg-naranja-artesanal text-white px-4 py-2 rounded-lg
                                           text-sm font-medium hover:bg-tierra-oscuro transition-colors" data-action="cart-add" data-id="${p.id_producto}" data-qty="1" data-name="${this.escapeHtml(p.nom_producto)}" data-price="${Number(p.precio_producto || 0)}" data-image="${productImage}" onclick="event.preventDefault();">
                                <i class="fas fa-shopping-cart mr-1"></i>Comprar
                            </button>
                        </div>
                    </div>
                </div>
            </a>
        `;
    }

    resolveAppUrl(path = '') {
        if (!path) {
            return this.baseURL || '/';
        }

        if (/^https?:\/\//i.test(path)) {
            return path;
        }

        if (typeof window.buildAppUrl === 'function') {
            return window.buildAppUrl(path);
        }

        const normalizedBase = String(this.baseURL || '').replace(/\/+$/, '');
        return `${normalizedBase}/${String(path).replace(/^\/+/, '')}`;
    }

    renderVacio() {
        return `
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
    }

    renderError(mensaje = '') {
        return `
            <div class="col-span-1 sm:col-span-2 xl:col-span-3 p-6 bg-red-50
                        text-red-600 rounded-xl text-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                ${mensaje || 'Hubo un error de conexión al cargar los productos. Por favor intenta de nuevo.'}
            </div>
        `;
    }
}
export const catalogController = new CatalogController();
