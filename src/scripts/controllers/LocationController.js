import { LocationService } from '../services/LocationService.js';

export class LocationController {
    init() {
        // Inicializamos referencias si existen en el DOM al cargar
        this.ciudadSelect = document.getElementById('ciudad');
    }

    async handleDepartamentoChange(departamentoSelect) {
        if (!this.ciudadSelect) {
            this.ciudadSelect = document.getElementById('ciudad');
        }
        
        if (!this.ciudadSelect) return;

        const departamentoId = departamentoSelect.value;
        this.ciudadSelect.innerHTML = '<option value="">Cargando...</option>';
        this.ciudadSelect.disabled = true;

        if (departamentoId) {
            try {
                const ciudades = await LocationService.getCiudades(departamentoId);
                this.ciudadSelect.innerHTML = '<option value="">Seleccionar ciudad...</option>';
                if (ciudades && ciudades.length > 0) {
                    ciudades.forEach(ciudad => {
                        const option = document.createElement('option');
                        option.value = ciudad.id;
                        option.textContent = ciudad.nombre;
                        this.ciudadSelect.appendChild(option);
                    });
                    this.ciudadSelect.disabled = false;
                } else {
                    this.ciudadSelect.innerHTML = '<option value="">No hay ciudades disponibles</option>';
                }
            } catch (error) {
                console.error('Error al cargar ciudades:', error);
                this.ciudadSelect.innerHTML = '<option value="">Error al cargar</option>';
            }
        } else {
            this.ciudadSelect.innerHTML = '<option value="">Seleccionar departamento primero...</option>';
            this.ciudadSelect.disabled = true;
        }
    }
}
export const locationController = new LocationController();