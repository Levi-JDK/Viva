<?php

/**
 * API Endpoint: /api/favoritos
 * 
 * Uso documentado:
 * 
 * - GET: Obtiene la lista de favoritos del usuario actual.
 *   Respuesta: { exito: true, favoritos: [...] }
 * 
 * - POST: Agrega o elimina un producto de favoritos.
 *   Body (JSON): { accion: 'agregar' | 'eliminar', id_producto: 123 }
 *   Respuesta: { exito: true, mensaje: '...' }
 */



header('Content-Type: application/json');

require_once __DIR__ . '/../functions/auth_helper.php';
$userData = AuthHelper::protectRoute();

try {
    // Nota: El vendor/autoload.php y el Dotenv::createImmutable ya se cargaron globalmente en index.php
    // Solo requerimos database.php ya que las URIs de API a veces no incluyen Database explícitamente en index.php,
    // pero si index.php lo tiene, no nos preocupamos. Usaremos require_once por si acaso la clase no está cargada.
    require_once __DIR__ . '/../functions/database.php';

    $db = Database::getInstance();
    $id_user = (int)$userData->id_user;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Lógica GET: Obtener lista de favoritos
        $stmt = $db->ejecutar('obtenerFavoritosUsuario', [':id_user' => $id_user]);
        $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['exito' => true, 'favoritos' => $favoritos]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Lógica POST: Agregar o Eliminar
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['exito' => false, 'mensaje' => 'Datos inválidos']);
            exit;
        }

        $accion = $input['accion'] ?? '';
        $id_producto = isset($input['id_producto']) ? (int)$input['id_producto'] : 0;

        if (empty($accion) || empty($id_producto)) {
            echo json_encode(['exito' => false, 'mensaje' => 'Faltan parámetros']);
            exit;
        }

        if ($accion === 'agregar') {
            $stmt = $db->ejecutar('agregarFavorito', [
                ':id_user' => $id_user,
                ':id_producto' => $id_producto
            ]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $exito = $res['fun_c_favorito'] ?? false;
            
            if ($exito) {
                echo json_encode(['exito' => true, 'mensaje' => 'Añadido a favoritos']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo agregar a favoritos']);
            }
    
        } elseif ($accion === 'eliminar') {
            $stmt = $db->ejecutar('eliminarFavorito', [
                ':id_user' => $id_user,
                ':id_producto' => $id_producto
            ]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            $exito = $res['fun_d_favoritos'] ?? false;
            
            if ($exito) {
                echo json_encode(['exito' => true, 'mensaje' => 'Eliminado de favoritos']);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'No se pudo eliminar de favoritos']);
            }
            
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Acción no reconocida']);
        }
        exit;
    }

    // Default para otros métodos
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);

} catch (Exception $e) {
    error_log("[Favoritos API] Error general: " . $e->getMessage());
    echo json_encode(['exito' => false, 'mensaje' => 'Error interno en el servidor']);
}
