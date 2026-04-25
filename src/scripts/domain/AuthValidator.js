export class AuthValidator {
    static validatePassword(contrasena) {
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
        return '';
    }

    static validateName(nombre) {
        if (/[#*\-'"]/.test(nombre)) {
            return "El nombre no puede contener caracteres especiales (# * - ' \")";
        }
        return '';
    }

    static validateLastName(apellido) {
        if (/['"]/.test(apellido)) {
            return "El apellido no puede contener comillas (' \")";
        }
        return '';
    }
}