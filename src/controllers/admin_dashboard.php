<?php
/**
 * src/controllers/admin_dashboard.php
 * Controlador del Panel de Administrador.
 */

require_once __DIR__ . '/../functions/auth_helper.php';

// Valida que el usuario tenga acceso al módulo 1 (Admin Dashboard)
AuthHelper::checkAccess(1);

// Cargar variables del usuario (foto, nombre) para el sidebar
require_once __DIR__ . '/../functions/navbar_usuario.php';
cargar_datos_navbar();

// Obtener parámetros globales y de configuración de landing
require_once __DIR__ . '/../functions/database.php';
try {
    $db = Database::getInstance();
    $pmtros = $db->obtenerConfiguracion();
} catch (Exception $e) {
    $pmtros = []; // Fallback seguro
}

require_once __DIR__ . '/../views/admin_dashboard.view.php';
