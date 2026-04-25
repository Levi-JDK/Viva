# Project Memories & Knowledge Base (Engram Sync)

> Este archivo contiene un volcado de las decisiones, bugfixes y descubrimientos más recientes almacenados en la memoria persistente (Engram) para el proyecto **Viva**. Mantener este archivo versionado permite que todo el equipo comparta el mismo contexto técnico.

## 🐛 Bugfixes Recientes

### JWT Secret con Fail-Fast
- **What**: Se reemplaza el JWT_SECRET hardcodeado ('TU_SUPER_CLAVE_SECRETA_AQUI_CAMBIAME') con lazy-loading desde $_ENV usando patrón ensureSecret() con RuntimeException.
- **Why**: Cualquiera podía forjar tokens JWT porque la clave estaba hardcodeada.
- **Where**: `src/functions/auth_helper.php` — variable `$secret_key`

### Validation Bypass en Redis Fallback
- **What**: Se agregó validación y chequeos de seguridad en el bloque catch del fallback de Redis en el registro.
- **Why**: Cuando Redis fallaba, el fallback DB insert omitía ValidationService::validarRegistro() y el check de email único estaba desprotegido — permitiendo registro con nombre vacío, email inválido, o email duplicado.
- **Where**: `src/functions/auth_controller.php` (registerVendor fallback)
- **Learned**: La validación redundante en el fallback es OBLIGATORIA por seguridad.

### Password Recovery Email (Falso Positivo)
- **What**: Se arregló el módulo de recuperación de contraseña que devolvía "éxito" aunque el correo no se enviara.
- **Why**: `MailService::sendPasswordRecoveryEmail()` devolvía `false` cuando el correo fallaba, pero el controlador no estaba manejando el retorno correctamente.
- **Where**: `PasswordRecoveryService.js`, `PasswordRecoveryController.js`, `mail_service.php`

### Configuración de Email & SMTP
- **What**: Se arregló la configuración de email donde `SMTP_SECURE` estaba siendo ignorado y se configuró correctamente el puerto 465 (SSL). Se documentó la necesidad de usar App Passwords de Gmail.
- **Why**: Los correos de recuperación no salían.
- **Where**: `.env`, `mail_service.php`

### Workers Asíncronos (Redis)
- **What**: Se agregó el envío del correo de bienvenida al Worker de Redis tras el registro.
- **Why**: El correo de bienvenida solo se estaba enviando en el fallback síncrono, pero no en la ruta asíncrona del Worker.
- **Where**: `src/workers/Worker.php` (`ejecutarInsertUsuario()`)

### Soft Delete & Admin CRUD
- **What**: Se modificó el mapeo de parámetros en `gestionarCRUDAdmin` para mapear correctamente `p_id` a la primary key (`$pk`) enviada desde el frontend.
- **Why**: La función de PostgreSQL de soft delete (`fun_softdel_*`) esperaba `p_id`, pero el CRUD genérico intentaba buscar una columna llamada `id`, causando fallos SQL.
- **Where**: `scripts/funciones_db/fun_softdel.sql`, Admin Panel.

### Triggers de Base de Datos
- **What**: Se modificó el trigger `set_updated()` para usar incondicionalmente `current_user` como `updated_by` y `CURRENT_TIMESTAMP` en `updated_at`.
- **Why**: Se perdían los datos de auditoría al hacer soft deletes (`is_deleted = true`).

## 🏗️ Decisiones de Arquitectura y Configuración

### Lighthouse Performance Quick Fix
- **What**: Se implementaron reglas en `.htaccess` (`Cache-Control`, `mod_expires`, `mod_deflate`) para assets estáticos y se agregó el atributo `defer` al script `main.js`.
- **Why**: Para mejorar rápidamente el score de Lighthouse y evitar el bloqueo de renderizado, antes de hacer cambios profundos en la arquitectura del frontend.
- **Where**: `.htaccess`, `src/views/partials/base_head.php`

### Limpieza de Código (Debug)
- **What**: Se eliminaron todos los statements de debug (`console.log`, `die`, código comentado) del codebase.
- **Why**: Preparación y limpieza antes del pase a producción.
- **Where**: `src/scripts/main.js`, `src/scripts/utils/EventRouter.js`

### Seguridad & Entorno (.env)
- **What**: Se restauraron las reglas de `.htaccess` y se limpiaron los cambios locales en el servidor, además de asegurar que los errores de la DB no se expongan en la API.
- **Why**: Un error crítico ocurrió al remover el `.env` del tracking de git y hacer un `git pull` en producción, lo que borró el archivo del servidor rompiendo la conexión a la base de datos.
- **Rule**: NUNCA dejar el `.env` en tracking, pero manejar con cuidado los despliegues para no borrar el archivo físico en producción.

### Login Refactor a Flexbox
- **What**: Refactorización de las vistas de autenticación (login y registro) eliminando el diseño basado en animaciones absolutas (sliding overlay) por una estructura limpia en Flexbox (`flex-col md:flex-row`).
- **Why**: El diseño original causaba problemas graves de responsiveness (cajas super gordas) y generaba barras de desplazamiento horizontales al hacer zoom en el navegador por usar `w-[200%]` y `overflow-hidden`.
- **Where**: `src/views/login.view.php`, `src/views/registro.view.php`, `index.php`
- **Learned**: Separar vistas lógicas y usar Flexbox (Side-by-side) es superior en mantenibilidad y accesibilidad frente a hacks de CSS de animación de superposición (overlay).

### UI Proportions & REM vs PX
- **What**: Ajuste de proporciones en el refactor de Login/Registro: cambio de `px` a `rem` y reducción del ancho máximo de las tarjetas.
- **Why**: El usuario reportó que los contenedores se veían "súper gordos" y no escalaban bien al hacer zoom o cambiar el tamaño de fuente del navegador. 
- **Where**: `src/views/login.view.php`, `src/views/registro.view.php`
- **Learned**: Siempre preferir medidas relativas (`rem`) sobre absolutas (`px`) en utilidades arbitrarias de Tailwind (ej. `max-w-[42rem]` en lugar de `max-w-[768px]`). Para diseños de tarjetas divididas al medio (Split UI), anchos superiores a `42rem` (672px) suelen verse desproporcionados o estirados ("gordos") en desktop.
- **What**: Eliminación de los límites artificiales (`context` y `output`) harcodeados en el archivo de configuración global `~/.config/opencode/opencode.json` y corrección en el plugin de auth.
- **Why**: El modelo abortaba tareas grandes a la mitad porque la configuración le imponía un límite estricto de tokens de salida (`output: 65536`) que chocaba con la capacidad real del modelo (como `qwen3.6` o `claude-3.5`).
- **Where**: `~/.config/opencode/opencode.json`, `opencode-qwencode-auth`
- **Learned**: Si un modelo potente aborta tareas grandes o deja código a la mitad, es casi seguro culpa del límite inyectado por la configuración local del CLI o plugins.