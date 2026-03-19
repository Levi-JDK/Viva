import { AdminService } from '../services/AdminService.js';

export class AdminDashboardController {
    init() {
        // Expose global methods for inline HTML calls (legacy support)
        window.showPanel = this.showPanel.bind(this);
        window.toggleSidebar = this.toggleSidebar.bind(this);

        this.initPanels();
        this.initLogoFallback();
        this.initFormParameters();
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

    initFormParameters() {
        const formParametros = document.getElementById('form-parametros');
        if (formParametros) {
            formParametros.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btnSubmit = formParametros.querySelector('button[type="submit"]');
                const originalText = btnSubmit.innerHTML;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';
                btnSubmit.disabled = true;

                const formData = new FormData(formParametros);

                try {
                    const data = await AdminService.updateParameters(formData);
                    if (data.success) {
                        if (typeof showToast !== 'undefined') showToast(data.message, 'success');
                    } else {
                        if (typeof showToast !== 'undefined') showToast(data.message, 'error');
                    }
                } catch (error) {
                    console.error("Error al actualizar parámetros:", error);
                    if (typeof showToast !== 'undefined') showToast('Error de conexión con el servidor', 'error');
                } finally {
                    btnSubmit.innerHTML = originalText;
                    btnSubmit.disabled = false;
                }
            });
        }
    }
}
export const adminDashboardController = new AdminDashboardController();