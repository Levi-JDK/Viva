<?php
// src/controllers/mis_productos/stand.php

require_once ROOT_PATH . 'src/functions/image_uploader.php';

// ==========================================
// MANEJAR PETICIÓN POST (ACTUALIZAR/CREAR)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        if (!$id_user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
            exit;
        }

        $db = Database::getInstance();

        // Obtener ID del productor
        $stmtProd = $db->ejecutar('obtenerIdProductor', [':id_user' => $id_user]);
        $id_productor = $stmtProd->fetchColumn();

        if (!$id_productor) {
            echo json_encode(['success' => false, 'message' => 'Productor no encontrado']);
            exit;
        }

        // Verificar si ya existe el stand
        $stmtCheck = $db->ejecutar('verificarStand', [':id_p' => $id_productor]);
        $id_stand = $stmtCheck->fetchColumn();

        // Gestionar subida de imágenes (retorna ruta o null)
        $targetDir = ROOT_PATH . 'images/stands/';
        $prefix = 'stand_' . $id_productor . '_';
        
        $img_stand = null;
        if (isset($_FILES['img_stand']) && $_FILES['img_stand']['error'] === UPLOAD_ERR_OK) {
            $res = handleImageUpload($_FILES['img_stand'], $targetDir, $prefix . 'logo_', 'images/stands/');
            if (!$res['success']) {
                echo json_encode(['success' => false, 'message' => 'Error Logo: ' . $res['message']]);
                exit;
            }
            $img_stand = $res['path'];
        }

        $portada_stand = null;
        if (isset($_FILES['portada_stand']) && $_FILES['portada_stand']['error'] === UPLOAD_ERR_OK) {
            $res = handleImageUpload($_FILES['portada_stand'], $targetDir, $prefix . 'cover_', 'images/stands/');
            if (!$res['success']) {
                echo json_encode(['success' => false, 'message' => 'Error Portada: ' . $res['message']]);
                exit;
            }
            $portada_stand = $res['path'];
        }

        // Obtener datos de texto
        $nom_stand = $_POST['nom_stand'] ?? null;
        $slogan_stand = $_POST['slogan_stand'] ?? null;
        $descripcion_stand = $_POST['descripcion_stand'] ?? null;

        if ($id_stand) {
            // ACTUALIZAR stand existente
            $stmt = $db->ejecutar('actualizarStand', [
                ':id_productor' => $id_productor,
                ':id_stand' => $id_stand,
                ':nom_stand' => $nom_stand,
                ':slogan_stand' => $slogan_stand,
                ':descripcion_stand' => $descripcion_stand,
                ':img_stand' => $img_stand,
                ':portada_stand' => $portada_stand
            ]);
            $message = 'Stand actualizado correctamente';
        } else {
            // CREAR nuevo stand con valores por defecto si es necesario
            $stmt = $db->ejecutar('registrarStand', [
                ':id_productor' => $id_productor,
                ':nom_stand' => $nom_stand ?: 'Mi Emprendimiento',
                ':slogan_stand' => $slogan_stand ?: 'Bienvenidos a mi stand virtual',
                ':descripcion_stand' => $descripcion_stand ?: 'Aquí encontrarás mis mejores productos artesanales.',
                ':img_stand' => $img_stand ?: 'images/default.jpg',
                ':portada_stand' => $portada_stand ?: 'images/default_cover.jpg'
            ]);
            $message = 'Stand creado correctamente';
        }

        // Verificar resultado de la función
        $result = $stmt->fetchColumn();
        if ($result === true || $result === 't') {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en base de datos']);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    
    exit;
}

// ==========================================
// MANEJAR PETICIÓN GET (RENDERIZAR VISTA)
// ==========================================

// Obtener datos del stand ($id_productor viene de mis_productos.php)
$stmtStand = $db->ejecutar('obtenerStandPrivado', [':id_p' => $id_productor]);
$stand = $stmtStand->fetch(PDO::FETCH_ASSOC);

require_once ROOT_PATH . "src/views/mis_productos/stand.view.php";
