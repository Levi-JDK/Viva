// Validación compartida de contraseña (global para reutilizar en recuperar.js, etc.)
window.validarContrasena = function (contrasena) {
    if (contrasena.length < 8) {
        return 'La contraseña debe tener al menos 8 caracteres.';
    }
    if (!/[A-Z]/.test(contrasena) || !/[a-z]/.test(contrasena)) {
        return 'La contraseña debe incluir mayúsculas y minúsculas.';
    }
    if (!/\d/.test(contrasena)) {
        return 'La contraseña debe incluir al menos un número.';
    }
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(contrasena)) {
        return 'La contraseña debe incluir al menos un símbolo (!@#$%^&*...)';
    }
    return ''; // válida
};

document.addEventListener("DOMContentLoaded", function () {
    const formRegistro = document.getElementById("form-registro");
    const formLogin = document.getElementById("form-login");
    const container = document.getElementById('container');

    // Manejador de Registro
    async function handleRegister(e) {
        e.preventDefault();

        const contrasena = formRegistro.querySelector('input[name="contrasena"]').value;
        const errorContrasena = validarContrasena(contrasena);

        if (errorContrasena) {
            if (window.showToast) window.showToast(errorContrasena, 'error');
            return;
        }

        const nombre = formRegistro.querySelector('input[name="nombre"]').value;
        const apellido = formRegistro.querySelector('input[name="apellido"]').value;

        // Validar nombre (# * - ' ")
        if (/[#*\-'"]/.test(nombre)) {
            if (window.showToast) window.showToast("El nombre no puede contener caracteres especiales (# * - ' \")", 'error');
            return;
        }

        // Validar apellido (' ")
        if (/['"]/.test(apellido)) {
            if (window.showToast) window.showToast("El apellido no puede contener comillas (' \")", 'error');
            return;
        }

        const formData = new FormData(formRegistro);
        formData.append('accion', 'registro');

        if (window.showToast) window.showToast("Procesando registro...", "info");

        try {
            const response = await fetch(BASE_URL + 'src/functions/auth_controller.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            const type = data.clase === 'mensaje-exito' ? 'success' : 'error';

            if (window.showToast) window.showToast(data.mensaje, type);

            if (data.clase === 'mensaje-exito') {
                formRegistro.reset();
                // Cambiar a login automáticamente
                setTimeout(() => {
                    if (window.innerWidth > 768) {
                        if (container) container.classList.remove("right-panel-active");
                    } else {
                        const signInMobileBtn = document.getElementById('signIn-mobile');
                        if (signInMobileBtn) signInMobileBtn.click();
                    }
                }, 1500);
            }
        } catch (error) {
            console.error('Error:', error);
            if (window.showToast) window.showToast("Error en la conexión con el servidor", "error");
        }
    }

    // Manejador de Login
    async function handleLogin(e) {
        e.preventDefault();

        const formData = new FormData(formLogin);
        formData.append('accion', 'login');

        // Leer el redirect desde el campo hidden del form (inyectado por PHP) o de la URL como fallback
        const hiddenRedirect = formLogin.querySelector('input[name="redirect"]')?.value || '';
        const urlRedirect = new URLSearchParams(window.location.search).get('redirect') || '';
        const redirectUrl = hiddenRedirect || urlRedirect;

        if (window.showToast) window.showToast("Verificando credenciales...", "info");

        try {
            const response = await fetch(BASE_URL + 'src/functions/auth_controller.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            const type = data.clase === 'mensaje-exito' ? 'success' : 'error';

            if (window.showToast) window.showToast(data.mensaje, type);

            if (data.clase === 'mensaje-exito') {
                formLogin.reset();
                // Usar el redirect capturado de la URL, o la home como fallback
                const destino = redirectUrl || BASE_URL;
                console.log('[Auth] Login exitoso. Redirect destino:', destino);
                setTimeout(() => {
                    window.location.href = destino;
                }, 800);
            }
        } catch (error) {
            console.error('Error:', error);
            if (window.showToast) window.showToast("Error en la conexión con el servidor", "error");
        }
    }

    // Asignar eventos
    if (formRegistro) {
        formRegistro.addEventListener("submit", handleRegister);
    }

    if (formLogin) {
        formLogin.addEventListener("submit", handleLogin);
    }
});
