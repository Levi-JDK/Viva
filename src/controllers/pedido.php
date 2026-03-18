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
    $stmtEnc = $db->connection->prepare("
        SELECT 
            f.id_factura,
            f.fec_factura,
            f.val_hora_fact,
            f.val_tot_fact,
            f.epayco_estado,
            f.epayco_ref,
            f.epayco_txn_id,
            f.dir_envio,
            p.nom_pago,
            dep.nom_departamento,
            ciu.nom_ciudad
        FROM tab_enc_fact f
        JOIN tab_clientes c ON f.id_client = c.id_client
        JOIN tab_formas_pago p ON f.id_pago = p.id_pago
        LEFT JOIN tab_departamentos dep ON f.id_pais = dep.id_pais AND f.id_departamento = dep.id_departamento
        LEFT JOIN tab_ciudades ciu ON f.id_pais = ciu.id_pais AND f.id_departamento = ciu.id_departamento AND f.id_ciudad = ciu.id_ciudad
        WHERE f.id_factura = :id_factura AND c.id_user = :id_user
    ");
    $stmtEnc->execute([
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
    $stmtDet = $db->connection->prepare("
        SELECT 
            d.val_cantidad,
            d.val_neto,
            prod.nom_producto,
            prod.id_producto,
            (SELECT url_imagen FROM tab_imagenes i WHERE i.id_producto = prod.id_producto ORDER BY id_imagen ASC LIMIT 1) as imagen
        FROM tab_det_fact d
        JOIN tab_productos prod ON d.id_producto = prod.id_producto
        WHERE d.id_factura = :id_factura
    ");
    $stmtDet->execute([':id_factura' => $id_factura]);
    $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error cargando detalle del pedido: " . $e->getMessage());
    header('Location: ' . BASE_URL . 'mi-cuenta');
    exit;
}

require_once ROOT_PATH . 'src/views/pedido.view.php';
?>
