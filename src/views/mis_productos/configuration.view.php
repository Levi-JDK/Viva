<?php
$config_data = $config_data ?? [];
$configuracion = $config_data['configuracion'] ?? [];
$bancos = $config_data['bancos'] ?? [];
$departamentos = $config_data['departamentos'] ?? [];
$ciudades = $config_data['ciudades'] ?? [];
$grupos = $config_data['grupos'] ?? [];
$tipos_cuenta = $config_data['tipos_cuenta'] ?? ['Ahorros' => 1, 'Corriente' => 2];

$h = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$selected = static fn($actual, $valor): string => (string) ($actual ?? '') === (string) $valor ? 'selected' : '';
$tipoCuentaActual = (int) ($configuracion['tipo_cuenta'] ?? 0);
?>

<div class="max-w-3xl mx-auto">
    <h2 class="text-2xl font-bold text-white mb-6">Opciones de Vendedor</h2>

    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 space-y-8">
        <form id="vendor-config-form" class="space-y-8" method="POST" action="<?= BASE_URL ?>mis_productos?view=configuration" novalidate>
            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Datos del vendedor</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Tipo de documento</span>
                        <input type="text" value="<?= $h($configuracion['nom_tipo_doc'] ?? $configuracion['id_tipo_doc'] ?? '') ?>" readonly class="mt-1 w-full rounded-lg border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Número de documento</span>
                        <input type="text" value="<?= $h($configuracion['id_productor'] ?? '') ?>" readonly class="mt-1 w-full rounded-lg border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed">
                    </label>
                </div>
            </section>

            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Datos bancarios</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="block md:col-span-1">
                        <span class="text-sm font-medium text-gray-700">Banco *</span>
                        <select name="banco" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-naranja-artesanal focus:ring-naranja-artesanal">
                            <option value="">Seleccioná un banco</option>
                            <?php foreach ($bancos as $banco): ?>
                                <option value="<?= $h($banco['id'] ?? '') ?>" <?= $selected($configuracion['id_banco'] ?? null, $banco['id'] ?? null) ?>><?= $h($banco['nombre'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="block md:col-span-1">
                        <span class="text-sm font-medium text-gray-700">Tipo de cuenta *</span>
                        <select name="tipo_cuenta" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-naranja-artesanal focus:ring-naranja-artesanal">
                            <option value="">Seleccioná el tipo</option>
                            <?php foreach ($tipos_cuenta as $nombre => $valor): ?>
                                <option value="<?= $h($nombre) ?>" <?= $selected($tipoCuentaActual, $valor) ?>><?= $h($nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="block md:col-span-1">
                        <span class="text-sm font-medium text-gray-700">Número de cuenta *</span>
                        <input type="text" name="numero_cuenta" value="<?= $h($configuracion['id_cuenta_prod'] ?? '') ?>" inputmode="numeric" pattern="\d{1,12}" maxlength="12" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-naranja-artesanal focus:ring-naranja-artesanal" placeholder="Solo números">
                    </label>
                </div>
            </section>

            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Ubicación y grupo artesanal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="block md:col-span-2">
                        <span class="text-sm font-medium text-gray-700">Dirección *</span>
                        <input type="text" name="direccion" value="<?= $h($configuracion['dir_prod'] ?? '') ?>" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-naranja-artesanal focus:ring-naranja-artesanal" placeholder="Dirección de tu taller">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Departamento *</span>
                        <select id="departamento-config" name="departamento" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-naranja-artesanal focus:ring-naranja-artesanal">
                            <option value="">Seleccioná un departamento</option>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?= $h($departamento['id'] ?? '') ?>" <?= $selected($configuracion['id_departamento'] ?? null, $departamento['id'] ?? null) ?>><?= $h($departamento['nombre'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-gray-700">Ciudad *</span>
                        <select id="ciudad-config" name="ciudad" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-naranja-artesanal focus:ring-naranja-artesanal">
                            <option value="">Seleccioná una ciudad</option>
                            <?php foreach ($ciudades as $ciudad): ?>
                                <option value="<?= $h($ciudad['id'] ?? '') ?>" <?= $selected($configuracion['id_ciudad'] ?? null, $ciudad['id'] ?? null) ?>><?= $h($ciudad['nombre'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-sm font-medium text-gray-700">Grupo artesanal *</span>
                        <select name="grupo_artesanal" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-naranja-artesanal focus:ring-naranja-artesanal">
                            <option value="">Seleccioná un grupo</option>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?= $h($grupo['id'] ?? '') ?>" <?= $selected($configuracion['id_grupo'] ?? null, $grupo['id'] ?? null) ?>><?= $h($grupo['nombre'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </section>

            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Visibilidad de la tienda</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">Tienda Pública</p>
                        <p class="text-sm text-gray-500">Cambio visual solamente en esta versión; no se guarda en base de datos.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="tienda-publica-toggle" value="" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-naranja-artesanal"></div>
                    </label>
                </div>
            </section>

            <div class="pt-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-naranja-artesanal hover:bg-tierra-oscuro text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-sm">
                    Guardar configuración
                </button>
            </div>
        </form>

        <section class="pt-6 border-t border-gray-100">
            <h3 class="text-lg font-semibold text-red-600 mb-4">Zona de Peligro</h3>
            <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-100 gap-4">
                <div>
                    <p class="font-medium text-red-800">Desactivar cuenta de vendedor</p>
                    <p class="text-xs text-red-600">Esto ocultará todos tus productos y perfil.</p>
                </div>
                <button type="button" id="deactivate-vendor" class="border border-red-200 text-red-600 bg-white hover:bg-red-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    Desactivar
                </button>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('vendor-config-form');
    const departamento = document.getElementById('departamento-config');
    const ciudad = document.getElementById('ciudad-config');
    const deactivateButton = document.getElementById('deactivate-vendor');
    const endpoint = '<?= BASE_URL ?>mis_productos?view=configuration';

    const notify = (message, success = true) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: success ? 'success' : 'error',
                text: message,
                confirmButtonColor: success ? '#D97706' : '#DC2626'
            });
            return;
        }

        alert(message);
    };

    const postConfig = async (formData) => {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        return response.json();
    };

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        try {
            const result = await postConfig(new FormData(form));
            notify(result.message || 'Configuración actualizada.', Boolean(result.success));
        } catch (error) {
            notify('No se pudo guardar la configuración. Intentá nuevamente.', false);
        }
    });

    departamento?.addEventListener('change', async () => {
        ciudad.innerHTML = '<option value="">Cargando ciudades...</option>';

        if (!departamento.value) {
            ciudad.innerHTML = '<option value="">Seleccioná una ciudad</option>';
            return;
        }

        try {
            const response = await fetch(`<?= BASE_URL ?>ciudades?id_departamento=${encodeURIComponent(departamento.value)}`);
            const result = await response.json();
            const ciudades = Array.isArray(result.data) ? result.data : [];

            ciudad.innerHTML = '<option value="">Seleccioná una ciudad</option>';
            ciudades.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nombre;
                ciudad.appendChild(option);
            });
        } catch (error) {
            ciudad.innerHTML = '<option value="">No se pudieron cargar ciudades</option>';
        }
    });

    deactivateButton?.addEventListener('click', async () => {
        const confirmed = typeof Swal !== 'undefined'
            ? await Swal.fire({
                icon: 'warning',
                title: '¿Desactivar cuenta de vendedor?',
                text: 'Esta acción ocultará tu perfil de vendedor y tus productos.',
                showCancelButton: true,
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#DC2626'
            }).then((result) => result.isConfirmed)
            : confirm('¿Desactivar cuenta de vendedor?');

        if (!confirmed) {
            return;
        }

        const formData = new FormData();
        formData.append('accion', 'deactivate');

        try {
            const result = await postConfig(formData);

            if (result.success && result.redirect) {
                window.location.href = result.redirect;
                return;
            }

            notify(result.message || 'No se pudo desactivar la cuenta.', Boolean(result.success));
        } catch (error) {
            notify('No se pudo desactivar la cuenta. Intentá nuevamente.', false);
        }
    });
});
</script>
