<?php
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/database.php';
require_once __DIR__ . '/../utils/image_uploader.php';

AuthHelper::checkAccess(8);

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $isParametrosUpdate = isset($_POST['nom_plataforma'])
        || isset($_POST['dir_contacto'])
        || isset($_POST['correo_contacto'])
        || isset($_POST['val_inifact'])
        || isset($_POST['val_finfact'])
        || isset($_POST['val_actfact'])
        || isset($_POST['val_observa'])
        || isset($_POST['landing_hero_titulo'])
        || isset($_POST['landing_hero_subtitulo'])
        || isset($_POST['landing_hero_btn'])
        || isset($_POST['landing_conf_1_tit'])
        || isset($_POST['landing_conf_1_sub'])
        || isset($_POST['landing_conf_2_tit'])
        || isset($_POST['landing_conf_2_sub'])
        || isset($_POST['landing_conf_3_tit'])
        || isset($_POST['landing_conf_3_sub'])
        || isset($_POST['landing_filosofia_tit'])
        || isset($_POST['landing_filosofia_p1'])
        || isset($_POST['landing_filosofia_p2'])
        || isset($_FILES['foto_hero']);

    try {
        $db = Database::getInstance();

        if ($isParametrosUpdate && ($_POST['entidad'] ?? 'parametros') === 'parametros') {
            AuthHelper::checkAccess(1);

            $id_parametro = 1;

            $nom_plataforma  = $_POST['nom_plataforma']  ?? null;
            $dir_contacto    = $_POST['dir_contacto']    ?? null;
            $correo_contacto = $_POST['correo_contacto'] ?? null;
            $val_inifact     = isset($_POST['val_inifact']) ? (int) $_POST['val_inifact'] : null;
            $val_finfact     = isset($_POST['val_finfact']) ? (int) $_POST['val_finfact'] : null;
            $val_actfact     = isset($_POST['val_actfact']) ? (int) $_POST['val_actfact'] : null;
            $val_observa     = $_POST['val_observa'] ?? null;
            $foto_hero       = $_POST['foto_hero'] ?? null;
            if (isset($_POST['remove_foto_hero']) && $_POST['remove_foto_hero'] === '1') {
                $foto_hero = 'images/hero.jpeg';
            }

            $landing_hero_titulo    = $_POST['landing_hero_titulo'] ?? null;
            $landing_hero_subtitulo  = $_POST['landing_hero_subtitulo'] ?? null;
            $landing_hero_btn       = $_POST['landing_hero_btn'] ?? null;
            $landing_conf_1_tit     = $_POST['landing_conf_1_tit'] ?? null;
            $landing_conf_1_sub     = $_POST['landing_conf_1_sub'] ?? null;
            $landing_conf_2_tit     = $_POST['landing_conf_2_tit'] ?? null;
            $landing_conf_2_sub     = $_POST['landing_conf_2_sub'] ?? null;
            $landing_conf_3_tit     = $_POST['landing_conf_3_tit'] ?? null;
            $landing_conf_3_sub     = $_POST['landing_conf_3_sub'] ?? null;
            $landing_filosofia_tit  = $_POST['landing_filosofia_tit'] ?? null;
            $landing_filosofia_p1   = $_POST['landing_filosofia_p1'] ?? null;
            $landing_filosofia_p2   = $_POST['landing_filosofia_p2'] ?? null;

            if (isset($_FILES['foto_hero']) && ($_FILES['foto_hero']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $upload = handleImageUpload($_FILES['foto_hero'], __DIR__ . '/../../images/landing/', 'landing_hero_', 'images/landing/');
                if (!$upload['success']) {
                    echo json_encode(['success' => false, 'message' => $upload['message'] ?? 'Error al subir la imagen hero.']);
                    exit;
                }

                $foto_hero = $upload['path'] ?? ($upload['paths'][0] ?? null);
            }

            $stmt = $db->ejecutar('actualizarParametrosGlob', [
                ':id_parametro'           => $id_parametro,
                ':nom_plataforma'         => $nom_plataforma,
                ':dir_contacto'           => $dir_contacto,
                ':correo_contacto'        => $correo_contacto,
                ':val_inifact'            => $val_inifact,
                ':val_finfact'            => $val_finfact,
                ':val_actfact'            => $val_actfact,
                ':val_observa'            => $val_observa,
                ':foto_hero'              => $foto_hero,
                ':landing_hero_titulo'    => $landing_hero_titulo,
                ':landing_hero_subtitulo' => $landing_hero_subtitulo,
                ':landing_hero_btn'       => $landing_hero_btn,
                ':landing_conf_1_tit'     => $landing_conf_1_tit,
                ':landing_conf_1_sub'     => $landing_conf_1_sub,
                ':landing_conf_2_tit'     => $landing_conf_2_tit,
                ':landing_conf_2_sub'     => $landing_conf_2_sub,
                ':landing_conf_3_tit'     => $landing_conf_3_tit,
                ':landing_conf_3_sub'     => $landing_conf_3_sub,
                ':landing_filosofia_tit'  => $landing_filosofia_tit,
                ':landing_filosofia_p1'   => $landing_filosofia_p1,
                ':landing_filosofia_p2'   => $landing_filosofia_p2
            ]);

            $resultado = $stmt->fetchColumn();

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Parámetros actualizados exitosamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar: los datos son inválidos o no hubo cambios.']);
            }

            exit;
        }

        $accion = $_REQUEST['accion'] ?? '';
        $entidad = $_REQUEST['entidad'] ?? '';

        if ($accion === 'list_users') {
            $stmt = $db->ejecutar('listarUsuariosAdmin');
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            exit;
        }

        if ($accion === 'list_products') {
            $stmt = $db->ejecutar('listarProductosAdmin');
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            exit;
        }

        if ($accion === 'toggle_user') {
            $id_user = (int) ($_REQUEST['id_user'] ?? 0);
            $is_active = $_REQUEST['is_active'] ?? 'true';

            if (!$id_user) {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido.']);
                exit;
            }

            $db->ejecutar('toggleUsuarioActivo', [
                ':id_user' => $id_user,
                ':is_deleted' => ($is_active === 'false')
            ]);

            echo json_encode(['success' => true, 'message' => 'Estado del usuario actualizado.']);
            exit;
        }

        if ($accion === 'toggle_product') {
            $id_producto = (int) ($_REQUEST['id_producto'] ?? 0);
            $is_active = $_REQUEST['is_active'] ?? 'true';

            if (!$id_producto) {
                echo json_encode(['success' => false, 'message' => 'ID de producto requerido.']);
                exit;
            }

            $db->ejecutar('toggleProductoActivo', [
                ':id_producto' => $id_producto,
                ':is_deleted' => ($is_active === 'false')
            ]);

            echo json_encode(['success' => true, 'message' => 'Estado del producto actualizado.']);
            exit;
        }

        if ($accion === 'list_user_menus') {
            $id_user = (int) ($_REQUEST['id_user'] ?? 0);
            if (!$id_user) {
                echo json_encode(['success' => false, 'message' => 'ID requerido.']);
                exit;
            }

            $stmt = $db->ejecutar('listarMenusUsuario', [':id_user' => $id_user]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            exit;
        }

        if ($accion === 'assign_menu') {
            $id_user = (int) ($_REQUEST['id_user'] ?? 0);
            $id_menu = (int) ($_REQUEST['id_menu'] ?? 0);

            if (!$id_user || !$id_menu) {
                echo json_encode(['success' => false, 'message' => 'Parámetros requeridos.']);
                exit;
            }

            $stmt = $db->ejecutar('asignarMenuUsuario', [':id_user' => $id_user, ':id_menu' => $id_menu]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchColumn()]);
            exit;
        }

        if ($accion === 'revoke_menu') {
            $id_user = (int) ($_REQUEST['id_user'] ?? 0);
            $id_menu = (int) ($_REQUEST['id_menu'] ?? 0);

            if (!$id_user || !$id_menu) {
                echo json_encode(['success' => false, 'message' => 'Parámetros requeridos.']);
                exit;
            }

            $stmt = $db->ejecutar('revocarMenuUsuario', [':id_user' => $id_user, ':id_menu' => $id_menu]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchColumn()]);
            exit;
        }

        if (!$accion || !$entidad) {
            echo json_encode(['success' => false, 'message' => 'Parámetros obligatorios faltantes.']);
            exit;
        }

        $datos = $_REQUEST;
        unset($datos['accion'], $datos['entidad']);

        if ($entidad === 'categoria' && isset($_POST['remove_img_cat']) && $_POST['remove_img_cat'] === '1') {
            $datos['img_cat'] = 'images/default_category.webp';
        }

        if ($entidad === 'categoria' && isset($_FILES['img_cat']) && ($_FILES['img_cat']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = handleImageUpload($_FILES['img_cat'], __DIR__ . '/../../images/categorias/', 'categoria_', 'images/categorias/');
            if (!$upload['success']) {
                echo json_encode(['success' => false, 'message' => $upload['message'] ?? 'Error al subir la imagen de la categoría.']);
                exit;
            }

            $datos['img_cat'] = $upload['path'] ?? ($upload['paths'][0] ?? null);
        }

        $resultado = $db->gestionarCRUDAdmin($accion, $entidad, $datos);
        echo json_encode(['success' => true, 'data' => $resultado]);
    } catch (Exception $e) {
        throw $e;
    }

    exit;
}

require_once __DIR__ . '/../functions/navbar_usuario.php';
cargar_datos_navbar();

try {
    $db = Database::getInstance();
    $stmtPmtros = $db->ejecutar('obtenerConfiguracionGlobal');
    $pmtros = $stmtPmtros->fetch(PDO::FETCH_ASSOC);
    if (!$pmtros) {
        $pmtros = [];
    }

    $totalUsuarios  = $db->ejecutar('contarUsuarios')->fetchColumn() ?: 0;
    $totalProductos = $db->ejecutar('contarProductos')->fetchColumn() ?: 0;
    $totalPedidos   = $db->ejecutar('contarPedidos')->fetchColumn() ?: 0;
    $totalArtesanos = $db->ejecutar('contarArtesanos')->fetchColumn() ?: 0;
    $ingresosMes    = $db->ejecutar('sumarIngresosMes')->fetchColumn() ?: 0;
} catch (Throwable $e) {
    throw $e;
}

require_once __DIR__ . '/../views/admin_dashboard.view.php';
