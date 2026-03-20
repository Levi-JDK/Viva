import { AdminService } from '../services/AdminService.js';
import { AdminDashboardService } from '../services/AdminDashboardService.js';

export class AdminDashboardController {
    init() {
        // Expose global methods for inline HTML calls (legacy support)
        window.showPanel = this.showPanel.bind(this);
        window.toggleSidebar = this.toggleSidebar.bind(this);

        this.initPanels();
        this.initLogoFallback();
        this.initConfirmModal();

        // Panel data stores
        this.usuariosData = [];
        this.productosData = [];
        this.usuariosLoaded = false;
        this.productosLoaded = false;
        this.parametrosLoaded = false;
        this.parametrosCols = [];
        this.parametrosRows = [];
    }

    showPanel(id) {
        document.querySelectorAll('.admin-panel').forEach(p => {
            p.classList.remove('admin-panel--active');
            p.style.display = 'none';
        });

        const target = document.getElementById('panel-' + id);
        if (target) {
            target.style.display = 'block';
            void target.offsetWidth;
            target.classList.add('admin-panel--active');
        }

        const btn = document.querySelector(`[data-panel="${id}"]`);
        const nom = btn ? (btn.dataset.nom || id) : id;
        const titleEl = document.getElementById('panel-title');
        if (titleEl) titleEl.textContent = nom;

        document.querySelectorAll('.sidebar-btn').forEach(b => b.classList.remove('sidebar-btn--active'));
        if (btn) btn.classList.add('sidebar-btn--active');

        if (window.innerWidth <= 768) this.closeSidebar();

        // Lazy-load panel data
        if (id === 'usuarios') this.loadUsuarios();
        if (id === 'productos') this.loadProductos();
        if (id === 'menus') {
            import('../controllers/AdminMenusController.js').then(m => m.adminMenusController.onPanelShow());
        }
        if (id === 'parametros') this.loadParametros();
    }

    toggleSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        const overlay = document.getElementById('mobile-overlay');
        if (!sidebar || !overlay) return;
        const isOpen = sidebar.classList.toggle('admin-sidebar--open');
        overlay.style.display = isOpen ? 'block' : 'none';
    }

    closeSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        if (sidebar) sidebar.classList.remove('admin-sidebar--open');
        const overlay = document.getElementById('mobile-overlay');
        if (overlay) overlay.style.display = 'none';
    }

    initPanels() {
        document.querySelectorAll('.admin-panel:not(.admin-panel--active)').forEach(p => {
            p.style.display = 'none';
        });
    }

    initLogoFallback() {
        const logoImg = document.querySelector('.admin-logo-img');
        if (logoImg) {
            logoImg.addEventListener('error', () => {
                logoImg.style.display = 'none';
                const fallback = document.getElementById('logo-fallback');
                if (fallback) fallback.style.display = 'inline';
            });
        }
    }

    // ═══════════════════════════════════════════
    // Confirmation Modal
    // ═══════════════════════════════════════════
    initConfirmModal() {
        this.confirmModal = document.getElementById('confirm-modal');
        this.confirmInner = this.confirmModal ? this.confirmModal.querySelector('.max-w-md') : null;
        this.confirmResolve = null;

        if (!this.confirmModal) return;

        document.getElementById('confirm-accept')?.addEventListener('click', () => {
            this.hideConfirm();
            if (this.confirmResolve) this.confirmResolve(true);
        });

        document.getElementById('confirm-cancel')?.addEventListener('click', () => {
            this.hideConfirm();
            if (this.confirmResolve) this.confirmResolve(false);
        });
    }

    showConfirm(title, message) {
        return new Promise((resolve) => {
            this.confirmResolve = resolve;
            const titleEl = document.getElementById('confirm-title');
            const msgEl = document.getElementById('confirm-message');
            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.textContent = message;

            this.confirmModal.classList.remove('hidden');
            this.confirmModal.classList.add('flex');
            setTimeout(() => {
                this.confirmModal.classList.remove('opacity-0');
                if (this.confirmInner) this.confirmInner.classList.remove('scale-95');
            }, 10);
        });
    }

    hideConfirm() {
        if (!this.confirmModal) return;
        this.confirmModal.classList.add('opacity-0');
        if (this.confirmInner) this.confirmInner.classList.add('scale-95');
        setTimeout(() => {
            this.confirmModal.classList.add('hidden');
            this.confirmModal.classList.remove('flex');
        }, 300);
    }

    // ═══════════════════════════════════════════
    // Panel: USUARIOS
    // ═══════════════════════════════════════════
    async loadUsuarios() {
        if (this.usuariosLoaded) return;
        try {
            const result = await AdminDashboardService.fetchUsers();
            if (result.success) {
                this.usuariosData = result.data;
                this.renderUsuarios(this.usuariosData);
                this.usuariosLoaded = true;
            }
        } catch (error) {
            console.error('Error loading users:', error);
            if (typeof showToast !== 'undefined') showToast('Error al cargar usuarios', 'error');
        } finally {
            const loader = document.getElementById('usuarios-loader');
            if (loader) loader.classList.add('hidden');
        }
    }

    renderUsuarios(list) {
        const tbody = document.getElementById('usuarios-tbody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500 italic">No se encontraron usuarios.</td></tr>';
            return;
        }

        list.forEach(u => {
            const isActive = u.is_active === true || u.is_active === 't' || u.is_active === 'true';
            const fecha = u.created_at ? new Date(u.created_at).toLocaleDateString('es-CO') : '—';
            const avatar = u.foto_user ? `${BASE_URL}${u.foto_user}` : `${BASE_URL}images/profiles/default.webp`;

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-white/[0.02] transition-colors group';
            tr.innerHTML = `
                <td class="px-4 py-3 font-mono text-slate-500">${u.id_user}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img src="${avatar}" class="w-8 h-8 rounded-full object-cover border border-white/10" alt="">
                        <span class="font-bold text-white">${u.nom_user || ''} ${u.ape_user || ''}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-slate-400">${u.mail_user || ''}</td>
                <td class="px-4 py-3">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        ${u.nom_grupo || 'Sin rol'}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest ${isActive ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'}">
                        ${isActive ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td class="px-4 py-3 text-slate-500 text-xs font-mono">${fecha}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button data-action="gestionar-menu" data-user-id="${u.id_user}" data-user-name="${u.nom_user} ${u.ape_user}"
                            class="h-8 px-3 rounded-full bg-violet-500/10 hover:bg-violet-500 text-violet-400 hover:text-white transition-colors text-[10px] font-bold tracking-widest uppercase flex items-center gap-1.5"
                            title="Gestionar Menú">
                            <i class="fas fa-layer-group text-xs"></i>
                            <span>Menú</span>
                        </button>
                        <button data-action="toggle-user" data-user-id="${u.id_user}" data-user-name="${u.nom_user} ${u.ape_user}" data-current-state="${isActive}"
                            class="w-8 h-8 rounded-full ${isActive ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400'} hover:text-white transition-colors"
                            title="${isActive ? 'Desactivar' : 'Activar'}">
                            <i class="fas ${isActive ? 'fa-user-slash' : 'fa-user-check'} text-xs"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    filterUsuarios(query) {
        const q = query.toLowerCase();
        const filtered = this.usuariosData.filter(u =>
            (u.nom_user || '').toLowerCase().includes(q) ||
            (u.ape_user || '').toLowerCase().includes(q) ||
            (u.mail_user || '').toLowerCase().includes(q)
        );
        this.renderUsuarios(filtered);
    }

    async handleToggleUser(btn) {
        const userId = btn.dataset.userId;
        const userName = btn.dataset.userName;
        const currentState = btn.dataset.currentState === 'true';
        const action = currentState ? 'desactivar' : 'activar';

        const confirmed = await this.showConfirm(
            `¿${action.charAt(0).toUpperCase() + action.slice(1)} usuario?`,
            `Se va a ${action} al usuario ${userName}.`
        );
        if (!confirmed) return;

        try {
            const result = await AdminDashboardService.toggleUser(userId, !currentState);
            if (result.success) {
                if (typeof showToast !== 'undefined') showToast(`Usuario ${action}do correctamente`, 'success');
                this.usuariosLoaded = false;
                await this.loadUsuarios();
            } else {
                if (typeof showToast !== 'undefined') showToast(result.message || 'Error', 'error');
            }
        } catch (error) {
            console.error('Toggle user error:', error);
            if (typeof showToast !== 'undefined') showToast('Error de conexión', 'error');
        }
    }

    handleGestionMenu(btn) {
        const userId = btn.dataset.userId;
        const userName = btn.dataset.userName;
        // Navigate to menus panel and pre-select user
        this.showPanel('menus');
        import('../controllers/AdminMenusController.js').then(m => {
            m.adminMenusController.selectUser(userId, userName);
        });
    }

    // ═══════════════════════════════════════════
    // Panel: PRODUCTOS
    // ═══════════════════════════════════════════
    async loadProductos() {
        if (this.productosLoaded) return;
        try {
            const result = await AdminDashboardService.fetchProducts();
            if (result.success) {
                this.productosData = result.data;
                this.renderProductos(this.productosData);
                this.productosLoaded = true;
            }
        } catch (error) {
            console.error('Error loading products:', error);
            if (typeof showToast !== 'undefined') showToast('Error al cargar productos', 'error');
        } finally {
            const loader = document.getElementById('productos-loader');
            if (loader) loader.classList.add('hidden');
        }
    }

    renderProductos(list) {
        const tbody = document.getElementById('productos-tbody');
        if (!tbody) return;
        tbody.innerHTML = '';

        // Separate active from archived
        const activos = list.filter(p => !(p.is_deleted === true || p.is_deleted === 't' || p.is_deleted === 'true'));
        const archivados = list.filter(p => p.is_deleted === true || p.is_deleted === 't' || p.is_deleted === 'true');

        if (activos.length === 0 && archivados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-slate-500 italic">No se encontraron productos.</td></tr>';
            return;
        }

        // Render active products
        activos.forEach(p => this._renderProductRow(tbody, p, false));

        // Render archived section
        if (archivados.length > 0) {
            const separatorTr = document.createElement('tr');
            separatorTr.innerHTML = `
                <td colspan="9" class="px-4 pt-8 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-px flex-1 bg-white/5"></div>
                        <span class="text-[10px] font-bold tracking-widest uppercase text-rose-400/70">
                            <i class="fas fa-archive mr-1"></i> Archivados (${archivados.length})
                        </span>
                        <div class="h-px flex-1 bg-white/5"></div>
                    </div>
                </td>`;
            tbody.appendChild(separatorTr);

            archivados.forEach(p => this._renderProductRow(tbody, p, true));
        }
    }

    _renderProductRow(tbody, p, isArchived) {
        const isActive = p.is_active === true || p.is_active === 't' || p.is_active === 'true';
        const img = p.primera_imagen ? `${BASE_URL}${p.primera_imagen}` : `${BASE_URL}images/default_product.jpg`;
        const precio = parseFloat(p.precio_producto || 0).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });

        const tr = document.createElement('tr');
        tr.className = `hover:bg-white/[0.02] transition-colors group ${isArchived ? 'opacity-50' : ''}`;
        tr.innerHTML = `
            <td class="px-4 py-3 font-mono text-slate-500">${p.id_producto}</td>
            <td class="px-4 py-3">
                <img src="${img}" class="w-10 h-10 rounded-xl object-cover border border-white/10 ${isArchived ? 'grayscale' : ''}" alt="" onerror="this.src='${BASE_URL}images/default_product.jpg'">
            </td>
            <td class="px-4 py-3 font-bold text-white max-w-[200px] truncate ${isArchived ? 'line-through text-slate-500' : ''}">${p.nom_producto || '—'}</td>
            <td class="px-4 py-3 font-mono text-emerald-400">${precio}</td>
            <td class="px-4 py-3 font-mono text-center">${p.stock_productor || 0}</td>
            <td class="px-4 py-3">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-violet-500/10 text-violet-400 border border-violet-500/20">
                    ${p.nom_categoria || 'Sin cat.'}
                </span>
            </td>
            <td class="px-4 py-3 text-slate-400 text-xs truncate max-w-[120px]">${p.nom_stand || '—'}</td>
            <td class="px-4 py-3">
                ${isArchived
                    ? '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-slate-500/10 text-slate-500 border border-slate-500/20">Archivado</span>'
                    : `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest ${isActive ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'}">${isActive ? 'Activo' : 'Inactivo'}</span>`
                }
            </td>
            <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    ${isArchived
                        ? `<button data-action="toggle-product" data-product-id="${p.id_producto}" data-product-name="${p.nom_producto}" data-current-state="false"
                                class="px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-white text-xs font-bold transition-colors"
                                title="Reactivar producto">
                                <i class="fas fa-undo mr-1"></i> Reactivar
                           </button>`
                        : `<button data-action="toggle-product" data-product-id="${p.id_producto}" data-product-name="${p.nom_producto}" data-current-state="${isActive}"
                                class="w-8 h-8 rounded-full ${isActive ? 'bg-rose-500/10 hover:bg-rose-500 text-rose-400' : 'bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400'} hover:text-white transition-colors"
                                title="${isActive ? 'Desactivar' : 'Activar'}">
                                <i class="fas ${isActive ? 'fa-eye-slash' : 'fa-eye'} text-xs"></i>
                           </button>`
                    }
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    }

    filterProductos(query) {
        const q = query.toLowerCase();
        const filtered = this.productosData.filter(p =>
            (p.nom_producto || '').toLowerCase().includes(q) ||
            (p.nom_stand || '').toLowerCase().includes(q) ||
            (p.nom_categoria || '').toLowerCase().includes(q)
        );
        this.renderProductos(filtered);
    }

    async handleToggleProduct(btn) {
        const productId = btn.dataset.productId;
        const productName = btn.dataset.productName;
        const currentState = btn.dataset.currentState === 'true';
        const action = currentState ? 'desactivar' : 'activar';

        const confirmed = await this.showConfirm(
            `¿${action.charAt(0).toUpperCase() + action.slice(1)} producto?`,
            `Se va a ${action} "${productName}".`
        );
        if (!confirmed) return;

        try {
            const result = await AdminDashboardService.toggleProduct(productId, !currentState);
            if (result.success) {
                if (typeof showToast !== 'undefined') showToast(`Producto ${action}do correctamente`, 'success');
                this.productosLoaded = false;
                await this.loadProductos();
            } else {
                if (typeof showToast !== 'undefined') showToast(result.message || 'Error', 'error');
            }
        } catch (error) {
            console.error('Toggle product error:', error);
            if (typeof showToast !== 'undefined') showToast('Error de conexión', 'error');
        }
    }

    // ═══════════════════════════════════════════
    // Panel: PARÁMETROS DB (update-only, editable blocks)
    // ═══════════════════════════════════════════
    async loadParametros() {
        if (this.parametrosLoaded) return;
        const loader = document.getElementById('parametros-loader');
        if (loader) loader.classList.remove('hidden');

        try {
            const result = await AdminDashboardService.crudRead('parametros');
            if (result.success) {
                this.parametrosCols = result.data.columnas;
                this.parametrosRows = result.data.filas;
                this.renderParametros(this.parametrosCols, this.parametrosRows);
                this.initParametrosActions();
                this.parametrosLoaded = true;
            }
        } catch (error) {
            if (typeof showToast !== 'undefined') showToast('Error al cargar parámetros', 'error');
        } finally {
            if (loader) loader.classList.add('hidden');
        }
    }

    renderParametros(cols, rows) {
        const container = document.getElementById('parametros-fields');
        if (!container) return;

        if (rows.length === 0) {
            container.innerHTML = '<p class="text-center text-slate-500 italic py-12">No hay parámetros configurados.</p>';
            return;
        }

        const row = rows[0]; // Registro único
        this.parametrosOriginal = { ...row };
        let html = '';

        cols.forEach(c => {
            const val = row[c] ?? '';
            const label = c.replace(/_/g, ' ');
            const isId = c.startsWith('id_') || c === 'id';
            const isReadonly = isId;

            html += `
                <div class="bg-slate-900 border border-white/10 rounded-2xl p-5 hover:border-white/15 transition-colors ${isReadonly ? 'opacity-60' : ''}">
                    <label class="block text-[10px] font-bold tracking-widest uppercase ${isReadonly ? 'text-slate-600' : 'text-slate-500'} mb-3">${label}</label>
                    <input type="text"
                        name="${c}"
                        value="${String(val).replace(/"/g, '&quot;')}"
                        ${isReadonly ? 'readonly' : ''}
                        data-original="${String(val).replace(/"/g, '&quot;')}"
                        class="param-input w-full bg-black/20 border border-white/5 rounded-xl px-4 py-3 text-sm font-bold
                            ${isReadonly ? 'text-slate-600 cursor-not-allowed' : 'text-white focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50'}
                            placeholder-slate-600 focus:outline-none transition-all"
                    >
                </div>`;
        });

        container.innerHTML = html;

        // Show actions on input change
        container.querySelectorAll('.param-input:not([readonly])').forEach(input => {
            input.addEventListener('input', () => this.checkParametrosChanged());
        });
    }

    checkParametrosChanged() {
        const actions = document.getElementById('parametros-actions');
        if (!actions) return;
        const inputs = document.querySelectorAll('#parametros-fields .param-input:not([readonly])');
        let changed = false;
        inputs.forEach(inp => {
            if (inp.value !== inp.dataset.original) changed = true;
        });
        if (changed) {
            actions.classList.remove('hidden');
            actions.classList.add('flex');
        } else {
            actions.classList.add('hidden');
            actions.classList.remove('flex');
        }
    }

    initParametrosActions() {
        const btnSave = document.getElementById('parametros-btn-save');
        const btnCancel = document.getElementById('parametros-btn-cancel');

        if (btnSave && !btnSave._bound) {
            btnSave._bound = true;
            btnSave.addEventListener('click', () => this.saveParametros());
        }
        if (btnCancel && !btnCancel._bound) {
            btnCancel._bound = true;
            btnCancel.addEventListener('click', () => this.cancelParametros());
        }
    }

    async saveParametros() {
        const inputs = document.querySelectorAll('#parametros-fields .param-input');
        const datos = {};
        inputs.forEach(inp => { datos[inp.name] = inp.value; });

        const btnSave = document.getElementById('parametros-btn-save');
        if (btnSave) {
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> <span>Guardando...</span>';
            btnSave.disabled = true;
        }

        try {
            const result = await AdminDashboardService.crudWrite('update', 'parametros', datos);
            if (result.success) {
                if (typeof showToast !== 'undefined') showToast('Parámetros actualizados correctamente', 'success');
                // Update originals
                inputs.forEach(inp => { inp.dataset.original = inp.value; });
                this.checkParametrosChanged();
            } else {
                if (typeof showToast !== 'undefined') showToast(result.message || 'Error al actualizar', 'error');
            }
        } catch (error) {
            if (typeof showToast !== 'undefined') showToast('Error de conexión', 'error');
        } finally {
            if (btnSave) {
                btnSave.innerHTML = '<i class="fas fa-save text-xs"></i> <span>Guardar</span>';
                btnSave.disabled = false;
            }
        }
    }

    cancelParametros() {
        const inputs = document.querySelectorAll('#parametros-fields .param-input');
        inputs.forEach(inp => { inp.value = inp.dataset.original; });
        this.checkParametrosChanged();
    }
}
export const adminDashboardController = new AdminDashboardController();