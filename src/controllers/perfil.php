<?php 

require_once ROOT_PATH . 'src/functions/auth_helper.php';
// Verificar que el usuario esté autenticado. Si no, redirige.
$usuarioData = AuthHelper::protectRoute();
$id_usuario = $usuarioData->id_user;

// Cargar las dependencias necesarias
require_once ROOT_PATH . 'src/functions/database.php';

// Conectar a la base de datos usando Singleton
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Error de conexión a base de datos: " . $e->getMessage());
}

// ============================================================================
// MANEJAR PETICIONES POST (ACTUALIZAR PERFIL, ETC.)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'update_profile') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');

        if (empty($nombre) || empty($apellido)) {
            echo json_encode(['clase' => 'mensaje-error', 'mensaje' => 'El nombre y apellido son obligatorios.']);
            exit;
        }

        // Validar caracteres en nombre (# * - ' ")
        if (preg_match('/[#*\-\'"]/', $nombre)) {
            echo json_encode(['clase' => 'mensaje-error', 'mensaje' => "El nombre no puede contener los caracteres: # * - ' \""]);
            exit;
        }

        // Validar caracteres en apellido (' ")
        if (preg_match('/[\'"]/', $apellido)) {
            echo json_encode(['clase' => 'mensaje-error', 'mensaje' => "El apellido no puede contener comillas (' \")"]);
            exit;
        }

        try {
            // Actualizar perfil usando consulta preparada
            $stmt = $db->ejecutar('actualizarPerfil', [
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':id' => $id_usuario
            ]);

            // Retornamos exito
            echo json_encode(['clase' => 'mensaje-exito', 'mensaje' => 'Perfil actualizado correctamente.']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['clase' => 'mensaje-error', 'mensaje' => 'Error al actualizar: ' . $e->getMessage()]);
            exit;
        }

    }
    
    // Si la acción no es válida
    echo json_encode(['clase' => 'mensaje-error', 'mensaje' => 'Acción no reconocida.']);
    exit;
}

// Obtener datos del usuario actual
try {
    // Obtener datos del usuario usando consulta preparada
    $stmt = $db->ejecutar('obtenerUsuarioPorId', [':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si no se encuentra el usuario en la BD, limpiar cookie JWT y redirigir
    if (!$usuario) {
        AuthHelper::clearAuthCookie();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
} catch (PDOException $e) {
    die("Error al obtener datos del usuario: " . $e->getMessage());
}

// ============================================================================
// PROCESAR DATOS DEL USUARIO PARA LA VISTA
// ============================================================================
$nombre_usuario = $usuario['nom_user'] ?? 'Usuario';
$apellido_usuario = $usuario['ape_user'] ?? '';
$nombre_completo = $nombre_usuario . ' ' . $apellido_usuario;
$email_usuario = $usuario['mail_user'] ?? '';
$foto_usuario = $usuario['foto_user'] ?? 'images/default.jpg';
$fecha_registro = $usuario['created_at'] ?? null;

// Formatear fecha de registro si existe
if ($fecha_registro) {
    $fecha_obj = new DateTime($fecha_registro);
    $fecha_formateada = $fecha_obj->format('F Y'); // Ejemplo: "Febrero 2026"
    // Traducir meses al español
    $meses = [
        'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
        'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
        'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
        'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
    ];
    $fecha_formateada = str_replace(array_keys($meses), array_values($meses), $fecha_formateada);
} else {
    $fecha_formateada = 'Fecha desconocida';
}

// Obtener la inicial del nombre para el avatar
$inicial_usuario = strtoupper(substr($nombre_usuario, 0, 1));

// Obtener pedidos del usuario
try {
    $stmtPedidos = $db->ejecutar('obtenerPedidosCliente', [':id_user' => $id_usuario]);
    $pedidos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pedidos = [];
    error_log("Error al obtener pedidos del usuario: " . $e->getMessage());
}

// Usamos ROOT_PATH para que el include sea absoluto desde el disco
require_once ROOT_PATH . "src/views/perfil.view.php";
?>
