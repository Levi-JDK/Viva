import { PasswordRecoveryService } from '../services/PasswordRecoveryService.js';
import { PasswordValidator } from '../domain/PasswordValidator.js';

export class PasswordRecoveryController {
    init() {
        window.togglePassword = this.togglePassword.bind(this);
        window.volverAlPaso1 = this.volverAlPaso1.bind(this);
    }

    togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input && icon) {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    }

    volverAlPaso1() {
        const pasoCodigo = document.getElementById('paso-codigo');
        const pasoEmail = document.getElementById('paso-email');
        if (pasoCodigo && pasoEmail) {
            pasoCodigo.classList.add('hidden');
            pasoEmail.classList.remove('hidden');
        }
    }

    handleTokenInput(e, tokenInput) {
        tokenInput.value = tokenInput.value.replace(/\D/g, '').slice(0, 6);
    }

    handleTokenKeydown(e) {
        const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
        if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) {
            e.preventDefault();
        }
    }

    async submitSolicitar(e, formSolicitar) {
        e.preventDefault();
        const email = document.getElementById('rec-email').value;
        const btn = formSolicitar.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Enviando...';

        try {
            const data = await PasswordRecoveryService.solicitarCodigo(email);
            if (data.exito) {
                if (typeof showToast !== 'undefined') showToast('¡Código enviado! Revisa tu correo.', 'success');
                const recEmailConfirm = document.getElementById('rec-email-confirm');
                if (recEmailConfirm) recEmailConfirm.value = email;
                
                setTimeout(() => {
                    document.getElementById('paso-email').classList.add('hidden');
                    document.getElementById('paso-codigo').classList.remove('hidden');
                    document.getElementById('rec-token').focus();
                }, 800);
            } else {
                if (typeof showToast !== 'undefined') showToast(data.mensaje, 'error');
            }
        } catch (err) {
            if (typeof showToast !== 'undefined') showToast('Error de conexión', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Enviar código';
        }
    }

    handlePassInput(e, input) {
        input.value = input.value.replace(/['"]/g, '');
    }

    handlePassKeydown(e) {
        if (e.key === '"' || e.key === "'") e.preventDefault();
    }

    async submitConfirmar(e, formConfirmar) {
        e.preventDefault();
        const passNueva = document.getElementById('rec-pass').value;
        const passConf = document.getElementById('rec-pass-conf').value;

        const errorPass = PasswordValidator.validate(passNueva);
        if (errorPass) {
            if (typeof showToast !== 'undefined') showToast(errorPass, 'error');
            return;
        }

        if (passNueva !== passConf) {
            if (typeof showToast !== 'undefined') showToast('Las contraseñas no coinciden', 'error');
            return;
        }

        const btn = formConfirmar.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Actualizando...';

        try {
            const formData = new FormData(formConfirmar);
            const data = await PasswordRecoveryService.confirmarPassword(formData);

            if (data.exito) {
                if (typeof showToast !== 'undefined') showToast(data.mensaje, 'success');
                setTimeout(() => { window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'login'; }, 1500);
            } else {
                if (typeof showToast !== 'undefined') showToast(data.mensaje, 'error');
                btn.disabled = false;
                btn.textContent = 'Cambiar contraseña';
            }
        } catch (err) {
            if (typeof showToast !== 'undefined') showToast('Error de conexión', 'error');
            btn.disabled = false;
            btn.textContent = 'Cambiar contraseña';
        }
    }
}
export const passwordRecoveryController = new PasswordRecoveryController();