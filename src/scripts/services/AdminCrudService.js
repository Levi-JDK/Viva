export class AdminCrudService {
    static async readEntity(entidad) {
        const response = await fetch(`${BASE_URL}/admin?ajax=1&accion=read&entidad=${encodeURIComponent(entidad)}&_=${Date.now()}`);
        if (!response.ok) throw new Error('Failed to read CRUD entity');
        return response.json();
    }

    static async writeEntity(accion, entidad, datos) {
        const hasFile = Object.values(datos).some((value) => value instanceof File && value.name);
        const payload = hasFile ? new FormData() : new URLSearchParams();
        payload.append('accion', accion);
        payload.append('entidad', entidad);

        for (const [key, value] of Object.entries(datos)) {
            if (value instanceof File) {
                if (value.name) payload.append(key, value);
                continue;
            }

            payload.append(key, value ?? '');
        }

        const response = await fetch(`${BASE_URL}/admin`, {
            method: 'POST',
            headers: hasFile ? {} : { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: hasFile ? payload : payload.toString()
        });

        if (!response.ok) throw new Error('Failed to persist CRUD entity');
        return response.json();
    }

    static async deleteEntity(entidad, rowData) {
        return this.writeEntity('delete', entidad, rowData);
    }
}
