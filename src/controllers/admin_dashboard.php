<?php
/**
 * src/controllers/admin_dashboard.php
 * Controlador del Panel de Administrador.
 */

require_once __DIR__ . '/../functions/auth_helper.php';

// Valida que el usuario tenga acceso al módulo 8 (Admin Dashboard)
AuthHelper::checkAccess(8);

// Cargar variables del usuario (foto, nombre) para el sidebar
require_once __DIR__ . '/../functions/navbar_usuario.php';
cargar_datos_navbar();

// Obtener parámetros globales y de configuración de landing
require_once __DIR__ . '/../functions/database.php';
try {
    $db = Database::getInstance();
    $stmtPmtros = $db->ejecutar('obtenerConfiguracionGlobal');
    $pmtros = $stmtPmtros->fetch(PDO::FETCH_ASSOC);
    if (!$pmtros) $pmtros = [];
} catch (\Throwable $e) {
    $pmtros = []; // Fallback seguro
}

require_once __DIR__ . '/../views/admin_dashboard.view.php';
