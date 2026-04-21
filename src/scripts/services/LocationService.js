export class LocationService {
    static async getCiudades(departamentoId) {
        const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
        const response = await fetch(`${baseUrl}ciudades?id_departamento=${departamentoId}`);
        if (!response.ok) throw new Error('Error en la red');
        const data = await response.json();
        if (!data.success) throw new Error('No success');
        return data.data;
    }
}