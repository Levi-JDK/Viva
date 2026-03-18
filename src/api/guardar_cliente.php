<?php

// src/api/guardar_cliente.php
// Endpoint POST: guarda o actualiza la dirección de envío del cliente en tab_clientes.
// Usa fun_c_cliente vía database.php. El botón de pago se habilita en el front tras éxito.

header('Content-Type: application/json');



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

require_once __DIR__ . '/../functions/auth_helper.php';
$userData = AuthHelper::protectRoute();
$id_user = $userData->id_user;

// --- Leer y validar campos ---
$id_departamento = trim($_POST['id_departamento'] ?? '');
$id_ciudad       = trim($_POST['id_ciudad']       ?? '');
$dir_envio       = trim($_POST['dir_envio']        ?? '');
$barrio_envio    = trim($_POST['barrio_envio']     ?? '');

$errores = [];
if (empty($id_departamento) || !is_numeric($id_departamento)) $errores[] = 'Selecciona un departamento.';
if (empty($id_ciudad)       || !is_numeric($id_ciudad))       $errores[] = 'Selecciona una ciudad.';
if (strlen($dir_envio) < 5)                                   $errores[] = 'La dirección debe tener al menos 5 caracteres.';

if (!empty($errores)) {
    http_response_code(422);
    echo json_encode(['exito' => false, 'mensaje' => implode(' ', $errores)]);
    exit;
}

try {
    require_once dirname(__DIR__) . '/functions/database.php';
    $db = Database::getInstance();

    // Obtener nombre y email del usuario para nom_client / mail_client
    $stmtUser = $db->ejecutar('obtenerUsuarioPorId', [':id' => $id_user]);
    $usuario  = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'Usuario no encontrado.']);
        exit;
    }

    $nom_client  = trim($usuario['nom_user'] . ' ' . ($usuario['ape_user'] ?? ''));
    $mail_client = $usuario['mail_user'];

    // Llamar a fun_c_cliente via statement preparado
    $stmt = $db->ejecutar('guardarCliente', [
        ':id_user'  => $id_user,
        ':id_client'=> (string) $id_user,   // placeholder hasta que ePayco entregue el documento real
        ':nom'      => $nom_client,
        ':mail'     => $mail_client,
        ':dpto'     => (int) $id_departamento,
        ':ciudad'   => (int) $id_ciudad,
        ':dir'      => $dir_envio,
        ':barrio'   => $barrio_envio ?: null,
    ]);

    $resultado = $stmt->fetchColumn(); // fun_c_cliente devuelve BOOLEAN

    if ($resultado) {
        echo json_encode(['exito' => true, 'mensaje' => 'Dirección guardada correctamente.']);
    } else {
        http_response_code(422);
        echo json_encode(['exito' => false, 'mensaje' => 'No se pudo guardar la dirección. Revisa que la ciudad sea válida.']);
    }

} catch (Exception $e) {
    error_log('[guardar_cliente] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al guardar la dirección.']);
}
