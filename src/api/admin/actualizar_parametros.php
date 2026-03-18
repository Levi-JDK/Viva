<?php
/**
 * src/api/admin/actualizar_parametros.php
 * API endpoint para actualizar los parámetros globales del sistema y la Landing Page.
 * Recibe datos vía POST desde admin_dashboard.js y retorna JSON.
 */

require_once __DIR__ . '/../../functions/auth_helper.php';
require_once __DIR__ . '/../../functions/database.php';

header('Content-Type: application/json');

// Forzar respuesta JSON en caso de que protectRoute detecte expiración
$_SERVER['HTTP_ACCEPT'] = 'application/json';

// Validar que el usuario sea Admin (Nivel 1)
try {
    AuthHelper::checkAccess(1); // Esta función valida la sesión y los permisos y corta el script automáticamente devolviendo 401/403 de JSON si falla.
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión expirada o inválida.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

try {
    $db = Database::getInstance();

    // ID del registro de parámetros (Por defecto 1)
    $id_parametro = 1; 

    // Obtener campos globales
    $nom_plataforma  = $_POST['nom_plataforma']  ?? null;
    $dir_contacto    = $_POST['dir_contacto']    ?? null;
    $correo_contacto = $_POST['correo_contacto'] ?? null;
    $val_inifact     = isset($_POST['val_inifact']) ? (int)$_POST['val_inifact']   : null;
    $val_finfact     = isset($_POST['val_finfact']) ? (int)$_POST['val_finfact']   : null;
    $val_actfact     = isset($_POST['val_actfact']) ? (int)$_POST['val_actfact']   : null;
    $val_observa     = $_POST['val_observa']     ?? null;

    // Obtener campos de landing page
    $landing_hero_titulo     = $_POST['landing_hero_titulo']     ?? null;
    $landing_hero_subtitulo  = $_POST['landing_hero_subtitulo']  ?? null;
    $landing_hero_btn        = $_POST['landing_hero_btn']        ?? null;
    $landing_conf_1_tit      = $_POST['landing_conf_1_tit']      ?? null;
    $landing_conf_1_sub      = $_POST['landing_conf_1_sub']      ?? null;
    $landing_conf_2_tit      = $_POST['landing_conf_2_tit']      ?? null;
    $landing_conf_2_sub      = $_POST['landing_conf_2_sub']      ?? null;
    $landing_conf_3_tit      = $_POST['landing_conf_3_tit']      ?? null;
    $landing_conf_3_sub      = $_POST['landing_conf_3_sub']      ?? null;
    $landing_filosofia_tit   = $_POST['landing_filosofia_tit']   ?? null;
    $landing_filosofia_p1    = $_POST['landing_filosofia_p1']    ?? null;
    $landing_filosofia_p2    = $_POST['landing_filosofia_p2']    ?? null;


    // Ejecutar actualización
    $stmt = $db->ejecutar('actualizarParametrosGlob', [
        ':id_parametro'            => $id_parametro,
        ':nom_plataforma'          => $nom_plataforma,
        ':dir_contacto'            => $dir_contacto,
        ':correo_contacto'         => $correo_contacto,
        ':val_inifact'             => $val_inifact,
        ':val_finfact'             => $val_finfact,
        ':val_actfact'             => $val_actfact,
        ':val_observa'             => $val_observa,
        ':landing_hero_titulo'     => $landing_hero_titulo,
        ':landing_hero_subtitulo'  => $landing_hero_subtitulo,
        ':landing_hero_btn'        => $landing_hero_btn,
        ':landing_conf_1_tit'      => $landing_conf_1_tit,
        ':landing_conf_1_sub'      => $landing_conf_1_sub,
        ':landing_conf_2_tit'      => $landing_conf_2_tit,
        ':landing_conf_2_sub'      => $landing_conf_2_sub,
        ':landing_conf_3_tit'      => $landing_conf_3_tit,
        ':landing_conf_3_sub'      => $landing_conf_3_sub,
        ':landing_filosofia_tit'   => $landing_filosofia_tit,
        ':landing_filosofia_p1'    => $landing_filosofia_p1,
        ':landing_filosofia_p2'    => $landing_filosofia_p2
    ]);

    $resultado = $stmt->fetchColumn();

    if ($resultado) {
        echo json_encode(['success' => true, 'message' => 'Parámetros actualizados exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: los datos son inválidos o no hubo cambios.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
