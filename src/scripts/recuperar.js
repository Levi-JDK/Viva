'use strict';

// ── Mostrar / ocultar contraseña ─────────────────────────
window.togglePassword = function (inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
};

// ── Validación de contraseña (mismas reglas que el registro) ─
function validarContrasena(pass) {
    if (pass.length < 8) return 'La contraseña debe tener al menos 8 caracteres.';
    if (!/[A-Z]/.test(pass)) return 'La contraseña debe incluir al menos una mayúscula.';
    if (!/[a-z]/.test(pass)) return 'La contraseña debe incluir al menos una minúscula.';
    if (!/\d/.test(pass)) return 'La contraseña debe incluir al menos un número.';
    if (!/[!@#$%^&*(),.?":{}|<>]/.test(pass))
        return 'La contraseña debe incluir al menos un símbolo (!@#$%^&*...)';
    return '';
}

// ── Solo permitir dígitos en el input del código ─────────
document.addEventListener('DOMContentLoaded', () => {
    const tokenInput = document.getElementById('rec-token');
    if (!tokenInput) return;

    tokenInput.addEventListener('input', () => {
        tokenInput.value = tokenInput.value.replace(/\D/g, '').slice(0, 6);
    });
    tokenInput.addEventListener('keydown', (e) => {
        const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
        if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) {
            e.preventDefault();
        }
    });
});

// ── Paso 1: Solicitar código ─────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const formSolicitar = document.getElementById('form-solicitar');
    if (!formSolicitar) return;

    formSolicitar.addEventListener('submit', async function (e) {
        e.preventDefault();
        const email = document.getElementById('rec-email').value;
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Enviando...';

        try {
            const res = await fetch(BASE_URL + 'api/recuperar', {
                method: 'POST',
                body: new URLSearchParams({ accion: 'solicitar', email })
            });
            const data = await res.json();

            if (data.exito) {
                showToast('¡Código enviado! Revisa tu correo.', 'success');
                // Guardar email en el campo oculto y pasar al paso 2
                document.getElementById('rec-email-confirm').value = email;
                setTimeout(() => {
                    document.getElementById('paso-email').classList.add('hidden');
                    document.getElementById('paso-codigo').classList.remove('hidden');
                    document.getElementById('rec-token').focus();
                }, 800);
            } else {
                showToast(data.mensaje, 'error');
            }
        } catch (err) {
            showToast('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Enviar código';
        }
    });
});

// ── Paso 2: Confirmar código y nueva contraseña ──────────
document.addEventListener('DOMContentLoaded', () => {
    const formConfirmar = document.getElementById('form-confirmar');
    if (!formConfirmar) return;

    // Bloquear comillas en los campos de contraseña en tiempo real
    ['rec-pass', 'rec-pass-conf'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('input', () => {
            input.value = input.value.replace(/['"]/g, '');
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === '"' || e.key === "'") e.preventDefault();
        });
    });

    formConfirmar.addEventListener('submit', async function (e) {
        e.preventDefault();

        const passNueva = document.getElementById('rec-pass').value;
        const passConf = document.getElementById('rec-pass-conf').value;

        // Validar con la función local (mismas reglas que el registro)
        const errorPass = validarContrasena(passNueva);
        if (errorPass) {
            showToast(errorPass, 'error');
            return;
        }

        if (passNueva !== passConf) {
            showToast('Las contraseñas no coinciden', 'error');
            return;
        }

        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Actualizando...';

        // Construir body manualmente para incluir accion=confirmar
        const params = new URLSearchParams(new FormData(this));
        params.append('accion', 'confirmar');

        try {
            const res = await fetch(BASE_URL + 'api/recuperar', {
                method: 'POST',
                body: params
            });
            const data = await res.json();

            if (data.exito) {
                showToast(data.mensaje, 'success');
                setTimeout(() => { window.location.href = BASE_URL + 'login'; }, 1500);
            } else {
                showToast(data.mensaje, 'error');
                btn.disabled = false;
                btn.textContent = 'Cambiar contraseña';
            }
        } catch (err) {
            showToast('Error de conexión', 'error');
            btn.disabled = false;
            btn.textContent = 'Cambiar contraseña';
        }
    });
});


// ── Volver al paso 1 ─────────────────────────────────────
window.volverAlPaso1 = function () {
    document.getElementById('paso-codigo').classList.add('hidden');
    document.getElementById('paso-email').classList.remove('hidden');
};
