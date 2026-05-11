export class LocationService {
    static async getCiudades(departamentoId) {
        const baseUrl = (window.BASE_URL || '');
        const response = await fetch(`${baseUrl}/api/ciudades?id_departamento=${departamentoId}`);
        if (!response.ok) throw new Error(response.statusText);

        const data = await response.json();
        if (!data.exito) throw new Error(data.mensaje || 'No se pudieron cargar las ciudades');
        return data.data;
    }
}
