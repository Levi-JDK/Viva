---
name: security-audit
description: >
  Analiza el código del proyecto en busca de vulnerabilidades de seguridad.
  Trigger: Cuando el usuario dice "revisar seguridad", "security audit", "auditar seguridad"
license: MIT
metadata:
  author: viva-project
  version: "1.0"
---

## Propósito

Realizar una auditoría de seguridad del proyecto Viva, identificando vulnerabilidades comunes y problemas de configuración.

## Ejecución

### Step 1: Verificar Configuración de Seguridad

1. Verificar que .env está en .gitignore
2. Verificar que no hay credenciales hardcodeadas en el código
3. Verificar que los mensajes de error no exponen datos sensibles

### Step 2: Revisar APIs

Para cada archivo en `src/api/`:
1. Verificar uso de parámetros firmados (JWT)
2. Verificar sanitización de inputs
3. Verificar manejo de errores

### Step 3: Generar Reporte

Generar un reporte markdown con:
- Vulnerabilidades críticas encontradas
- Vulnerabilidades importantes
- Sugerencias de mejora

## Retorno

```
## Security Audit - Viva Project

### Vulnerabilidades Críticas
- [ ] {vulnerabilidad}

### Vulnerabilidades Importantes
- [ ] {vulnerabilidad}

### Sugerencias
- [ ] {sugerencia}
```
