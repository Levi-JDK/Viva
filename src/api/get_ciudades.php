<?php
// Endpoint para obtener ciudades base a un departamento
// Recibe: id_departamento (GET o POST)
// Retorna: JSON con lista de ciudades
header('Content-Type: application/json');
try {
    // 1. Obtener ID del departamento
    $id_departamento = $_GET['id_departamento'] ?? $_POST['id_departamento'] ?? null;
    if (!$id_departamento) {
        throw new Exception("ID de departamento no proporcionado");
    }



    require_once dirname(__DIR__, 2) . '/src/functions/database.php';
    // 3. Obtener instancia de BD
    $db = Database::getInstance();

    // 4. Ejecutar consulta preparada
    // Se asume que 'obtenerCiudades' ya está definida en database.php
    // y espera el parámetro :id_depto
    $stmt = $db->ejecutar('obtenerCiudades', [':id_depto' => $id_departamento]);
    $ciudades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Retornar resultados
    echo json_encode([
        'success' => true,
        'data' => $ciudades
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
