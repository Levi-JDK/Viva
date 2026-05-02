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
                item.classList.add('active-item', 'bg-orange-50', 'text-naranja-artesanal');
                item.classList.remove('text-gray-600');
            } else {
                item.classList.remove('active-item', 'bg-orange-50', 'text-naranja-artesanal');
                item.classList.add('text-gray-600');
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
                i.classList.remove('bg-white', 'text-gray-800', 'border-tierra-medio');
                i.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
            } else {
                i.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
                i.classList.add('bg-white', 'text-gray-800', 'border-tierra-medio');
            }
        });
        
        const btnEditar = document.getElementById('btn-editar');
        const saveCancelButtons = document.getElementById('save-cancel-buttons');
        
        if (btnEditar) btnEditar.classList.toggle('hidden');
        if (saveCancelButtons) saveCancelButtons.classList.toggle('hidden');
    }

    saveProfile() {
        // Mock save logic for now
        this.toggleEdit();
        if (typeof showToast !== 'undefined') {
            showToast('Perfil actualizado correctamente', 'success');
        } else if (window.Toast && typeof window.Toast.show === 'function') {
            window.Toast.show('Perfil actualizado correctamente', 'success');
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
