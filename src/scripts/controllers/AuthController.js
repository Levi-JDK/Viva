import { Toast } from '../ui/Toast.js';
import { AuthValidator } from '../domain/AuthValidator.js';
import { ApiService } from '../services/ApiService.js';
import { loginUIController } from './LoginUIController.js';

export class AuthController {
    constructor() {
        this.formRegistro = document.getElementById("form-registro");
        this.formLogin = document.getElementById("form-login");
        this.bindEvents();
    }

    bindEvents() {
        if (this.formRegistro) {
            this.formRegistro.addEventListener("submit", this.handleRegister.bind(this));
        }

        if (this.formLogin) {
            this.formLogin.addEventListener("submit", this.handleLogin.bind(this));
        }
    }

    async handleRegister(e) {
        e.preventDefault();

        const contrasena = this.formRegistro.querySelector('input[name="contrasena"]').value;
        const errorContrasena = AuthValidator.validatePassword(contrasena);

        if (errorContrasena) {
            Toast.show(errorContrasena, 'error');
            return;
        }

        const nombre = this.formRegistro.querySelector('input[name="nombre"]').value;
        const apellido = this.formRegistro.querySelector('input[name="apellido"]').value;

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

        const formData = new FormData(this.formRegistro);
        formData.append('accion', 'registro');

        try {
            const data = await ApiService.post(BASE_URL + 'src/functions/auth_controller.php', formData);
            const type = data.clase === 'mensaje-exito' ? 'success' : 'error';

            Toast.show(data.mensaje, type);

            if (data.clase === 'mensaje-exito') {
                this.formRegistro.reset();
                
                // Deslizamiento automático hacia el login después del registro usando el controlador centralizado
                setTimeout(() => {
                    loginUIController.showSignIn();
                }, 1500); // Esperar 1.5s para que vea el Toast verde
            }
        } catch (error) {
            Toast.show("Error en la conexión con el servidor", "error");
        }
    }

    async handleLogin(e) {
        e.preventDefault();

        const formData = new FormData(this.formLogin);
        formData.append('accion', 'login');

        const hiddenRedirect = this.formLogin.querySelector('input[name="redirect"]')?.value || '';
        const urlRedirect = new URLSearchParams(window.location.search).get('redirect') || '';
        const redirectUrl = hiddenRedirect || urlRedirect;

        try {
            const data = await ApiService.post(BASE_URL + 'src/functions/auth_controller.php', formData);
            const type = data.clase === 'mensaje-exito' ? 'success' : 'error';

            Toast.show(data.mensaje, type);

            if (data.clase === 'mensaje-exito') {
                this.formLogin.reset();
                const destino = redirectUrl || BASE_URL;
                console.log('[Auth] Login exitoso. Redirect destino:', destino);
                setTimeout(() => {
                    window.location.href = destino;
                }, 800);
            }
        } catch (error) {
            Toast.show("Error en la conexión con el servidor", "error");
        }
    }
}