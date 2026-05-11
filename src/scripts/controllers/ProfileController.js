import { PasswordService } from '../services/PasswordService.js';

export class ProfileController {
    init() {
        this.handleClickOutside = this.handleClickOutside.bind(this);
        this.handleTouchStart = this.handleTouchStart.bind(this);
        this.handleTouchEnd = this.handleTouchEnd.bind(this);
        this.touchStartX = 0;
        this.touchStartY = 0;

        // Deshabilitamos la restauración automática de scroll del navegador.
        // Así no recuerda la posición de la página anterior ni scrollea al hash.
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        // Forzamos scroll arriba de todo, incluso si hay hash (#profile, #orders).
        // El navegador scrollea automáticamente al hash DESPUÉS de cargar,
        // así que usamos setTimeout para sobreescribir ese comportamiento.
        if (window.innerWidth < 1024) {
            const forceScrollTop = () => {
                window.scrollTo(0, 0);
                const mainContent = document.querySelector('main');
                if (mainContent) {
                    mainContent.scrollTop = 0;
                }
            };
            forceScrollTop();
            setTimeout(forceScrollTop, 0);
            setTimeout(forceScrollTop, 50);
        }

        this.handleHashChange();
        this.applyInitialTheme();
        window.addEventListener('hashchange', () => this.handleHashChange());
    }

    handleHashChange() {
        const hash = window.location.hash.slice(1);
        if (hash && hash !== 'profile') {
            this.showSection(hash);
        }
    }

    showSection(sectionId) {
        const sections = document.querySelectorAll('.content-section');
        const menuItems = document.querySelectorAll('.menu-item');

        sections.forEach(section => {
            if (section.id === sectionId) {
                section.classList.add('active');
                section.classList.remove('hidden');
            } else {
                section.classList.remove('active');
                section.classList.add('hidden');
            }
        });

        menuItems.forEach(item => {
            if (item.dataset.sectionId === sectionId) {
                item.classList.add('active-item', 'bg-orange-50', 'text-naranja-artesanal', 'dark:bg-stone-800');
                item.classList.remove('text-gray-600', 'dark:text-gray-300');
            } else {
                item.classList.remove('active-item', 'bg-orange-50', 'text-naranja-artesanal', 'dark:bg-stone-800');
                item.classList.add('text-gray-600', 'dark:text-gray-300');
            }
        });

        // En mobile el sidebar tapa el contenido; se cierra después de navegar.
        if (window.innerWidth < 1024) {
            this.closeSidebar();
        }
    }

    goBackSafely() {
        const baseUrl = window.BASE_URL || '/';
        let referrerUrl = null;

        try {
            referrerUrl = document.referrer ? new URL(document.referrer) : null;
        } catch (error) {
            referrerUrl = null;
        }

        // Evita sacar al usuario de Viva si llegó desde un sitio externo.
        if (referrerUrl && referrerUrl.origin === window.location.origin && window.history.length > 1) {
            window.history.back();
            return;
        }

        window.location.href = baseUrl;
    }

    toggleEdit() {
        const form = document.getElementById('profile-form');
        if (!form) return;
        
        const inputs = form.querySelectorAll('input.profile-input');
        inputs.forEach(i => {
            i.disabled = !i.disabled;
            if (i.disabled) {
                i.classList.remove('bg-white', 'text-gray-800', 'border-tierra-medio', 'dark:bg-stone-900', 'dark:text-gray-100');
                i.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'dark:bg-stone-800', 'dark:text-gray-400');
            } else {
                i.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'dark:bg-stone-800', 'dark:text-gray-400');
                i.classList.add('bg-white', 'text-gray-800', 'border-tierra-medio', 'dark:bg-stone-900', 'dark:text-gray-100');
            }
        });
        
        const btnEditar = document.getElementById('btn-editar');
        const saveCancelButtons = document.getElementById('save-cancel-buttons');
        
        if (btnEditar) btnEditar.classList.toggle('hidden');
        if (saveCancelButtons) saveCancelButtons.classList.toggle('hidden');
    }

    async saveProfile() {
        const nombre = document.getElementById('input-nombre')?.value.trim() || '';
        const apellido = document.getElementById('input-apellido')?.value.trim() || '';
        const button = document.querySelector('[data-action="save-profile"]');

        if (!nombre || !apellido) {
            this.showToast('El nombre y apellido son obligatorios.', 'error');
            return;
        }

        const originalHtml = button?.innerHTML;
        this.setButtonLoading(button, true, 'Guardando...');

        try {
            const formData = new FormData();
            formData.append('accion', 'update_profile');
            formData.append('nombre', nombre);
            formData.append('apellido', apellido);

            const response = await fetch(window.buildAppUrl ? window.buildAppUrl('perfil') : `${window.BASE_URL}perfil`, {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();
            const success = response.ok && (data.exito === true || data.clase === 'mensaje-exito');

            if (!success) {
                this.showToast(data.mensaje || 'No se pudo actualizar el perfil.', 'error');
                return;
            }

            this.toggleEdit();
            this.showToast(data.mensaje || 'Perfil actualizado correctamente.', 'success');
        } catch (error) {
            console.error('Profile save error:', error);
            this.showToast('Error al actualizar el perfil.', 'error');
        } finally {
            this.setButtonLoading(button, false, null, originalHtml);
        }
    }

    cancelEdit() {
        this.toggleEdit();
    }

    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        const isClosed = !sidebar.classList.contains('open');

        if (isClosed) {
            this.openSidebar();
        } else {
            this.closeSidebar();
        }
    }

    openSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('profileSidebarOverlay');
        if (!sidebar) return;

        sidebar.classList.add('open');
        if (overlay) {
            overlay.classList.remove('hidden');
        }
        this.addSidebarHandlers(sidebar);
    }

    closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('profileSidebarOverlay');
        if (!sidebar) return;

        sidebar.classList.remove('open');
        if (overlay) {
            overlay.classList.add('hidden');
        }

        this.removeSidebarHandlers(sidebar);
    }

    addSidebarHandlers(sidebar) {
        setTimeout(() => {
            document.addEventListener('click', this.handleClickOutside);
        }, 0);
        sidebar.addEventListener('touchstart', this.handleTouchStart, { passive: true });
        sidebar.addEventListener('touchend', this.handleTouchEnd, { passive: true });
    }

    removeSidebarHandlers(sidebar) {
        document.removeEventListener('click', this.handleClickOutside);
        sidebar.removeEventListener('touchstart', this.handleTouchStart);
        sidebar.removeEventListener('touchend', this.handleTouchEnd);
    }

    handleClickOutside(e) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('profileSidebarOverlay');
        const toggleButton = e.target.closest('[data-event="click:toggleSidebar"], [data-action="toggleSidebar"]');

        if (!sidebar || !sidebar.classList.contains('open')) return;
        if (sidebar.contains(e.target) || toggleButton) return;
        if (overlay && overlay.contains(e.target)) {
            this.closeSidebar();
            return;
        }
        if (!sidebar.contains(e.target)) {
            this.closeSidebar();
        }
    }

    handleTouchStart(e) {
        const touch = e.changedTouches[0];
        this.touchStartX = touch.clientX;
        this.touchStartY = touch.clientY;
    }

    handleTouchEnd(e) {
        const touch = e.changedTouches[0];
        const deltaX = touch.clientX - this.touchStartX;
        const deltaY = touch.clientY - this.touchStartY;

        if (deltaX < -50 && Math.abs(deltaY) < 50) {
            this.closeSidebar();
        }
    }

    triggerProfileUpload() {
        const input = document.getElementById('profile-image-input');
        if (input) {
            input.click();
        }
    }

    applyInitialTheme() {
        const toggle = document.getElementById('dark-mode-toggle');
        const theme = window.themePreference === 'dark' ? 'dark' : 'light';

        document.documentElement.classList.toggle('dark', theme === 'dark');
        if (toggle) {
            toggle.checked = theme === 'dark';
        }
    }

    async toggleTheme(toggle) {
        const theme = toggle?.checked ? 'dark' : 'light';
        const previousTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';

        document.documentElement.classList.toggle('dark', theme === 'dark');
        window.themePreference = theme;

        const formData = new FormData();
        formData.append('accion', 'update_theme');
        formData.append('theme', theme);

        try {
            const response = await fetch(window.buildAppUrl ? window.buildAppUrl('perfil') : `${window.BASE_URL}perfil`, {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (!response.ok || !data.exito) {
                throw new Error(data.mensaje || 'No se pudo actualizar el tema.');
            }

            this.showToast(data.mensaje || 'Tema actualizado.', 'success');
        } catch (error) {
            document.documentElement.classList.toggle('dark', previousTheme === 'dark');
            if (toggle) {
                toggle.checked = previousTheme === 'dark';
            }
            window.themePreference = previousTheme;
            this.showToast(error.message || 'No se pudo actualizar el tema.', 'error');
        }
    }

    openChangePasswordModal() {
        const modal = document.getElementById('password-modal');
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('current-password')?.focus();
    }

    closeChangePasswordModal() {
        const modal = document.getElementById('password-modal');
        const form = document.getElementById('password-change-form');
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form?.reset();
    }

    async submitPasswordChange(form) {
        const button = form?.querySelector('button[type="submit"]');
        const originalHtml = button?.innerHTML;
        const currentPassword = form?.querySelector('#current-password')?.value || '';
        const newPassword = form?.querySelector('#new-password')?.value || '';
        const confirmPassword = form?.querySelector('#confirm-password')?.value || '';

        this.setButtonLoading(button, true, 'Actualizando...');

        try {
            const result = await PasswordService.cambiarContrasena(currentPassword, newPassword, confirmPassword);
            if (!result.exito) {
                this.showToast(result.mensaje, 'error');
                return;
            }

            this.closeChangePasswordModal();
            this.showToast(result.mensaje, 'success');
        } catch (error) {
            console.error('Password change error:', error);
            this.showToast('No se pudo cambiar la contraseña.', 'error');
        } finally {
            this.setButtonLoading(button, false, null, originalHtml);
        }
    }

    setButtonLoading(button, isLoading, label = 'Procesando...', originalHtml = null) {
        if (!button) return;

        button.disabled = isLoading;
        if (isLoading) {
            button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${label}`;
            return;
        }

        if (originalHtml) {
            button.innerHTML = originalHtml;
        }
    }

    showToast(message, type = 'success') {
        if (typeof showToast !== 'undefined') {
            showToast(message, type);
            return;
        }

        if (window.Toast && typeof window.Toast.show === 'function') {
            window.Toast.show(message, type);
        }
    }

    async handleProfileUpload(e) {
        const input = document.getElementById('profile-image-input');
        if (!input || !input.files.length) return;

        const form = document.getElementById('profile-upload-form');
        if (!form) return;

        const formData = new FormData(form);

        try {
            const response = await fetch(window.BASE_URL + 'api/upload', {
                method: 'POST',
                body: formData
            });

            if (response.redirected || response.ok) {
                const url = response.url || window.BASE_URL + 'perfil?success=photo_updated';
                window.location.href = url.includes('success=') ? url : url + '?success=photo_updated#profile';
            } else {
                if (typeof showToast !== 'undefined') {
                    showToast('Error al subir la foto', 'error');
                }
            }
        } catch (error) {
            console.error('Upload error:', error);
            if (typeof showToast !== 'undefined') {
                showToast('Error al subir la foto', 'error');
            }
        }
    }
}

export const profileController = new ProfileController();
