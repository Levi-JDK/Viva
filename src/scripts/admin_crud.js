/**
 * src/scripts/admin_crud.js
 * Controlador Vainilla para Gestor Dinámico de CRUD
 */

document.addEventListener('DOMContentLoaded', () => {
    // Custom dropdown elements
    const dropdownTrigger = document.getElementById('crud-dropdown-trigger');
    const dropdownMenu = document.getElementById('crud-dropdown-menu');
    const dropdownLabel = document.getElementById('crud-dropdown-label');
    const dropdownArrow = document.getElementById('crud-dropdown-arrow');
    const dropdownOptions = document.querySelectorAll('.crud-dropdown-option');

    const btnNew = document.getElementById('crud-btn-new');
    const tbEmpty = document.getElementById('crud-empty-state');
    const tbTable = document.getElementById('crud-table');
    const tHeadTr = document.getElementById('crud-thead-tr');
    const tBody = document.getElementById('crud-tbody');
    const loader = document.getElementById('crud-loader');

    let currentEntity = null;
    let schemaCols = [];
    let isEditing = false;
    let currentId = null;

    // ---- CUSTOM DROPDOWN HANDLERS ----
    if (dropdownTrigger && dropdownMenu) {
        dropdownTrigger.addEventListener('click', () => {
            const isOpen = !dropdownMenu.classList.contains('hidden');
            dropdownMenu.classList.toggle('hidden');
            if (dropdownArrow) dropdownArrow.style.transform = isOpen ? '' : 'rotate(180deg)';
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#crud-dropdown')) {
                dropdownMenu.classList.add('hidden');
                if (dropdownArrow) dropdownArrow.style.transform = '';
            }
        });

        // Option click
        dropdownOptions.forEach(opt => {
            opt.addEventListener('click', async () => {
                currentEntity = opt.dataset.value;
                dropdownLabel.textContent = opt.textContent.trim();
                dropdownMenu.classList.add('hidden');
                if (dropdownArrow) dropdownArrow.style.transform = '';

                // Highlight selected
                dropdownOptions.forEach(o => o.classList.remove('text-amber-400', 'bg-white/5'));
                opt.classList.add('text-amber-400', 'bg-white/5');

                btnNew.disabled = false;
                tbEmpty.classList.add('hidden');
                tbTable.classList.remove('hidden');
                await loadData();
            });
        });
    }

    // Modal
    const modal = document.getElementById('crud-modal');
    const modalInner = modal ? modal.querySelector('.max-w-lg') : null;
    const btnClose = document.getElementById('crud-modal-close');
    const btnCancel = document.getElementById('crud-btn-cancel');
    const form = document.getElementById('crud-form');
    const formFields = document.getElementById('crud-form-fields');
    const modalTitle = document.getElementById('crud-modal-title');
    const modalSub = document.getElementById('crud-modal-subtitle');

    btnNew.addEventListener('click', () => {
        openModal(false);
    });

    [btnClose, btnCancel].forEach(b => b.addEventListener('click', closeModal));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const fd = new FormData(form);
        const urlEncoded = new URLSearchParams();
        urlEncoded.append('accion', isEditing ? 'update' : 'create');
        urlEncoded.append('entidad', currentEntity);
        for (const [k, v] of fd.entries()) {
            urlEncoded.append(k, v);
        }

        try {
            const btnSubmit = form.querySelector('button[type="submit"]');
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
            btnSubmit.disabled = true;

            const baseUrl = window.BASE_URL || '';
            const res = await fetch(`${baseUrl}admin`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: urlEncoded.toString()
            });
            const data = await res.json();

            if (data.success && data.data) {
                if (window.showToast) window.showToast('Operación ' + (isEditing ? 'actualizada' : 'registrada') + ' exitosamente', 'success');
                closeModal();
                await loadData();
            } else {
                if (window.showToast) window.showToast(data.message || 'Error en validación DB.', 'error');
            }
        } catch (err) {
            console.error(err);
            if (window.Toast) Toast.fire({ icon: 'error', title: 'Error de red.' });
        } finally {
            const btnSubmit = form.querySelector('button[type="submit"]');
            btnSubmit.innerHTML = '<span>Guardar Datos</span>';
            btnSubmit.disabled = false;
        }
    });

    // ---- LÓGICA CORE ----
    async function loadData() {
        showLoader(true);
        tHeadTr.innerHTML = '';
        tBody.innerHTML = '';

        try {
            const baseUrl = window.BASE_URL || '';
            const res = await fetch(`${baseUrl}admin?accion=read&entidad=${currentEntity}&_=${Date.now()}`);
            const data = await res.json();

            if (data.success) {
                schemaCols = data.data.columnas;
                renderTable(data.data.columnas, data.data.filas);
            } else {
                if (window.Toast) Toast.fire({ icon: 'error', title: data.message });
            }
        } catch (e) {
            console.error(e);
        } finally {
            showLoader(false);
        }
    }

    function renderTable(cols, rows) {
        // Render THead
        cols.forEach(c => {
            const th = document.createElement('th');
            th.className = 'px-4 py-3 whitespace-nowrap';
            th.textContent = c.replace(/_/g, ' ');
            tHeadTr.appendChild(th);
        });
        const thActions = document.createElement('th');
        thActions.className = 'px-4 py-3 text-right';
        thActions.textContent = 'Acciones';
        tHeadTr.appendChild(thActions);

        // Render TBody
        if (rows.length === 0) {
            tBody.innerHTML = `<tr><td colspan="${cols.length + 1}" class="px-4 py-8 text-center text-slate-500 italic">No hay registros almacenados.</td></tr>`;
            return;
        }

        const pkField = cols[0]; // Asumimos convencionalmente que el primero es el PK

        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-white/[0.02] transition-colors group';

            cols.forEach(c => {
                const td = document.createElement('td');
                td.className = 'px-4 py-3 border-t border-white/5';

                // Truncate logic to avoid huge tables
                let val = r[c] ?? '-';
                if (val && val.length > 50) val = val.substring(0, 50) + '...';

                td.textContent = val;
                tr.appendChild(td);
            });

            // Actions
            const tdA = document.createElement('td');
            tdA.className = 'px-4 py-3 border-t border-white/5 text-right w-32';
            tdA.innerHTML = `
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button class="w-8 h-8 rounded-full bg-sky-500/10 hover:bg-sky-500 text-sky-400 hover:text-white transition-colors btn-edit" title="Editar">
                        <i class="fas fa-edit text-xs pointer-events-none"></i>
                    </button>
                    <!-- Eliminacion Logica solo si tab admite fun_d, en este CRUD generico podemos forzarlo -->
                    <button class="w-8 h-8 rounded-full bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white transition-colors btn-delete" title="Archivar">
                        <i class="fas fa-trash text-xs pointer-events-none"></i>
                    </button>
                </div>
            `;

            // Listeners
            const btnE = tdA.querySelector('.btn-edit');
            btnE.addEventListener('click', () => openModal(true, r));

            const btnD = tdA.querySelector('.btn-delete');
            btnD.addEventListener('click', () => deleteRecord(r[pkField], r));

            tr.appendChild(tdA);
            tBody.appendChild(tr);
        });
    }

    // ---- CONFIRM MODAL ----
    const confirmModal = document.getElementById('crud-confirm-modal');
    const confirmInner = confirmModal ? confirmModal.querySelector('.max-w-sm') : null;
    let confirmResolve = null;

    function showCrudConfirm(title, message) {
        return new Promise((resolve) => {
            confirmResolve = resolve;
            const titleEl = document.getElementById('crud-confirm-title');
            const msgEl = document.getElementById('crud-confirm-message');
            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.textContent = message;

            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
            setTimeout(() => {
                confirmModal.classList.remove('opacity-0');
                if (confirmInner) confirmInner.classList.remove('scale-95');
            }, 10);
        });
    }

    function hideCrudConfirm() {
        if (!confirmModal) return;
        confirmModal.classList.add('opacity-0');
        if (confirmInner) confirmInner.classList.add('scale-95');
        setTimeout(() => {
            confirmModal.classList.add('hidden');
            confirmModal.classList.remove('flex');
        }, 300);
    }

    document.getElementById('crud-confirm-accept')?.addEventListener('click', () => {
        hideCrudConfirm();
        if (confirmResolve) confirmResolve(true);
    });

    document.getElementById('crud-confirm-cancel')?.addEventListener('click', () => {
        hideCrudConfirm();
        if (confirmResolve) confirmResolve(false);
    });

    async function deleteRecord(id, fullRow) {
        const confirmed = await showCrudConfirm(
            '¿Eliminar registro?',
            'Se archivará lógicamente este registro. ¿Deseas continuar?'
        );
        if (!confirmed) return;

        const urlEncoded = new URLSearchParams();
        urlEncoded.append('accion', 'delete');
        urlEncoded.append('entidad', currentEntity);
        // Enviamos el objeto entero para compound keys support nativo
        for (let k in fullRow) urlEncoded.append(k, fullRow[k]);

        const baseUrl = window.BASE_URL || '';
        const res = await fetch(`${baseUrl}admin`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: urlEncoded.toString()
        });
        const data = await res.json();
        if (data.success && data.data) {
            if (window.showToast) window.showToast('Registro archivado correctamente.', 'success');
            loadData();
        } else {
            if (window.showToast) window.showToast(data.message || 'Error en DB.', 'error');
        }
    }

    function openModal(editMode, rowData = null) {
        isEditing = editMode;
        modalTitle.textContent = editMode ? 'Editar Registro' : 'Nuevo Registro';
        modalSub.textContent = `Entidad: ${currentEntity}`;

        // Known entities with string PK that MUST be entered manually
        const stringPkEntities = ['color', 'idioma', 'moneda', 'forma_pago', 'transportadora'];

        // Generate Form
        formFields.innerHTML = '';
        schemaCols.forEach((c, idx) => {
            const isPk = (idx === 0);
            
            // If it's PK, not in edit mode, and NOT a string PK entity, SKIP generating this input
            if (isPk && !editMode && !stringPkEntities.includes(currentEntity)) {
                return;
            }

            const val = rowData ? rowData[c] : '';
            
            let labelText = c.replace(/_/g, ' ');
            if (isPk && currentEntity === 'color') labelText = 'RGB (Hex)';

            const div = document.createElement('div');
            div.className = 'group';
            div.innerHTML = `
                <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-amber-500 transition-colors">
                    ${labelText}
                    ${isPk && editMode ? '<span class="text-rose-400">(Bloqueado)</span>' : ''}
                </label>
                <input type="${c.includes('pass') ? 'password' : (c.includes('mail') ? 'email' : 'text')}" 
                       name="${c}" 
                       value="${val}" 
                       ${isPk && editMode ? 'readonly' : ''}
                       class="w-full bg-black/20 border border-white/5 rounded-xl px-5 py-3.5 text-sm font-bold ${isPk && editMode ? 'text-slate-500 cursor-not-allowed' : 'text-white'} placeholder-slate-600 focus:border-amber-500/50 focus:ring-1 focus:ring-amber-500/50 focus:outline-none transition-all shadow-inner">
            `;
            formFields.appendChild(div);
        });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Small delay for transition
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalInner.classList.remove('scale-95');
        }, 10);
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        modalInner.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function showLoader(show) {
        if (show) {
            loader.classList.remove('hidden');
            tbTable.classList.add('hidden');
        } else {
            loader.classList.add('hidden');
            if (currentEntity) tbTable.classList.remove('hidden');
        }
    }
});
