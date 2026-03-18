<?php


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once dirname(__DIR__, 2) . '/src/functions/auth_helper.php';
$userData = AuthHelper::protectRoute();

// Cargar Database
require_once dirname(__DIR__, 2) . '/src/functions/database.php';

$params = [];

try {
    $db = Database::getInstance();
    $id_user = $userData->id_user;
    
    // 1. Recoger datos del formulario
    $tipo_doc = $_POST['tipo_documento'] ?? null;
    $num_doc  = $_POST['numero_documento'] ?? null;
    $direccion = $_POST['direccion'] ?? null;
    $dpto     = $_POST['departamento'] ?? null;
    $ciudad   = $_POST['ciudad'] ?? null;
    $grupo    = $_POST['grupo_artesanal'] ?? null;
    $banco    = $_POST['banco'] ?? null;
    $num_cuenta = $_POST['numero_cuenta'] ?? null;
    $tipo_cuenta_str = $_POST['tipo_cuenta'] ?? 'Ahorros';

    // Validaciones básicas
    if (!$tipo_doc || !$num_doc || !$direccion || !$dpto || !$ciudad || !$grupo || !$banco || !$num_cuenta) {
        throw new Exception("Todos los campos marcados con * son obligatorios.");
    }

    // Convertir datos
    $tipo_cuenta = ($tipo_cuenta_str === 'Ahorros') ? 1 : 2;

    // Limpiar caracteres no numéricos para campos que deben ser DECIMAL
    $num_doc = preg_replace('/\D/', '', $num_doc);
    $num_cuenta = preg_replace('/\D/', '', $num_cuenta);

    // 2. Llamar a fun_c_productor
    $params = [
        ':tipo_doc' => (int)$tipo_doc,
        ':id_prod'  => $num_doc,   // Se envía como string para soportar BIGINT/NUMERIC sin desbordamiento
        ':id_user'  => (int)$id_user,
        ':dir'      => $direccion,
        ':pais'     => 1,
        ':dpto'     => (int)$dpto,
        ':ciudad'   => (int)$ciudad,
        ':grupo'    => (int)$grupo,
        ':banco'    => (int)$banco,
        ':cuenta'   => $num_cuenta, // Se envía como string para evitar desbordamiento de entero en PHP
        ':tipo_cuenta' => (int)$tipo_cuenta
    ];

    try {
        $stmt = $db->ejecutar('crearProductor', $params);
        $result = $stmt->fetchColumn(); 

        if ($result) {
            // Asignar los módulos de productor (Mis Productos)
            $db->ejecutar('asignarMenuUsuario', [':id_user' => (int)$id_user, ':id_menu' => 10]);
            
            // Revocar el módulo de "Vender en VIVA"
            $db->ejecutar('revocarMenuUsuario', [':id_user' => (int)$id_user, ':id_menu' => 9]);
        }
    } catch (PDOException $ex) {
        // Loguear error real si falla SQL
        error_log("Error SQL Registro Vendedor: " . $ex->getMessage());
        // DEBUG: Lanzar excepción real para que el usuario la vea
        throw new Exception("Error SQL: " . $ex->getMessage());
    }

    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => '¡Registro exitoso! Bienvenido a VIVA.',
            // Mantenemos debug_params por si acaso el usuario quiere validar
            'debug_params' => $params 
        ]);
    } else {
        // La función retornó FALSE (validación fallida)
        echo json_encode([
            'success' => false, 
            'message' => 'No se pudo completar el registro. Verifique sus datos (ej. documento o usuario ya registrado).',
            'debug_params' => $params 
        ]);
    }

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'debug_params' => isset($params) ? $params : null
    ]);
}
