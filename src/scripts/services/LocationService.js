export class LocationService {
    static async getCiudades(departamentoId) {
        const url = (typeof window.buildAppUrl === 'function')
            ? window.buildAppUrl(`api/ciudades?id_departamento=${departamentoId}`)
            : `${(window.BASE_URL || '').replace(/\/+$/, '')}/api/ciudades?id_departamento=${departamentoId}`;
        const response = await fetch(url);
        if (!response.ok) throw new Error(response.statusText);

        const data = await response.json();
        if (!data.success) throw new Error(data.mensaje || 'No se pudieron cargar las ciudades');
        return data.data;
    }
}
