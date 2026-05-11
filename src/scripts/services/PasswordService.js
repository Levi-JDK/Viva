import { PasswordValidator } from '../domain/PasswordValidator.js';

export class PasswordService {
    static validateChange(currentPassword, newPassword, confirmPassword) {
        if (!currentPassword || !newPassword || !confirmPassword) {
            return 'Todos los campos son obligatorios.';
        }

        if (newPassword !== confirmPassword) {
            return 'Las contraseñas no coinciden.';
        }

        if (currentPassword === newPassword) {
            return 'La nueva contraseña debe ser diferente a la actual.';
        }

        return PasswordValidator.validate(newPassword);
    }

    static async cambiarContrasena(currentPassword, newPassword, confirmPassword) {
        const validationError = this.validateChange(currentPassword, newPassword, confirmPassword);
        if (validationError) {
            return { exito: false, mensaje: validationError };
        }

        const formData = new FormData();
        formData.append('accion', 'change_password');
        formData.append('current_password', currentPassword);
        formData.append('new_password', newPassword);
        formData.append('confirm_password', confirmPassword);

        const response = await fetch(window.buildAppUrl ? window.buildAppUrl('perfil') : `${window.BASE_URL}perfil`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        return {
            exito: response.ok && Boolean(data.exito),
            mensaje: data.mensaje || 'No se pudo cambiar la contraseña.',
        };
    }
}
