<?php

require_once __DIR__ . '/../functions/auth_helper.php';
$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

// Este controlador SOLO se encarga de cargar los datos necesarios para la vista (GET)
// NO procesa el formulario de registro. Eso lo hace src/api/post_registro_vendedor.php
require_once(__DIR__ . '/../functions/database.php');


try {
    // Usar Singleton pattern
    $db = Database::getInstance();
    
    // Verificar si ya es vendedor
    $params = [':id_user' => $id_user];
    $stmt = $db->ejecutar('validarProductor', $params);
    $es_productor = $stmt->fetchColumn();

    if ($es_productor) {
        header('Location: ' . BASE_URL . 'mis_productos');
        exit();
    }
    
    // Ejecutar consulta preparada
    $stmt = $db->ejecutar('obtenerTiposDocumento');
    $tipos_doc = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db->ejecutar('obtenerDepartamentos');
    $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db->ejecutar('obtenerGrupos');
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $db->ejecutar('obtenerBancos');
    $bancos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit;
}

require_once ROOT_PATH . "src/views/registro_vendedor.view.php";
?>
