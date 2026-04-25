export class ProfileController {
    init() {
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
        if (sidebar) {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-0');
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