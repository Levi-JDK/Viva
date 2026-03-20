/**
 * AdminMenusController.js
 * Panel de Gestión de Menús en el Admin Dashboard.
 * Permite asignar/revocar accesos a menús por usuario.
 * El usuario se selecciona SIEMPRE desde el panel de Usuarios (botón "Menú").
 */

const BASE = window.BASE_URL || '';

async function fetchJSON(accion, params = {}) {
    const body = new URLSearchParams({ accion, ...params });
    const res = await fetch(`${BASE}api/admin_crud`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    });
    return res.json();
}

// ── State ───────────────────────────────────────────────────
let selectedUserId = null;
let selectedUserName = '';

// ── DOM Refs ────────────────────────────────────────────────
let menusGrid, menusPlaceholder, menusLoader, userLabel, backBtn;

function ensureDom() {
    menusGrid = document.getElementById('menus-grid');
    menusPlaceholder = document.getElementById('menus-placeholder');
    menusLoader = document.getElementById('menus-loader');
    userLabel = document.getElementById('menus-usuario-nombre');
    backBtn = document.getElementById('menus-back-usuarios');
}

// ── Helpers ─────────────────────────────────────────────────
function showLoader(show) {
    if (!menusLoader) return;
    menusLoader.classList.toggle('hidden', !show);
    menusLoader.classList.toggle('flex', show);
}

function renderMenuCards(menus) {
    if (!menusGrid) return;
    menusGrid.innerHTML = '';
    menusGrid.classList.remove('hidden');
    menus.forEach(m => {
        const active = m.tiene_acceso === true || m.tiene_acceso === 't' || m.tiene_acceso === '1' || m.tiene_acceso === 'true';
        const card = document.createElement('div');
        card.className = `relative group rounded-2xl p-6 border transition-all duration-300 cursor-pointer select-none
            ${active
                ? 'bg-violet-500/10 border-violet-500/30 shadow-[0_0_20px_rgba(139,92,246,0.1)]'
                : 'bg-white/[0.02] border-white/5 hover:border-white/10'}`;
        card.dataset.menuId = m.id_menu;
        card.dataset.active = active ? 'true' : 'false';

        card.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center
                    ${active ? 'bg-violet-500/20 text-violet-400' : 'bg-white/5 text-slate-500'}
                    transition-colors duration-300">
                    <i class="${m.icono_menu || 'fas fa-circle'} text-base"></i>
                </div>
                <div class="relative">
                    <div class="w-11 h-6 rounded-full transition-all duration-300
                        ${active ? 'bg-violet-500' : 'bg-slate-700'} shadow-inner">
                        <div class="absolute top-1 transition-all duration-300
                            ${active ? 'left-6 bg-white' : 'left-1 bg-slate-400'}
                            w-4 h-4 rounded-full shadow-sm"></div>
                    </div>
                </div>
            </div>
            <p class="font-bold text-sm ${active ? 'text-white' : 'text-slate-400'} transition-colors">${m.nom_menu}</p>
            <p class="text-[10px] mt-1 font-semibold tracking-widest uppercase ${active ? 'text-violet-400' : 'text-slate-600'}">
                ${active ? 'Acceso activo' : 'Sin acceso'}
            </p>
        `;

        card.addEventListener('click', () => toggleMenu(card, m));
        menusGrid.appendChild(card);
    });
}

async function toggleMenu(card, menu) {
    const currentlyActive = card.dataset.active === 'true';
    const accion = currentlyActive ? 'revoke_menu' : 'assign_menu';

    try {
        const res = await fetchJSON(accion, { id_user: selectedUserId, id_menu: menu.id_menu });
        if (res.success) {
            await loadUserMenus(selectedUserId);
            if (window.Toast) Toast.fire({
                icon: 'success',
                title: !currentlyActive ? `"${menu.nom_menu}" asignado` : `"${menu.nom_menu}" revocado`
            });
        } else {
            if (window.Toast) Toast.fire({ icon: 'error', title: res.message || 'Error al cambiar acceso' });
        }
    } catch (err) {
        console.error('[AdminMenusController] toggleMenu error:', err);
    }
}

async function loadUserMenus(id_user) {
    showLoader(true);
    if (menusPlaceholder) menusPlaceholder.classList.add('hidden');
    try {
        const res = await fetchJSON('list_user_menus', { id_user });
        if (res.success && Array.isArray(res.data)) {
            renderMenuCards(res.data);
        }
    } catch (err) {
        console.error('[AdminMenusController] loadUserMenus error:', err);
    } finally {
        showLoader(false);
    }
}

function showNoUserState() {
    if (menusPlaceholder) {
        menusPlaceholder.innerHTML = `
            <div class="flex flex-col items-center justify-center h-64 text-slate-500 gap-6">
                <i class="fas fa-user-cog text-5xl opacity-30"></i>
                <p class="font-medium text-sm">Debes elegir un usuario primero</p>
                <button id="btn-ir-usuarios"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-sky-500/10 hover:bg-sky-500 text-sky-400 hover:text-white border border-sky-500/30 text-xs font-bold tracking-widest uppercase transition-all duration-200">
                    <i class="fas fa-users text-xs"></i>
                    <span>Ir a Gestión de Usuarios</span>
                </button>
            </div>
        `;
        menusPlaceholder.classList.remove('hidden');
        document.getElementById('btn-ir-usuarios')?.addEventListener('click', goToUsuarios);
    }
    if (menusGrid) menusGrid.classList.add('hidden');
    if (userLabel) userLabel.textContent = 'Selecciona un usuario desde el panel de Usuarios.';
}

function goToUsuarios() {
    import('../controllers/AdminDashboardController.js').then(m => {
        m.adminDashboardController.showPanel('usuarios');
    });
}

// ── Controller ──────────────────────────────────────────────
export const adminMenusController = {
    init() {
        ensureDom();
        if (!menusGrid) return; // Not on dashboard page

        backBtn?.addEventListener('click', goToUsuarios);
    },

    onPanelShow() {
        ensureDom();
        if (!selectedUserId) {
            showNoUserState();
        } else {
            loadUserMenus(selectedUserId);
        }
    },

    // Called from AdminDashboardController.handleGestionMenu
    selectUser(id, name) {
        selectedUserId = id;
        selectedUserName = name;
        setTimeout(() => {
            ensureDom();
            if (userLabel) userLabel.textContent = `Permisos de: ${name}`;
            loadUserMenus(id);
        }, 50);
    }
};
