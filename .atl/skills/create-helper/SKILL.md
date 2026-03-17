---
name: create-helper
description: >
  Genera una nueva función helper reutilizable en el proyecto Viva.
  Trigger: Cuando el usuario dice "crear helper", "new helper", "generar función helper"
license: MIT
metadata:
  author: viva-project
  version: "1.0"
---

## Propósito

Crear una nueva función helper siguiendo las convenciones del proyecto Viva.

## convenciones del Proyecto

### Estructura de Helper

```php
<?php

/**
 * Descripción breve de lo que hace la función
 * 
 * @param tipo $parametro Descripción del parámetro
 * @return tipo Descripción del retorno
 * @throws Exception Descripción de excepciones
 */

function nombreFuncion($parametro): tipo
{
    // Validación de entrada
    if (empty($parametro)) {
        throw new Exception('Mensaje de error');
    }

    // Lógica
    
    return $resultado;
}
```

### Reglas

1. Usar type hints en PHP 7+
2. Documentar con DocBlock
3. Lanzar excepciones con mensajes claros
4. Ubicar en `src/functions/`
5. Nombre en snake_case: `nombre_funcion.php`

## Retorno

Crear el archivo en `src/functions/{nombre_helper}.php` con la estructura correcta.
