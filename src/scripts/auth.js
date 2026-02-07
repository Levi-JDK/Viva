document.addEventListener("DOMContentLoaded", function () {
    const formRegistro = document.getElementById("form-registro");
    const formLogin = document.getElementById("form-login");
    const container = document.getElementById('container');

    // Validación de contraseña
    function validarContrasena(contrasena) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(contrasena);
        const hasLowerCase = /[a-z]/.test(contrasena);
        const hasNumbers = /\d/.test(contrasena);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(contrasena);

        if (contrasena.length < minLength) {
            return "La contraseña debe tener al menos 8 caracteres.";
        }
        if (!hasUpperCase || !hasLowerCase || !hasNumbers || !hasSpecialChar) {
            return "La contraseña debe incluir mayúsculas, minúsculas, números y caracteres especiales.";
        }
        return "";
    }

    // Manejador de Registro
    async function handleRegister(e) {
        e.preventDefault();

        const contrasena = formRegistro.querySelector('input[name="contrasena"]').value;
        const errorContrasena = validarContrasena(contrasena);

        if (errorContrasena) {
            if (window.showToast) window.showToast(errorContrasena, 'error');
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
                setTimeout(() => {
                    window.location.href = BASE_URL;
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
