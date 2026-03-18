<?php
$page_title = "Finalizar Compra | VIVA";
$body_class = "bg-gray-50 font-sans antialiased min-h-screen flex flex-col";
require_once __DIR__ . '/partials/base_head.php';
?>

<!-- Navbar -->
<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<!-- ePayco Script -->
<script type="text/javascript" src="https://checkout.epayco.co/checkout.js"></script>

<main class="flex-grow container mx-auto px-4 py-8 mt-16 max-w-5xl">
    
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-tierra-oscuro">Resumen de tu pedido</h1>
        <p class="text-gray-600">Revisa los productos y completa la dirección de envío antes de pagar.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Columna izquierda: productos + formulario -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Productos del carrito -->
            <?php foreach ($carrito_items as $item): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex gap-4 items-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                        <?php if(!empty($item['imagen'])): ?>
                            <img src="<?= BASE_URL . $item['imagen'] ?>" alt="<?= htmlspecialchars($item['nom_producto']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-image text-2xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow">
                        <h3 class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($item['nom_producto']) ?></h3>
                        <p class="text-sm text-gray-500">Cantidad: <?= $item['cantidad'] ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-tierra-oscuro text-xl">$<?= number_format($item['subtotal'], 0, ',', '.') ?></p>
                        <p class="text-xs text-gray-400">$<?= number_format($item['precio_unitario'], 0, ',', '.') ?> c/u</p>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- ── FORMULARIO DE DIRECCIÓN DE ENVÍO ── -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-5 border-b pb-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                        <?= $direccion_guardada ? 'bg-green-100 text-green-600' : 'bg-orange-100 text-naranja-artesanal' ?>">
                        <i class="fas <?= $direccion_guardada ? 'fa-check' : 'fa-map-marker-alt' ?> text-sm"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Dirección de envío</h2>
                    <?php if ($direccion_guardada): ?>
                        <span class="ml-auto text-xs text-green-600 font-medium bg-green-50 px-2 py-1 rounded-full">
                            <i class="fas fa-check-circle mr-1"></i>Guardada
                        </span>
                    <?php endif; ?>
                </div>

                <form id="form-envio" class="space-y-4" novalidate method="post">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Departamento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Departamento <span class="text-red-500">*</span>
                            </label>
                            <select id="departamento" name="id_departamento" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-naranja-artesanal focus:border-transparent text-sm">
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($departamentos as $dpto): ?>
                                    <option value="<?= $dpto['id'] ?>"
                                        <?= ($cliente_envio && $cliente_envio['id_departamento'] == $dpto['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dpto['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Ciudad (cargada via AJAX) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Ciudad / Municipio <span class="text-red-500">*</span>
                            </label>
                            <select id="ciudad" name="id_ciudad" required disabled
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-naranja-artesanal focus:border-transparent text-sm disabled:bg-gray-50 disabled:text-gray-400">
                                <option value="">-- Selecciona un departamento primero --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Dirección <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="input-dir" name="dir_envio" required minlength="5"
                            value="<?= htmlspecialchars($cliente_envio['dir_envio'] ?? '') ?>"
                            placeholder="Ej: Calle 10 # 5-20 Apto 301"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-naranja-artesanal focus:border-transparent text-sm">
                    </div>

                    <!-- Barrio (opcional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Barrio <span class="text-gray-400 font-normal">(opcional)</span>
                        </label>
                        <input type="text" id="input-barrio" name="barrio_envio"
                            value="<?= htmlspecialchars($cliente_envio['barrio_envio'] ?? '') ?>"
                            placeholder="Ej: El Poblado"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-naranja-artesanal focus:border-transparent text-sm">
                    </div>

                    <!-- Mensaje de error/éxito -->
                    <div id="msg-envio" class="hidden text-sm rounded-lg px-4 py-3"></div>

                    <!-- Botón guardar -->
                    <button type="submit" id="btn-guardar-dir"
                        class="w-full sm:w-auto bg-gray-800 hover:bg-gray-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-all text-sm flex items-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Guardar dirección</span>
                    </button>
                </form>
            </div>
            <!-- ── FIN FORMULARIO ── -->

        </div>

        <!-- Columna derecha: resumen y pago -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sticky top-24">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-4">Total a Pagar</h2>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal (<?= $resumen['total_items'] ?> items)</span>
                        <span>$<?= number_format($resumen['total_precio'], 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Envío estimado</span>
                        <span class="text-green-600">¡Gratis!</span>
                    </div>
                </div>
                
                <div class="border-t pt-4 mb-8">
                    <div class="flex justify-between items-end">
                        <span class="font-bold text-gray-800">Total</span>
                        <span class="font-black text-3xl text-tierra-oscuro">$<?= number_format($resumen['total_precio'], 0, ',', '.') ?> <span class="text-sm font-normal text-gray-500">COP</span></span>
                    </div>
                </div>

                <!-- Tooltip cuando está deshabilitado -->
                <div id="tooltip-pagar" class="<?= $direccion_guardada ? 'hidden' : '' ?> text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-3 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    Completa y guarda la dirección de envío para continuar.
                </div>

                <button id="btn-pagar"
                    <?= !$direccion_guardada ? 'disabled' : '' ?>
                    class="w-full font-bold py-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 text-lg
                        <?= $direccion_guardada 
                            ? 'bg-naranja-artesanal hover:bg-orange-600 text-white cursor-pointer' 
                            : 'bg-gray-200 text-gray-400 cursor-not-allowed' ?>">
                    <i class="fas fa-lock"></i> Pagar Seguro
                </button>
                
                <div class="mt-4 text-center">
                    <img src="https://multimedia.epayco.co/epayco-landing/btns/Boton-epayco-color1.png" alt="Pagos Seguros con ePayco" class="h-8 mx-auto opacity-70">
                </div>
            </div>
        </div>
        
    </div>
</main>

<!-- Footer -->
<?php require_once __DIR__ . '/partials/footer.php'; ?>

<!-- Initializing ePayco Checkout -->
<script>
// BASE_URL ya definida en base_head.php


// ── ePayco handler ──────────────────────────────────────────────
var handler = ePayco.checkout.configure({
    key: '<?= $epayco_public_key ?>',
    test: true
});

var data = {
    name:         "Compra en Artesanías VIVA",
    description:  "Pedido <?= $referencia_pago ?> con <?= $resumen['total_items'] ?> productos",
    invoice:      "<?= $referencia_pago ?>",
    currency:     "cop",
    amount:       "<?= $resumen['total_precio'] ?>",
    tax_base:     "0",
    tax:          "0",
    country:      "co",
    lang:         "es",
    external:     "false",
    // ePayco rechaza "localhost" en su validador de URL, usamos 127.0.0.1 como fallback
    response:     "<?= str_replace('localhost', '127.0.0.1', BASE_URL) ?>checkout/respuesta",
    confirmation: "<?= str_replace('localhost', '127.0.0.1', BASE_URL) ?>api/epayco_webhook",
    name_billing: "<?= $_SESSION['nom_user'] ?? 'Cliente VIVA' ?>",
    email_billing:"<?= $_SESSION['mail_user'] ?? '' ?>"
};

document.getElementById('btn-pagar').addEventListener('click', function () {
    if (!this.disabled) handler.open(data);
});

// ── Dirección de envío ───────────────────────────────────────────
const selectCiudad = document.getElementById('ciudad');
const inputDir     = document.getElementById('input-dir');
const btnPagar     = document.getElementById('btn-pagar');
const tooltip      = document.getElementById('tooltip-pagar');
const msgEnvio     = document.getElementById('msg-envio');

// Si hay departamento pre-seleccionado (dirección guardada), cargar su ciudad
<?php if ($cliente_envio): ?>
(function () {
    // consultar_ciudades.js maneja el cambio de departamento.
    // Aquí solo cargamos las ciudades del dpto guardado y pre-seleccionamos la ciudad.
    const idCiudadGuardada = <?= json_encode((string)$cliente_envio['id_ciudad']) ?>;
    fetch(`${BASE_URL}api/get_ciudades?id_departamento=<?= $cliente_envio['id_departamento'] ?>`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                selectCiudad.innerHTML = '<option value="">Seleccionar ciudad...</option>';
                res.data.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.nombre;
                    if (opt.value === idCiudadGuardada) opt.selected = true;
                    selectCiudad.appendChild(opt);
                });
                selectCiudad.disabled = false;
            }
        });
})();
<?php endif; ?>

// ── Guardar dirección (sin recargar la página) ──────────────────
document.getElementById('form-envio').addEventListener('submit', async function (e) {
    e.preventDefault(); // evita el reload

    const dpto   = document.getElementById('departamento').value;
    const ciudad = selectCiudad.value;
    const dir    = inputDir.value.trim();

    if (!dpto || !ciudad || dir.length < 5) {
        mostrarMsg('Completa todos los campos obligatorios (departamento, ciudad y dirección).', false);
        return;
    }

    const btn = document.getElementById('btn-guardar-dir');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
        const res  = await fetch(`${BASE_URL}api/guardar_cliente`, { method: 'POST', body: new FormData(this) });
        const json = await res.json();

        if (json.exito) {
            mostrarMsg('✔ ' + json.mensaje, true);
            habilitarPago();
        } else {
            mostrarMsg(json.mensaje, false);
        }
    } catch {
        mostrarMsg('Error de conexión. Inténtalo de nuevo.', false);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar dirección';
    }
});

function mostrarMsg(texto, exito) {
    msgEnvio.textContent = texto;
    msgEnvio.className = `text-sm rounded-lg px-4 py-3 ${
        exito ? 'bg-green-50 text-green-700 border border-green-200'
              : 'bg-red-50 text-red-700 border border-red-200'
    }`;
    msgEnvio.classList.remove('hidden');
}

function habilitarPago() {
    btnPagar.disabled = false;
    btnPagar.className = 'w-full bg-naranja-artesanal hover:bg-orange-600 text-white font-bold py-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 text-lg cursor-pointer';
    tooltip.classList.add('hidden');
}
</script>

<!-- Reutilizamos el script existente que maneja el cambio de departamento → ciudades -->
<script src="<?= BASE_URL ?>src/scripts/consultar_ciudades.js"></script>

</body>
</html>
