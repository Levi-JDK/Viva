import { Toast } from '../ui/Toast.js';
import { AuthValidator } from '../domain/AuthValidator.js';
import { ApiService } from '../services/ApiService.js';

export class AuthController {
    async handleRegister(form) {
        const contrasena = form.querySelector('input[name="contrasena"]').value;
        const errorContrasena = AuthValidator.validatePassword(contrasena);

        if (errorContrasena) {
            Toast.show(errorContrasena, 'error');
            return;
        }

        const nombre = form.querySelector('input[name="nombre"]').value;
        const apellido = form.querySelector('input[name="apellido"]').value;

        const errorNombre = AuthValidator.validateName(nombre);
        if (errorNombre) {
            Toast.show(errorNombre, 'error');
            return;
        }

        const errorApellido = AuthValidator.validateLastName(apellido);
        if (errorApellido) {
            Toast.show(errorApellido, 'error');
            return;
        }

        const formData = new FormData(form);
        formData.append('accion', 'registro');

        try {
            const data = await ApiService.post(BASE_URL + 'src/functions/auth_controller.php', formData);
            const type = data.clase === 'mensaje-exito' ? 'success' : 'error';

            Toast.show(data.mensaje, type);

            if (data.clase === 'mensaje-exito') {
                form.reset();
                
                // Redirigir al login después del registro
                setTimeout(() => {
                    window.location.href = BASE_URL + 'login';
                }, 1500); // Esperar 1.5s para que vea el Toast verde
            }
        } catch (error) {
            Toast.show("Error en la conexión con el servidor", "error");
        }
    }

    async handleLogin(form) {
        const formData = new FormData(form);
        formData.append('accion', 'login');

        const hiddenRedirect = form.querySelector('input[name="redirect"]')?.value || '';
        const urlRedirect = new URLSearchParams(window.location.search).get('redirect') || '';
        const redirectUrl = hiddenRedirect || urlRedirect;

        try {
            const data = await ApiService.post(BASE_URL + 'src/functions/auth_controller.php', formData);
            const type = data.clase === 'mensaje-exito' ? 'success' : 'error';

            Toast.show(data.mensaje, type);

            if (data.clase === 'mensaje-exito') {
                form.reset();
                const destino = redirectUrl || BASE_URL;

                setTimeout(() => {
                    window.location.href = destino;
                }, 800);
            }
        } catch (error) {
            Toast.show("Error en la conexión con el servidor", "error");
        }
    }
}

export const authController = new AuthController();