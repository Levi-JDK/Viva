---
name: create-api
description: >
  Genera una nueva API REST en el proyecto Viva.
  Trigger: Cuando el usuario dice "crear api", "new api", "generar endpoint"
license: MIT
metadata:
  author: viva-project
  version: "1.0"
---

## Propósito

Crear una nueva API REST siguiendo las convenciones del proyecto Viva.

## convenciones del Proyecto

### Estructura de API

```php
<?php
/**
 * API: {Nombre de la API}
 * Método: GET/POST
 * Ruta: /api/{nombre}
 * 
 * Descripción de lo que hace la API
 */

header('Content-Type: application/json; charset=utf-8');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Requerir helpers
require_once __DIR__ . '/../functions/auth_helper.php';
require_once __DIR__ . '/../functions/error_handler.php';
require_once __DIR__ . '/../functions/database.php';

// Proteger ruta (si requiere autenticación)
// $userData = AuthHelper::protectRoute();

try {
    // Lógica de la API
    
    echo json_encode([
        'success' => true,
        'data' => []
    ]);

} catch (Exception $e) {
    echo json_encode(ErrorHandler::jsonResponse($e, 'nombre_api'));
}
```

### Reglas

1. Siempre usar ErrorHandler para manejo de errores
2. Siempre incluir headers de seguridad
3. Usar json_encode con JSON_UNESCAPED_UNICODE
4. Nombres de archivos en snake_case: `nombre_api.php`

## Retorno

Crear el archivo en `src/api/{nombre_api}.php` con la estructura correcta.
