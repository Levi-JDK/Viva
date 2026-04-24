import { AdminDashboardService } from '../services/AdminDashboardService.js';
import { AdminService } from '../services/AdminService.js';
import { AdminCrudService } from '../services/AdminCrudService.js';

export class AdminDashboardController {
    init() {
        // Expose global methods for inline HTML calls (legacy support)
        window.showPanel = this.showPanel.bind(this);
        window.toggleSidebar = this.toggleSidebar.bind(this);

        this.initPanels();
        this.initLogoFallback();
        this.initConfirmModal();
        this.initCrudPanel();
        this.initCrudConfirmModal();

        // Panel data stores
        this.usuariosData = [];
        this.productosData = [];
        this.usuariosLoaded = false;
        this.productosLoaded = false;
        this.parametrosLoaded = false;
        this.parametrosCols = [];
        this.parametrosRows = [];

        this.crudCurrentEntity = null;
        this.crudSchemaCols = [];
        this.crudIsEditing = false;
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

    initCrudPanel() {
        this.crudDropdownTrigger = document.getElementById('crud-dropdown-trigger');
        this.crudDropdownMenu = document.getElementById('crud-dropdown-menu');
        this.crudDropdownLabel = document.getElementById('crud-dropdown-label');
        this.crudDropdownArrow = document.getElementById('crud-dropdown-arrow');
        this.crudBtnNew = document.getElementById('crud-btn-new');
        this.crudEmptyState = document.getElementById('crud-empty-state');
        this.crudTable = document.getElementById('crud-table');
        this.crudTheadRow = document.getElementById('crud-thead-tr');
        this.crudTbody = document.getElementById('crud-tbody');
        this.crudLoader = document.getElementById('crud-loader');
        this.crudModal = document.getElementById('crud-modal');
        this.crudModalInner = this.crudModal ? this.crudModal.querySelector('.max-w-lg') : null;
        this.crudForm = document.getElementById('crud-form');
        this.crudFormFields = document.getElementById('crud-form-fields');
        this.crudModalTitle = document.getElementById('crud-modal-title');
        this.crudModalSubtitle = document.getElementById('crud-modal-subtitle');

        if (this.crudDropdownTrigger && this.crudDropdownMenu) {
            this.crudDropdownTrigger.addEventListener('click', (event) => {
                event.preventDefault();
                this.toggleCrudDropdown();
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('#crud-dropdown')) {
                    this.closeCrudDropdown();
                }
            });
        }
    }

    initCrudConfirmModal() {
        this.crudConfirmModal = document.getElementById('crud-confirm-modal');
        this.crudConfirmInner = this.crudConfirmModal ? this.crudConfirmModal.querySelector('.max-w-sm') : null;
        this.crudConfirmResolve = null;

        if (!this.crudConfirmModal) return;

        document.getElementById('crud-confirm-accept')?.addEventListener('click', () => {
            this.hideCrudConfirm();
            if (this.crudConfirmResolve) this.crudConfirmResolve(true);
        });

        document.getElementById('crud-confirm-cancel')?.addEventListener('click', () => {
            this.hideCrudConfirm();
            if (this.crudConfirmResolve) this.crudConfirmResolve(false);
        });
    }

    toggleCrudDropdown() {
        if (!this.crudDropdownMenu) return;
        const isOpen = !this.crudDropdownMenu.classList.contains('hidden');
        this.crudDropdownMenu.classList.toggle('hidden');
        if (this.crudDropdownArrow) {
            this.crudDropdownArrow.style.transform = isOpen ? '' : 'rotate(180deg)';
        }
    }

    closeCrudDropdown() {
        if (!this.crudDropdownMenu) return;
        this.crudDropdownMenu.classList.add('hidden');
        if (this.crudDropdownArrow) this.crudDropdownArrow.style.transform = '';
    }

    async selectCrudEntity(btn) {
        this.crudCurrentEntity = btn.dataset.entity || btn.dataset.value || null;
        if (!this.crudCurrentEntity) return;

        if (this.crudDropdownLabel) {
            this.crudDropdownLabel.textContent = btn.textContent.trim();
        }

        document.querySelectorAll('#crud-dropdown-menu [data-action="select-crud-entity"]').forEach((option) => {
            option.classList.remove('text-amber-400', 'bg-white/5');
        });
        btn.classList.add('text-amber-400', 'bg-white/5');

        if (this.crudBtnNew) this.crudBtnNew.disabled = false;
        if (this.crudEmptyState) this.crudEmptyState.classList.add('hidden');
        if (this.crudTable) this.crudTable.classList.remove('hidden');

        this.closeCrudDropdown();
        await this.loadCrudData();
    }

    async loadCrudData() {
        if (!this.crudCurrentEntity || !this.crudTheadRow || !this.crudTbody) return;

        this.showCrudLoader(true);
        this.crudTheadRow.innerHTML = '';
        this.crudTbody.innerHTML = '';

        try {
            const result = await AdminCrudService.readEntity(this.crudCurrentEntity);
            if (result.success) {
                this.crudSchemaCols = result.data.columnas || [];
                this.renderCrudTable(this.crudSchemaCols, result.data.filas || []);
                return;
            }

            if (typeof showToast !== 'undefined') showToast(result.message || 'Error al cargar entidad', 'error');
        } catch (error) {
            console.error('Error loading CRUD entity:', error);
            if (typeof showToast !== 'undefined') showToast('Error al cargar entidad', 'error');
        } finally {
            this.showCrudLoader(false);
        }
    }

    renderCrudTable(cols, rows) {
        if (!this.crudTheadRow || !this.crudTbody) return;

        this.crudTheadRow.innerHTML = '';
        this.crudTbody.innerHTML = '';

        cols.forEach((col) => {
            const th = document.createElement('th');
            th.className = 'px-4 py-3 whitespace-nowrap';
            th.textContent = col.replace(/_/g, ' ');
            this.crudTheadRow.appendChild(th);
        });

        const actionsHeader = document.createElement('th');
        actionsHeader.className = 'px-4 py-3 text-right';
        actionsHeader.textContent = 'Acciones';
        this.crudTheadRow.appendChild(actionsHeader);

        if (!rows.length) {
            this.crudTbody.innerHTML = `<tr><td colspan="${cols.length + 1}" class="px-4 py-8 text-center text-slate-500 italic">No hay registros almacenados.</td></tr>`;
            return;
        }

        const primaryKeyField = cols[0];

        rows.forEach((row) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-white/[0.02] transition-colors group';

            cols.forEach((col) => {
                const td = document.createElement('td');
                td.className = 'px-4 py-3 border-t border-white/5';

                let value = row[col] ?? '-';
                if (typeof value === 'string' && value.length > 50) {
                    value = `${value.substring(0, 50)}...`;
                }

                td.textContent = value;
                tr.appendChild(td);
            });

            const actionsTd = document.createElement('td');
            actionsTd.className = 'px-4 py-3 border-t border-white/5 text-right w-32';

            const actionsWrap = document.createElement('div');
            actionsWrap.className = 'flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity';

            const editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.className = 'w-8 h-8 rounded-full bg-sky-500/10 hover:bg-sky-500 text-sky-400 hover:text-white transition-colors';
            editBtn.title = 'Editar';
            editBtn.dataset.action = 'edit-crud-record';
            editBtn.dataset.row = encodeURIComponent(JSON.stringify(row));
            editBtn.dataset.pk = row[primaryKeyField] ?? '';
            editBtn.innerHTML = '<i class="fas fa-edit text-xs pointer-events-none"></i>';

            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'w-8 h-8 rounded-full bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white transition-colors';
            deleteBtn.title = 'Archivar';
            deleteBtn.dataset.action = 'delete-crud-record';
            deleteBtn.dataset.row = encodeURIComponent(JSON.stringify(row));
            deleteBtn.dataset.pk = row[primaryKeyField] ?? '';
            deleteBtn.innerHTML = '<i class="fas fa-trash text-xs pointer-events-none"></i>';

            actionsWrap.appendChild(editBtn);
            actionsWrap.appendChild(deleteBtn);
            actionsTd.appendChild(actionsWrap);
            tr.appendChild(actionsTd);

            this.crudTbody.appendChild(tr);
        });
    }

    parseCrudRow(btn) {
        try {
            return JSON.parse(decodeURIComponent(btn.dataset.row || ''));
        } catch (error) {
            console.error('Error parsing CRUD row:', error);
            return null;
        }
    }

    openCrudModal(rowData = null) {
        if (!this.crudCurrentEntity || !this.crudModal || !this.crudFormFields) {
            if (typeof showToast !== 'undefined') showToast('Seleccioná una entidad primero', 'error');
            return;
        }

        this.crudIsEditing = Boolean(rowData);

        if (this.crudModalTitle) {
            this.crudModalTitle.textContent = this.crudIsEditing ? 'Editar Registro' : 'Nuevo Registro';
        }
        if (this.crudModalSubtitle) {
            this.crudModalSubtitle.textContent = `Entidad: ${this.crudCurrentEntity}`;
        }

        const stringPkEntities = ['color', 'idioma', 'moneda', 'forma_pago', 'transportadora'];
        this.crudFormFields.innerHTML = '';

        this.crudSchemaCols.forEach((column, index) => {
            const isPrimaryKey = index === 0;
            if (isPrimaryKey && !this.crudIsEditing && !stringPkEntities.includes(this.crudCurrentEntity)) {
                return;
            }

            const value = rowData ? rowData[column] ?? '' : '';
            const labelText = isPrimaryKey && this.crudCurrentEntity === 'color'
                ? 'RGB (Hex)'
                : column.replace(/_/g, ' ');
            const escapedValue = String(value).replace(/"/g, '&quot;');

            const wrapper = document.createElement('div');
            wrapper.className = 'group';

            const label = document.createElement('label');
            label.className = 'block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-amber-500 transition-colors';
            label.innerHTML = `${labelText}${isPrimaryKey && this.crudIsEditing ? ' <span class="text-rose-400">(Bloqueado)</span>' : ''}`;
            wrapper.appendChild(label);

            if (this.crudCurrentEntity === 'categoria' && column === 'img_cat') {
                wrapper.innerHTML += `
                    <input type="hidden" name="img_cat" value="${escapedValue}">
                    <input type="file"
                        name="img_cat_upload"
                        data-file-field="img_cat"
                        accept="image/*"
                        class="w-full bg-black/20 border border-white/5 rounded-xl px-5 py-3 text-sm font-bold text-white file:mr-4 file:border-0 file:rounded-lg file:bg-amber-500 file:px-3 file:py-2 file:text-xs file:font-bold file:text-slate-900 hover:file:bg-amber-400 focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 focus:outline-none transition-all shadow-inner"
                    >
                    <p class="mt-2 text-xs text-slate-500 break-all">${value ? `Actual: ${escapedValue}` : 'Sin imagen cargada'}</p>
                `;

                this.crudFormFields.appendChild(wrapper);
                return;
            }

            const input = document.createElement('input');
            input.type = column.includes('pass') ? 'password' : (column.includes('mail') ? 'email' : 'text');
            input.name = column;
            input.value = value;
            input.className = `w-full bg-black/20 border border-white/5 rounded-xl px-5 py-3.5 text-sm font-bold ${isPrimaryKey && this.crudIsEditing ? 'text-slate-500 cursor-not-allowed' : 'text-white'} placeholder-slate-600 focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 focus:outline-none transition-all shadow-inner`;

            if (isPrimaryKey && this.crudIsEditing) {
                input.readOnly = true;
            }

            wrapper.appendChild(input);
            this.crudFormFields.appendChild(wrapper);
        });

        this.crudModal.classList.remove('hidden');
        this.crudModal.classList.add('flex');
        setTimeout(() => {
            this.crudModal.classList.remove('opacity-0');
            if (this.crudModalInner) this.crudModalInner.classList.remove('scale-95');
        }, 10);
    }

    closeCrudModal() {
        if (!this.crudModal) return;

        this.crudModal.classList.add('opacity-0');
        if (this.crudModalInner) this.crudModalInner.classList.add('scale-95');
        setTimeout(() => {
            this.crudModal.classList.add('hidden');
            this.crudModal.classList.remove('flex');
        }, 300);
    }

    async submitCrudForm(event, form) {
        event.preventDefault();
        if (!this.crudCurrentEntity || !form) return;

        const formData = new FormData(form);
        const datos = {};
        for (const [key, value] of formData.entries()) {
            if (value instanceof File && !value.name) continue;
            datos[key] = value;
        }

        form.querySelectorAll('input[type="file"][data-file-field]').forEach((input) => {
            delete datos[input.name];

            const file = input.files?.[0] ?? null;
            if (file) {
                datos[input.dataset.fileField] = file;
            }
        });

        const btnSubmit = form.querySelector('button[type="submit"]');

        try {
            if (btnSubmit) {
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Procesando...</span>';
                btnSubmit.disabled = true;
            }

            const result = await AdminCrudService.writeEntity(
                this.crudIsEditing ? 'update' : 'create',
                this.crudCurrentEntity,
                datos
            );

            if (result.success && result.data) {
                if (typeof showToast !== 'undefined') {
                    showToast(`Operación ${this.crudIsEditing ? 'actualizada' : 'registrada'} exitosamente`, 'success');
                }
                this.closeCrudModal();
                await this.loadCrudData();
            } else {
                if (typeof showToast !== 'undefined') showToast(result.message || 'Error en validación DB.', 'error');
            }
        } catch (error) {
            console.error('CRUD submit error:', error);
            if (typeof showToast !== 'undefined') showToast('Error de red.', 'error');
        } finally {
            if (btnSubmit) {
                btnSubmit.innerHTML = '<span>Guardar Datos</span>';
                btnSubmit.disabled = false;
            }
        }
    }

    handleEditCrudRecord(btn) {
        const row = this.parseCrudRow(btn);
        if (!row) {
            if (typeof showToast !== 'undefined') showToast('No se pudo abrir el registro', 'error');
            return;
        }

        this.openCrudModal(row);
    }

    showCrudConfirm(title, message) {
        return new Promise((resolve) => {
            this.crudConfirmResolve = resolve;

            const titleEl = document.getElementById('crud-confirm-title');
            const messageEl = document.getElementById('crud-confirm-message');
            if (titleEl) titleEl.textContent = title;
            if (messageEl) messageEl.textContent = message;

            this.crudConfirmModal.classList.remove('hidden');
            this.crudConfirmModal.classList.add('flex');

            setTimeout(() => {
                this.crudConfirmModal.classList.remove('opacity-0');
                if (this.crudConfirmInner) this.crudConfirmInner.classList.remove('scale-95');
            }, 10);
        });
    }

    hideCrudConfirm() {
        if (!this.crudConfirmModal) return;

        this.crudConfirmModal.classList.add('opacity-0');
        if (this.crudConfirmInner) this.crudConfirmInner.classList.add('scale-95');

        setTimeout(() => {
            this.crudConfirmModal.classList.add('hidden');
            this.crudConfirmModal.classList.remove('flex');
        }, 300);
    }

    async handleDeleteCrudRecord(btn) {
        const row = this.parseCrudRow(btn);
        if (!row || !this.crudCurrentEntity) {
            if (typeof showToast !== 'undefined') showToast('No se pudo eliminar el registro', 'error');
            return;
        }

        const confirmed = await this.showCrudConfirm(
            '¿Eliminar registro?',
            'Se archivará lógicamente este registro. ¿Deseas continuar?'
        );

        if (!confirmed) return;

        try {
            const result = await AdminCrudService.deleteEntity(this.crudCurrentEntity, row);
            if (result.success && result.data) {
                if (typeof showToast !== 'undefined') showToast('Registro archivado correctamente.', 'success');
                await this.loadCrudData();
            } else {
                if (typeof showToast !== 'undefined') showToast(result.message || 'Error en DB.', 'error');
            }
        } catch (error) {
            console.error('CRUD delete error:', error);
            if (typeof showToast !== 'undefined') showToast('Error de red.', 'error');
        }
    }

    showCrudLoader(show) {
        if (!this.crudLoader || !this.crudTable) return;

        if (show) {
            this.crudLoader.classList.remove('hidden');
            this.crudTable.classList.add('hidden');
            return;
        }

        this.crudLoader.classList.add('hidden');
        if (this.crudCurrentEntity) this.crudTable.classList.remove('hidden');
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
            const escapedValue = String(val).replace(/"/g, '&quot;');

            if (c === 'foto_hero') {
                html += `
                    <div class="bg-slate-900 border border-white/10 rounded-2xl p-5 hover:border-white/15 transition-colors">
                        <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-3">${label}</label>
                        <input type="hidden" name="foto_hero" value="${escapedValue}" data-original="${escapedValue}" class="param-input">
                        <input type="file"
                            name="foto_hero_upload"
                            data-file-field="foto_hero"
                            accept="image/*"
                            class="param-file-input w-full bg-black/20 border border-white/5 rounded-xl px-4 py-3 text-sm font-bold text-white file:mr-4 file:border-0 file:rounded-lg file:bg-amber-500 file:px-3 file:py-2 file:text-xs file:font-bold file:text-slate-900 hover:file:bg-amber-400 focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 focus:outline-none transition-all"
                        >
                        <p class="mt-2 text-xs text-slate-500 break-all">${val ? `Actual: ${escapedValue}` : 'Sin imagen cargada'}</p>
                    </div>`;
                return;
            }

            html += `
                <div class="bg-slate-900 border border-white/10 rounded-2xl p-5 hover:border-white/15 transition-colors ${isReadonly ? 'opacity-60' : ''}">
                    <label class="block text-[10px] font-bold tracking-widest uppercase ${isReadonly ? 'text-slate-600' : 'text-slate-500'} mb-3">${label}</label>
                    <input type="text"
                        name="${c}"
                        value="${escapedValue}"
                        ${isReadonly ? 'readonly' : ''}
                        data-original="${escapedValue}"
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
        container.querySelectorAll('.param-file-input').forEach((input) => {
            input.addEventListener('change', () => this.checkParametrosChanged());
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
        document.querySelectorAll('#parametros-fields .param-file-input').forEach((input) => {
            if (input.files?.length) changed = true;
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
        const formData = new FormData();
        inputs.forEach(inp => { formData.append(inp.name, inp.value); });
        document.querySelectorAll('#parametros-fields .param-file-input').forEach((input) => {
            const file = input.files?.[0] ?? null;
            if (file) {
                formData.append(input.dataset.fileField, file);
            }
        });

        const btnSave = document.getElementById('parametros-btn-save');
        if (btnSave) {
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> <span>Guardando...</span>';
            btnSave.disabled = true;
        }

        try {
            const result = await AdminService.updateParameters(formData);
            if (result.success) {
                if (typeof showToast !== 'undefined') showToast('Parámetros actualizados correctamente', 'success');
                this.parametrosLoaded = false;
                await this.loadParametros();
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
        document.querySelectorAll('#parametros-fields .param-file-input').forEach((input) => {
            input.value = '';
        });
        this.checkParametrosChanged();
    }
}
export const adminDashboardController = new AdminDashboardController();
