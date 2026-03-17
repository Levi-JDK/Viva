<?php
require_once __DIR__ . '/src/functions/database.php';

try {
    $db = Database::getInstance();
    
    // Simulate updating client
    $stmt = $db->ejecutar('actualizarClienteEpayco', [
        ':id_user'     => 2,
        ':id_client'   => '123456789',
        ':id_tipo_doc' => 1,
        ':tel'         => '300000000',
        ':ref'         => 'VIVA-TEST',
        ':txn'         => 'TXN-TEST',
        ':banco'       => 'Banco Test',
        ':cod_resp'    => 1,
    ]);
    
    $res = $stmt->fetchColumn();
    echo "Update result: " . var_export($res, true) . "\n";
    
    // Check client row
    $q = $db->connection->query("SELECT * FROM tab_clientes WHERE id_user = 2");
    print_r($q->fetch(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
