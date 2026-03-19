export class ProfileController {
    init() {}

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
}

export const profileController = new ProfileController();