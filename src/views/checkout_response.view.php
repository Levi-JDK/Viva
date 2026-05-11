<?php
$page_title = "Estado del Pago | VIVA";
$body_class = "bg-gray-50 font-sans antialiased min-h-screen flex flex-col";
require_once __DIR__ . '/partials/base_head.php';
?>

<!-- Navbar -->
<?php require_once __DIR__ . '/partials/navbar.php'; ?>

<main class="flex-grow container mx-auto px-4 py-8 mt-16 max-w-4xl flex items-center justify-center">

    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8 w-full text-center">

        <?php if ($error): ?>
            <!-- Error State -->
            <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-exclamation-triangle text-red-500 text-5xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Error al verificar el pago</h1>
            <p class="text-gray-600 mb-8"><?= htmlspecialchars($error) ?></p>

            <a href="<?= BASE_URL ?>catalogo" class="btn-primary text-white px-8 py-3 rounded-full font-medium shadow-md hover:shadow-lg transition-all inline-block">
                Volver al catálogo
            </a>

        <?php elseif ($transaccion): ?>

            <?php if ($transaccion['x_cod_response'] == 1): // Aceptada ?>
                <!-- Success State / VIVA Invoice -->
                <div class="w-24 h-24 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-2 border-4 border-white shadow-lg -mt-16">
                    <i class="fas fa-file-invoice-dollar text-green-500 text-5xl"></i>
                </div>
                <h1 class="text-3xl font-black text-tierra-oscuro mb-1 uppercase tracking-wide">Factura de Compra VIVA</h1>
                <p class="text-md text-gray-500 mb-4 font-mono">Orden #<?= htmlspecialchars($transaccion['x_id_invoice']) ?></p>

                <div class="bg-white rounded-xl text-left max-w-lg mx-auto mb-4 border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                        <span class="font-bold text-gray-700">Estado del pago</span>
                        <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full font-bold uppercase"><i class="fas fa-check-circle mr-1"></i> Aprobado</span>
                    </div>

                    <div class="p-4 space-y-2 text-sm text-gray-600">
                        <div class="flex justify-between border-b pb-1">
                            <span>Recibo Transaccional (ePayco):</span>
                            <span class="font-mono text-gray-800"><?= htmlspecialchars($transaccion['x_transaction_id']) ?></span>
                        </div>
                        <div class="flex justify-between border-b pb-1">
                            <span>Fecha de facturación:</span>
                            <span class="font-mono text-gray-800"><?= htmlspecialchars($transaccion['x_transaction_date']) ?></span>
                        </div>
                        <div class="flex justify-between border-b pb-1">
                            <span>Método de comprobación:</span>
                            <span class="font-mono text-gray-800">Tarjeta terminada en **** <?= substr($transaccion['x_cardnumber'] ?? '0000', -4) ?></span>
                        </div>

                        <div class="pt-2 flex justify-between items-end">
                            <span class="text-lg font-bold text-gray-800">Total Facturado</span>
                            <span class="text-3xl font-black text-naranja-artesanal">$<?= number_format($transaccion['x_amount'], 0, ',', '.') ?> <span class="text-sm font-normal text-gray-500 inline-block align-top mt-1"><?= $transaccion['x_currency_code'] ?></span></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($transaccion['envio_error']) || (($transaccion['envio_estado'] ?? null) === 'ERROR_ENVIO')): ?>
                <div class="bg-yellow-50 rounded-xl text-left max-w-lg mx-auto mb-4 border border-yellow-200 shadow-sm overflow-hidden p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-700 shrink-0">
                            <i class="fas fa-triangle-exclamation text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-yellow-900 text-md">Pago aprobado, envío pendiente</h3>
                            <p class="text-sm text-yellow-800">
                                <?= htmlspecialchars($transaccion['envio_error'] ?? 'No pudimos generar la guía de envío automáticamente. Nuestro equipo revisará el pedido.') ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($transaccion['num_guia'])): ?>
                <div class="bg-blue-50 rounded-xl text-left max-w-lg mx-auto mb-4 border border-blue-200 shadow-sm overflow-hidden p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 shrink-0">
                            <i class="fas fa-truck-fast text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 text-md">¡Tu pedido está en camino!</h3>
                            <p class="text-xs text-gray-600">Paquete generado con éxito en PuntoEnvio</p>
                        </div>
                    </div>
                    <div class="mt-3 bg-white rounded-lg border border-blue-100 p-3 flex justify-between items-center">
                        <span class="text-gray-500 text-xs font-medium">No. de Paquete:</span>
                        <span class="font-mono font-bold text-md text-blue-700"><?= htmlspecialchars($transaccion['num_guia']) ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <p class="text-sm text-gray-500 mb-6 max-w-md mx-auto"><i class="fas fa-info-circle mr-1 text-blue-400"></i> Tu pedido ha sido registrado en nuestro sistema. El carrito fue vaciado automáticamente.</p>

            <?php elseif ($transaccion['x_cod_response'] == 2): // Rechazada ?>
                <!-- Rejected State -->
                <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-lg -mt-16">
                    <i class="fas fa-times-circle text-red-500 text-5xl"></i>
                </div>
                <h1 class="text-3xl font-black text-gray-800 mb-2">Pago No Aprobado</h1>
                <p class="text-lg text-gray-600 mb-6">Tu banco ha declinado la transacción o hubo un problema con el método de pago en VIVA.</p>
                <div class="bg-red-50 text-red-700 p-4 rounded-lg text-sm max-w-sm mx-auto mb-8 font-mono border border-red-100">
                    Motivo: <?= htmlspecialchars($transaccion['x_response_reason_text']) ?>
                </div>
                <p class="text-sm text-gray-500 mb-8"><i class="fas fa-shopping-cart mx-1"></i> No te preocupes, <strong>tu carrito sigue intacto</strong>. Puedes intentar pagar de nuevo con otra tarjeta.</p>

            <?php else: // Pendiente o Fallida ?>
                <!-- Pending State -->
                <div class="w-24 h-24 bg-yellow-50 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-lg -mt-16">
                    <i class="fas fa-clock text-yellow-500 text-5xl"></i>
                </div>
                <h1 class="text-3xl font-black text-gray-800 mb-2">Transacción Pendiente</h1>
                <p class="text-lg text-gray-600 mb-8">Nuestros servidores están esperando confirmación del estado del pago. (Estado: <?= htmlspecialchars($transaccion['x_response']) ?>)</p>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if ($transaccion['x_cod_response'] != 1): ?>
                    <a href="<?= BASE_URL ?>checkout" class="bg-tierra-oscuro text-white px-8 py-3 rounded-full font-medium hover:bg-tierra-medio transition-all shadow-md">
                        Intentar de nuevo
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>catalogo" class="text-tierra-oscuro border border-tierra-oscuro px-8 py-3 rounded-full font-medium hover:bg-orange-50 transition-all">
                    Volver a comprar
                </a>
            </div>

        <?php endif; ?>

    </div>
</main>

<!-- Footer -->
<?php require_once __DIR__ . '/partials/footer.php'; ?>
</body>
</html>
