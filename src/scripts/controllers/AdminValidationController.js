import { adminValidationService } from '../services/AdminValidationService.js';
import { eventRouter } from '../utils/EventRouter.js';

class AdminValidationController {
    constructor() {
        this.status = 'pending_review';
        this.page = 1;
        this.limit = 20;
        this.pages = 1;
        this.products = [];
        this.loaded = false;
        this.initialized = false;
    }

    init() {
        if (this.initialized) return;
        this.initialized = true;

        eventRouter.register('admin-validation:list', (event, btn) => {
            event.preventDefault();
            this.changeStatus(btn.dataset.status || 'pending_review');
        });
        eventRouter.register('admin-validation:approve', (event, btn) => this.handleAction(event, btn, 'approve'));
        eventRouter.register('admin-validation:reject', (event, btn) => this.handleAction(event, btn, 'reject'));
        eventRouter.register('admin-validation:reprocess', (event, btn) => this.handleAction(event, btn, 'reprocess'));
        eventRouter.register('admin-validation:view-evidence', (event, btn) => this.showEvidence(event, btn));
        eventRouter.register('admin-validation:close-evidence', (event) => {
            event.preventDefault();
            this.closeEvidence();
        });
        eventRouter.register('admin-validation:page', (event, btn) => this.changePage(event, btn));
    }

    async onPanelShow() {
        if (!this.loaded) await this.load();
    }

    async changeStatus(status) {
        this.status = status;
        this.page = 1;
        this.updateTabs();
        await this.load();
    }

    async changePage(event, btn) {
        event.preventDefault();
        const direction = btn.dataset.direction;
        const nextPage = direction === 'next' ? this.page + 1 : this.page - 1;
        if (nextPage < 1 || nextPage > this.pages) return;
        this.page = nextPage;
        await this.load();
    }

    async load() {
        this.showLoader(true);
        try {
            const result = await adminValidationService.list({ status: this.status, page: this.page, limit: this.limit });
            if (!result.exito) {
                this.toast(result.mensaje || 'Error al cargar validaciones', 'error');
                return;
            }

            const data = result.data || {};
            this.products = data.productos || data.items || [];
            this.pages = Number(data.pages || 1);
            this.render(this.products);
            this.renderPagination(data);
            this.loaded = true;
        } catch (error) {
            console.error('Admin validation list error:', error);
            this.toast(error.message || 'Error de conexión', 'error');
        } finally {
            this.showLoader(false);
        }
    }

    render(products) {
        const tbody = document.getElementById('admin-validation-tbody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!products.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-10 text-center text-slate-500 italic">No hay productos para este estado.</td></tr>';
            return;
        }

        products.forEach((product) => tbody.appendChild(this.createRow(product)));
    }

    createRow(product) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-white/[0.02] transition-colors';

        const id = this.escape(product.id_producto);
        const payload = this.escapeAttr(JSON.stringify(product));
        const price = Number(product.precio_producto || 0).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
        const reason = product.reason || 'Sin razón registrada';
        const date = product.validated_at || product.created_at || '—';

        tr.innerHTML = `
            <td class="px-4 py-3 font-mono text-slate-500" data-label="ID">${id}</td>
            <td class="px-4 py-3 font-bold text-white" data-label="Nombre">${this.escape(product.nom_producto || '—')}</td>
            <td class="px-4 py-3 text-slate-400" data-label="Productor">${this.escape(product.nombre_productor || product.nom_stand || product.id_productor || '—')}</td>
            <td class="px-4 py-3 font-mono text-emerald-400 whitespace-nowrap" data-label="Precio">${this.escape(price)}</td>
            <td class="px-4 py-3 font-mono text-center" data-label="Stock">${this.escape(product.stock_productor || 0)}</td>
            <td class="px-4 py-3" data-label="Decisión IA">${this.badge(product.validation_status || product.decision)}</td>
            <td class="px-4 py-3 text-xs text-slate-500 font-mono whitespace-nowrap" data-label="Fecha">${this.escape(String(date).substring(0, 10))}</td>
            <td class="px-4 py-3 text-right" data-label="Acciones">
                <div class="flex items-center justify-end gap-1.5">
                    <button data-action="admin-validation:view-evidence" data-product='${payload}' class="h-8 px-3 rounded-lg bg-sky-500/10 hover:bg-sky-500 text-sky-400 hover:text-white transition-colors text-[10px] font-bold uppercase tracking-widest">Ev</button>
                    <button data-action="admin-validation:approve" data-product-id="${id}" class="w-8 h-8 rounded-lg bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-white transition-colors flex items-center justify-center shrink-0" title="Aprobar"><i class="fas fa-check text-xs pointer-events-none"></i></button>
                    <button data-action="admin-validation:reject" data-product-id="${id}" class="w-8 h-8 rounded-lg bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white transition-colors flex items-center justify-center shrink-0" title="Rechazar"><i class="fas fa-times text-xs pointer-events-none"></i></button>
                    <button data-action="admin-validation:reprocess" data-product-id="${id}" class="w-8 h-8 rounded-lg bg-amber-500/10 hover:bg-amber-500 text-amber-400 hover:text-slate-900 transition-colors flex items-center justify-center shrink-0" title="Reprocesar"><i class="fas fa-rotate text-xs pointer-events-none"></i></button>
                </div>
            </td>`;

        return tr;
    }

    async handleAction(event, btn, action) {
        event.preventDefault();
        const productId = btn.dataset.productId;
        if (!productId) return;

        btn.disabled = true;
        try {
            const result = action === 'approve'
                ? await adminValidationService.approve(productId)
                : action === 'reject'
                    ? await adminValidationService.reject(productId)
                    : await adminValidationService.reprocess(productId);

            if (result.exito) {
                this.toast(result.mensaje || 'Acción realizada correctamente', 'success');
                await this.load();
            } else {
                this.toast(result.mensaje || 'No se pudo realizar la acción', 'error');
            }
        } catch (error) {
            console.error('Admin validation action error:', error);
            this.toast(error.message || 'Error de conexión', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    showEvidence(event, btn) {
        event.preventDefault();
        const product = this.parseProduct(btn.dataset.product);
        if (!product) return;

        const modal = document.getElementById('admin-validation-evidence-modal');
        const content = document.getElementById('admin-validation-evidence-content');
        const title = document.getElementById('admin-validation-modal-title');
        const subtitle = document.getElementById('admin-validation-modal-subtitle');
        if (!modal || !content) return;

        if (title) title.textContent = product.nom_producto || 'Producto';
        if (subtitle) subtitle.textContent = `ID ${product.id_producto} · ${product.validation_status || 'sin estado'}`;
        content.innerHTML = this.renderEvidence(product);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.scale-95')?.classList.remove('scale-95');
        }, 10);
    }

    closeEvidence() {
        const modal = document.getElementById('admin-validation-evidence-modal');
        if (!modal) return;
        const inner = modal.querySelector('.max-w-3xl');
        modal.classList.add('opacity-0');
        inner?.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 250);
    }

    renderEvidence(product) {
        const rows = [
            ['Decisión', product.decision || '—'],
            ['Razón', product.reason || '—'],
            ['Proveedor', product.provider_used || '—'],
            ['Fallback', this.formatBool(product.fallback_used)],
            ['Plagio', `${product.plagiarism_status || '—'} · score ${product.plagiarism_score ?? '—'} · método ${product.plagiarism_method || '—'}`],
            ['Texto/Imagen', `${product.text_image_status || '—'} · score ${product.text_image_score ?? '—'}`],
            ['Artesanal', `${product.artisan_status || '—'} · score ${product.artisan_score ?? '—'}`],
        ];

        const matchedUrl = product.matched_image_url && product.matched_image_url !== 'N/A' ? product.matched_image_url : null;
        const productUrl = product.url_imagen && product.url_imagen !== 'N/A' ? product.url_imagen : null;
        const isPerceptualPlagiarism = product.plagiarism_status === 'posible'
            && (product.plagiarism_method === 'hash_perceptual' || product.plagiarism_method === 'hash_diferencia');

        const hasPhotos = productUrl || matchedUrl;

        return `
            ${hasPhotos ? `
            <div class="bg-black/20 border border-white/5 rounded-2xl p-4 sm:p-5 mb-4">
                <p class="text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-4 flex items-center gap-2">
                    <i class="fas fa-camera text-rose-400"></i> 
                    ${isPerceptualPlagiarism ? 'Comparación de imágenes' : 'Imagen del producto'}
                </p>
                <div class="grid grid-cols-1 ${matchedUrl ? 'sm:grid-cols-2' : ''} gap-4">
                    ${productUrl ? `
                    <div class="${matchedUrl ? 'border border-rose-500/20 bg-rose-500/5' : 'border border-white/5 bg-white/5'} rounded-xl p-2 sm:p-3">
                        <p class="text-[9px] font-bold tracking-widest uppercase text-slate-500 mb-2 ${matchedUrl ? 'text-rose-400' : ''}">${matchedUrl ? 'Producto actual' : 'Foto principal'}</p>
                        <img src="${this.escape(productUrl)}" alt="Producto actual" class="w-full h-48 sm:h-64 object-contain rounded-lg bg-black/40" loading="lazy" onerror="this.parentElement.innerHTML='<div class=\\'flex items-center justify-center h-48 text-slate-600 text-sm\\'><i class=\\'fas fa-image mr-2\\'></i>Sin imagen</div>'">
                    </div>
                    ` : ''}
                    ${matchedUrl ? `
                    <div class="border border-amber-500/20 bg-amber-500/5 rounded-xl p-2 sm:p-3">
                        <p class="text-[9px] font-bold tracking-widest uppercase text-amber-400 mb-2">Producto similar (ID: ${this.escape(product.matched_product_id || '?')})</p>
                        <img src="${this.escape(matchedUrl)}" alt="Producto similar" class="w-full h-48 sm:h-64 object-contain rounded-lg bg-black/40" loading="lazy" onerror="this.parentElement.innerHTML='<div class=\\'flex items-center justify-center h-48 text-slate-600 text-sm\\'><i class=\\'fas fa-image mr-2\\'></i>Sin imagen</div>'">
                    </div>
                    ` : ''}
                </div>
                ${isPerceptualPlagiarism ? `
                <div class="mt-3 flex items-start gap-2 bg-amber-500/10 border border-amber-500/20 rounded-xl px-3 sm:px-4 py-3">
                    <i class="fas fa-triangle-exclamation text-amber-400 mt-0.5 shrink-0"></i>
                    <p class="text-xs text-amber-300 font-medium">
                        Plagio perceptual detectado. Revisá ambas imágenes y decidí si es el mismo producto.
                    </p>
                </div>
                ` : ''}
                ${product.plagiarism_status === 'confirmed' ? `
                <div class="mt-3 flex items-start gap-2 bg-rose-500/10 border border-rose-500/20 rounded-xl px-3 sm:px-4 py-3">
                    <i class="fas fa-ban text-rose-400 mt-0.5 shrink-0"></i>
                    <p class="text-xs text-rose-300 font-medium">
                        Plagio confirmado: imagen idéntica a otro producto. Rechazo automático.
                    </p>
                </div>
                ` : ''}
            </div>
            ` : ''}

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                ${rows.map(([label, value]) => `
                    <div class="bg-black/20 border border-white/5 rounded-xl sm:rounded-2xl p-3 sm:p-4">
                        <p class="text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-1.5 sm:mb-2">${this.escape(label)}</p>
                        <p class="text-xs sm:text-sm font-semibold text-slate-200 break-words">${this.escape(value)}</p>
                    </div>
                `).join('')}
            </div>

            ${product.rag_rules_used ? `
            <div class="bg-black/20 border border-white/5 rounded-xl sm:rounded-2xl p-3 sm:p-4">
                <p class="text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2">Reglas RAG</p>
                <pre class="whitespace-pre-wrap text-xs text-slate-300">${this.escape(this.formatJson(product.rag_rules_used))}</pre>
            </div>
            ` : ''}`;
    }

    renderPagination(data) {
        const info = document.getElementById('admin-validation-page-info');
        const prev = document.getElementById('admin-validation-prev');
        const next = document.getElementById('admin-validation-next');
        const total = Number(data.total || 0);
        const pages = Number(data.pages || 1);
        if (info) info.textContent = `Página ${this.page} de ${pages} · ${total} producto(s)`;
        if (prev) prev.disabled = this.page <= 1;
        if (next) next.disabled = this.page >= pages;
    }

    updateTabs() {
        document.querySelectorAll('.admin-validation-tab').forEach((tab) => {
            const active = tab.dataset.status === this.status;
            tab.classList.toggle('border-rose-500/30', active);
            tab.classList.toggle('bg-rose-500/10', active);
            tab.classList.toggle('text-rose-300', active);
            tab.classList.toggle('border-white/10', !active);
            tab.classList.toggle('bg-white/5', !active);
            tab.classList.toggle('text-slate-400', !active);
        });
    }

    showLoader(show) {
        const loader = document.getElementById('admin-validation-loader');
        if (!loader) return;
        loader.classList.toggle('hidden', !show);
        loader.classList.toggle('flex', show);
    }

    parseProduct(value) {
        try {
            return JSON.parse(value || '{}');
        } catch (error) {
            console.error('Invalid product payload:', error);
            this.toast('No se pudo abrir la evidencia', 'error');
            return null;
        }
    }

    formatJson(value) {
        if (!value) return '—';
        if (typeof value === 'object') return JSON.stringify(value, null, 2);
        try {
            return JSON.stringify(JSON.parse(value), null, 2);
        } catch (_) {
            return String(value);
        }
    }

    formatBool(value) {
        return value === true || value === 't' || value === 'true' ? 'Sí' : 'No';
    }

    badge(value) {
        const normalized = String(value || '').toLowerCase();
        const styles = {
            approved: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            rejected: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
            pending_review: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            revision_humana: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        };
        return `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border ${styles[normalized] || 'bg-slate-500/10 text-slate-400 border-slate-500/20'}">${this.escape(value || 'Sin decisión')}</span>`;
    }

    escape(value) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(value ?? ''));
        return div.innerHTML;
    }

    escapeAttr(value) {
        return this.escape(value).replace(/'/g, '&#039;');
    }

    toast(message, type) {
        if (typeof showToast !== 'undefined') showToast(message, type);
    }
}

export const adminValidationController = new AdminValidationController();
export { AdminValidationController };
