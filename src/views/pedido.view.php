<?php 
$page_title = "Detalle del Pedido | VIVA";
$body_class = "bg-gray-50 font-sans antialiased";
require_once __DIR__ . '/partials/base_head.php'; 

$fecha_factura = new DateTime($pedido['fec_factura']);

// Determinar color de estado
$color_estado = 'bg-gray-100 text-gray-700';
if(strtolower($pedido['epayco_estado']) == 'aceptada') {
    $color_estado = 'bg-green-100 text-green-700';
} else if(strtolower($pedido['epayco_estado']) == 'pendiente') {
    $color_estado = 'bg-blue-100 text-blue-700';
} else if(strtolower($pedido['epayco_estado']) == 'rechazada' || strtolower($pedido['epayco_estado']) == 'fallida') {
    $color_estado = 'bg-red-100 text-red-700';
}
?>

    <?php require_once __DIR__ . '/partials/navbar.php'; ?>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 mt-16">
        
        <div class="mb-6 flex items-center space-x-4">
            <a href="<?= BASE_URL ?>perfil" class="text-naranja-artesanal hover:underline flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Mis Pedidos
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-gray-100 pb-6 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-tierra-oscuro">Pedido #<?= htmlspecialchars($pedido['id_factura']) ?></h1>
                    <p class="text-gray-500 mt-1">Realizado el <?= $fecha_factura->format('d \d\e M \d\e Y') ?></p>
                </div>
                <div class="mt-4 md:mt-0 text-right">
                    <span class="inline-block px-4 py-1.5 <?= $color_estado ?> font-semibold rounded-full text-sm">
                        Estado: <?= ucfirst(htmlspecialchars($pedido['epayco_estado'])) ?>
                    </span>
                    <p class="text-xs text-gray-400 mt-2">Ref pago: <?= htmlspecialchars($pedido['epayco_ref']) ?></p>
                </div>
            </div>

            <!-- Detalles resumidos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Información de Pago -->
                <div class="bg-gray-50 p-6 rounded-xl">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-wallet text-tierra-medio mr-2"></i> Información de Pago
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Método de Pago:</span>
                            <span class="font-medium text-gray-800 text-sm"><?= htmlspecialchars($pedido['nom_pago']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Transacción ID:</span>
                            <span class="font-medium text-gray-800 text-sm break-all"><?= htmlspecialchars($pedido['epayco_txn_id']) ?></span>
                        </div>
                        <div class="border-t border-gray-200 mt-3 pt-3 flex justify-between">
                            <span class="font-bold text-gray-800">Total Pagado:</span>
                            <span class="font-bold text-tierra-oscuro text-lg">$<?= number_format($pedido['val_tot_fact'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Envío opcional -->
                <div class="bg-gray-50 p-6 rounded-xl">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-truck text-tierra-medio mr-2"></i> Detalles de Envío
                    </h3>
                    <div class="text-sm text-gray-600 leading-relaxed text-balance">
                        <div class="font-bold text-gray-800 text-base mb-1">
                            <i class="fas fa-map-marker-alt text-tierra-medio mr-2"></i>
                            <?= htmlspecialchars($pedido['nom_ciudad']) ?>, <?= htmlspecialchars($pedido['nom_departamento']) ?>
                        </div>
                        <div class="ml-6 text-gray-600">
                            <?= htmlspecialchars($pedido['dir_envio']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Artículos comprados -->
            <h3 class="text-lg font-bold text-tierra-oscuro mb-4">Artículos Comprados</h3>
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <ul class="divide-y divide-gray-100">
                    <?php foreach ($detalles as $item): 
                        $img = !empty($item['imagen']) ? BASE_URL . $item['imagen'] : BASE_URL . 'images/default_product.png';
                    ?>
                    <li class="p-6 flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 hover:bg-gray-50 transition-colors">
                        <a href="<?= BASE_URL ?>producto?id=<?= $item['id_producto'] ?>" class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['nom_producto']) ?>" class="w-full h-full object-cover">
                        </a>
                        <div class="flex-1 text-center sm:text-left">
                            <a href="<?= BASE_URL ?>producto?id=<?= $item['id_producto'] ?>" class="font-bold text-gray-800 hover:text-naranja-artesanal transition-colors">
                                <?= htmlspecialchars($item['nom_producto']) ?>
                            </a>
                            <p class="text-sm text-gray-500 mt-1">
                                Cantidad: <span class="font-medium"><?= $item['val_cantidad'] ?></span>
                            </p>
                            <p class="text-lg font-bold text-tierra-oscuro mt-2">
                                $<?= number_format($item['val_neto'], 0, ',', '.') ?>
                            </p>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
        </div>
    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
