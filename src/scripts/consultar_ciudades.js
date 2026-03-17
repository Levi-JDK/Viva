document.addEventListener('DOMContentLoaded', function () {
    const departamentoSelect = document.getElementById('departamento');
    const ciudadSelect = document.getElementById('ciudad');

    if (!departamentoSelect || !ciudadSelect) {
        console.error('No se encontraron los elementos select de departamento o ciudad');
        return;
    }

    departamentoSelect.addEventListener('change', function () {
        const departamentoId = this.value;

        // Limpiar select de ciudades
        ciudadSelect.innerHTML = '<option value="">Cargando...</option>';
        ciudadSelect.disabled = true;

        if (departamentoId) {
            // Realizar petición Fetch al backend
            // Usar BASE_URL si está definida, de lo contrario intentar ruta relativa
            const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';
            fetch(`${baseUrl}src/api/get_ciudades.php?id_departamento=${departamentoId}`)

                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la red');
                    }
                    return response.json();
                })
                .then(data => {
                    // Limpiar select
                    ciudadSelect.innerHTML = '<option value="">Seleccionar ciudad...</option>';

                    if (data.success && data.data.length > 0) {
                        // Llenar select con nuevas opciones
                        data.data.forEach(ciudad => {
                            const option = document.createElement('option');
                            option.value = ciudad.id;
                            option.textContent = ciudad.nombre;
                            ciudadSelect.appendChild(option);
                        });
                        ciudadSelect.disabled = false;
                    } else {
                        ciudadSelect.innerHTML = '<option value="">No hay ciudades disponibles</option>';
                    }
                })
                .catch(error => {
                    console.error('Error al cargar ciudades:', error);
                    ciudadSelect.innerHTML = '<option value="">Error al cargar</option>';
                });
        } else {
            // Si no hay departamento seleccionado
            ciudadSelect.innerHTML = '<option value="">Seleccionar departamento primero...</option>';
            ciudadSelect.disabled = true;
        }
    });
});
