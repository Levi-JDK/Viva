export class PasswordValidator {
    static validate(pass) {
        if (pass.length < 8) return 'La contraseña debe tener al menos 8 caracteres.';
        if (!/[A-Z]/.test(pass)) return 'La contraseña debe incluir al menos una mayúscula.';
        if (!/[a-z]/.test(pass)) return 'La contraseña debe incluir al menos una minúscula.';
        if (!/\d/.test(pass)) return 'La contraseña debe incluir al menos un número.';
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(pass))
            return 'La contraseña debe incluir al menos un símbolo (!@#$%^&*...)';
        return '';
    }
}