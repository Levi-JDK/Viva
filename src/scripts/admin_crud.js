/**
 * src/scripts/admin_crud.js
 * Controlador Vainilla para Gestor Dinámico de CRUD
 */

document.addEventListener('DOMContentLoaded', () => {
    const selEntity = document.getElementById('crud-entity-selector');
    const btnNew = document.getElementById('crud-btn-new');
    const tbEmpty = document.getElementById('crud-empty-state');
    const tbTable = document.getElementById('crud-table');
    const tHeadTr = document.getElementById('crud-thead-tr');
    const tBody = document.getElementById('crud-tbody');
    const loader = document.getElementById('crud-loader');
    
    // Modal
    const modal = document.getElementById('crud-modal');
    const modalInner = modal.querySelector('.max-w-lg');
    const btnClose = document.getElementById('crud-modal-close');
    const btnCancel = document.getElementById('crud-btn-cancel');
    const form = document.getElementById('crud-form');
    const formFields = document.getElementById('crud-form-fields');
    const modalTitle = document.getElementById('crud-modal-title');
    const modalSub = document.getElementById('crud-modal-subtitle');
    
    let currentEntity = null;
    let schemaCols = [];
    let isEditing = false;
    let currentId = null; 

    // ---- HANDLERS ----
    selEntity.addEventListener('change', async (e) => {
        currentEntity = e.target.value;
        if(!currentEntity) return;
        
        btnNew.disabled = false;
        tbEmpty.classList.add('hidden');
        tbTable.classList.remove('hidden');
        await loadData();
    });

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

            const res = await fetch('src/api/admin_crud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: urlEncoded.toString()
            });
            const data = await res.json();
            
            if (data.success && data.data) {
                if(window.showToast) window.showToast('Operación ' + (isEditing ? 'actualizada' : 'registrada') + ' exitosamente', 'success');
                closeModal();
                await loadData();
            } else {
                if(window.showToast) window.showToast(data.message || 'Error en validación DB.', 'error');
            }
        } catch(err) {
            console.error(err);
            if(window.Toast) Toast.fire({ icon: 'error', title: 'Error de red.'});
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
            const res = await fetch(`src/api/admin_crud.php?accion=read&entidad=${currentEntity}&_=${Date.now()}`);
            const data = await res.json();
            
            if (data.success) {
                schemaCols = data.data.columnas;
                renderTable(data.data.columnas, data.data.filas);
            } else {
                if(window.Toast) Toast.fire({ icon: 'error', title: data.message });
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
                if(val && val.length > 50) val = val.substring(0, 50) + '...';
                
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

    async function deleteRecord(id, fullRow) {
        if (!confirm('¿Estás seguro de eliminar lógicamente este registro?')) return;
        
        const urlEncoded = new URLSearchParams();
        urlEncoded.append('accion', 'delete');
        urlEncoded.append('entidad', currentEntity);
        // Enviamos el objeto entero para compound keys support nativo
        for(let k in fullRow) urlEncoded.append(k, fullRow[k]);
        
        const res = await fetch('src/api/admin_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: urlEncoded.toString()
        });
        const data = await res.json();
        if(data.success && data.data) {
            if(window.Toast) Toast.fire({ icon: 'success', title: 'Registro desactivado.'});
            loadData();
        } else {
            if(window.Toast) Toast.fire({ icon: 'error', title: data.message || 'Error en DB.'});
        }
    }

    function openModal(editMode, rowData = null) {
        isEditing = editMode;
        modalTitle.textContent = editMode ? 'Editar Registro' : 'Nuevo Registro';
        modalSub.textContent = `Entidad: ${currentEntity}`;
        
        // Generate Form
        formFields.innerHTML = '';
        schemaCols.forEach((c, idx) => {
            const isPk = (idx === 0);
            const val = rowData ? rowData[c] : '';
            
            const div = document.createElement('div');
            div.className = 'group';
            div.innerHTML = `
                <label class="block text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2.5 group-focus-within:text-amber-500 transition-colors">
                    ${c.replace(/_/g, ' ')}
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
        if(show) {
            loader.classList.remove('hidden');
            tbTable.classList.add('hidden');
        } else {
            loader.classList.add('hidden');
            if(currentEntity) tbTable.classList.remove('hidden');
        }
    }
});
