# Proposal: Correcciones de Seguridad Básicas

## Intent

El proyecto Viva fue desarrollado mediante vibecoding sin revisión de seguridad formal. Esta propuesta documenta las correcciones críticas de seguridad identificadas y aplicadas para proteger datos personales de los usuarios y credenciales del sistema.

**Problema raíz**: El código contenía credenciales hardcodeadas, datos sensibles expuestos en APIs, y configuración insegura que violaba las políticas de tratamiento de datos aceptadas por los usuarios.

## Scope

### In Scope
- Eliminar credenciales hardcodeadas del código fuente
- Eliminar exposición de datos personales en respuestas API
- Configurar manejo seguro de variables de entorno
- Agregar validación de configuración requerida

### Out of Scope
- Implementación de CSRF (pendiente)
- Sanear mensajes de error de BD (pendiente)
- Auditoría completa de seguridad
- Tests automatizados de seguridad

## Approach

Las correcciones se implementaron de forma directa:

1. **Credenciales**: Mover JWT_SECRET a .env con generación de clave segura
2. **Datos personales**: Eliminar debug_params de APIs y console.log del frontend
3. **Fallbacks seguros**: Cambiar valores por defecto que ocultaban problemas de configuración

Los cambios siguen el principio "fail fast" - el sistema ahora falla de forma controlada si falta configuración en vez de funcionar con valores inseguros.

## Affected Areas

| Área | Impacto | Descripción |
|------|---------|-------------|
| `.env` | Modificado | Agregada JWT_SECRET |
| `src/functions/auth_helper.php` | Modificado | Lee JWT_SECRET de $_ENV |
| `src/functions/mail_service.php` | Modificado | Fallback con excepción |
| `src/api/post_registro_vendedor.php` | Modificado | Eliminado debug_params |
| `src/scripts/registro_vendedor.js` | Modificado | Eliminado console.error |

## Risks

| Riesgo | Likelihood | Mitigación |
|--------|------------|------------|
| .env comprometido | Low | .gitignore ya configurado, regenerar credenciales |
| Break en producción | Low | Mismo comportamiento anterior, solo más seguro |

## Rollback Plan

Para revertir cada cambio:

1. **JWT_SECRET**: Volver a valor hardcodeado en auth_helper.php línea 29
2. **debug_params**: Restaurar las líneas eliminadas en post_registro_vendedor.php y registro_vendedor.js
3. **mail_service**: Cambiar `throw new Exception` por `?? 'tu_correo@gmail.com'`

## Dependencies

- Ninguna dependencia externa
- Requiere que .env tenga JWT_SECRET configurado

## Success Criteria

- [x] JWT_SECRET configurada en .env y usada por auth_helper.php
- [x] MailService lanza excepción si no hay MAIL_FROM_ADDRESS
- [x] post_registro_vendedor.php no expone datos personales
- [x] registro_vendedor.js no hace console.error con datos sensibles

## metadata

- **Estado**: Completado y archivado
- **Fecha**: 2026-03-15
- **Implementado por**: AI Assistant con revisión de usuario
- **Tipo**: Security fix
- **Severidad**: Crítica

---

## Archive Info

- **Archivado**: 2026-03-15
- **Ubicación**: `openspec/changes/archive/2026-03-15-seguridad-correcciones-basicas/`
- **Cambios adicionales incluidos en el archive**:
  - ErrorHandler centralizado (`src/functions/error_handler.php`)
  - 9 APIs actualizadas con manejo seguro de errores
  - Mensajes de error genéricos para usuarios