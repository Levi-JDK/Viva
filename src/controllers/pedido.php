<?php
require_once __DIR__ . '/../functions/auth_helper.php';
$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

require_once __DIR__ . '/../functions/database.php';
$id_factura = $_GET['id'] ?? null;

if (!$id_factura || !is_numeric($id_factura)) {
    // Si no manda ID válido, regresemos a perfil
    header('Location: ' . BASE_URL . 'mi-cuenta');
    exit;
}

try {
    $db = Database::getInstance();

    // 1. Obtener datos de la factura (solo si pertenece a este usuario)
    $stmtEnc = $db->ejecutar('obtenerFacturaPorId', [
        ':id_factura' => $id_factura,
        ':id_user' => $id_user
    ]);
    
    $pedido = $stmtEnc->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        // La factura no existe o no le pertenece
        header('Location: ' . BASE_URL . 'mi-cuenta');
        exit;
    }

    // 2. Obtener detalles de productos comprados en esta factura
    $stmtDet = $db->ejecutar('obtenerDetallesFactura', [':id_factura' => $id_factura]);
    $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error cargando detalle del pedido: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'mi-cuenta');
    exit;
}

require_once ROOT_PATH . 'src/views/pedido.view.php';
?>
