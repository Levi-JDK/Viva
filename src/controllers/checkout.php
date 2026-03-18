<?php
// src/controllers/checkout.php
// Controlador para la pantalla de resumen web de compra e integracion ePayco

// Sesión iniciada centralmente en index.php

// 1. Validar autenticación
require_once __DIR__ . '/../functions/auth_helper.php';
$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

require_once __DIR__ . '/../functions/database.php';

try {
    $db = Database::getInstance();

    // 2. Obtener el carrito actual del usuario usando la función PL/pgSQL
    $params = [
        ':id_user'     => $id_user,
        ':accion'      => 'obtener',
        ':id_producto' => null,
        ':cantidad'    => null,
    ];

    $stmt = $db->ejecutar('gestionarCarrito', $params);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    // La función devuelve una columna 'fun_carrito' en formato JSON
    $resultado = json_decode($fila['fun_carrito'] ?? $fila[array_key_first($fila)], true);

    if (!$resultado['exito'] || empty($resultado['carrito'])) {
        // Redirigir al catálogo si el carrito está vacío
        header('Location: ' . BASE_URL . 'catalogo');
        exit;
    }

    $carrito_items = $resultado['carrito'];
    $resumen = $resultado['resumen']; // Tiene total_items y total_precio
    
    // 3. Cargar departamentos para el select del formulario de envío
    $stmt_dptos     = $db->ejecutar('obtenerDepartamentos');
    $departamentos  = $stmt_dptos->fetchAll(PDO::FETCH_ASSOC);

    // 4. Cargar dirección de envío preexistente del cliente (si ya compró antes)
    $cliente_envio = null;
    try {
        $stmtCliente   = $db->ejecutar('obtenerDireccionCliente', [':id_user' => $id_user]);
        $cliente_envio = $stmtCliente->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        error_log('[Checkout] Error cargando dirección previa: ' . $e->getMessage());
    }
    $direccion_guardada = !empty($cliente_envio);

    // 5. Obtener llave pública de ePayco del .env
    $epayco_public_key = $_ENV['EPAYCO_PUBLIC_KEY'] ?? '';

    // Generar una referencia única para esta transacción
    $referencia_pago = 'VIVA-' . time() . '-' . $id_user;

    // Cargar la vista de checkout pasándole los datos
    require_once ROOT_PATH . 'src/views/checkout.view.php';

} catch (Exception $e) {
    error_log('[Checkout] Error: ' . $e->getMessage());
    echo "Ocurrió un error al procesar tu solicitud. Por favor intenta de nuevo.";
}
?>
