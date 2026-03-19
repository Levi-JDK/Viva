export class PasswordRecoveryService {
    static async solicitarCodigo(email) {
        const response = await fetch(BASE_URL + 'api/recuperar', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'solicitar', email })
        });
        return response.json();
    }

    static async confirmarPassword(formData) {
        const params = new URLSearchParams(formData);
        params.append('accion', 'confirmar');
        const response = await fetch(BASE_URL + 'api/recuperar', {
            method: 'POST',
            body: params
        });
        return response.json();
    }
}