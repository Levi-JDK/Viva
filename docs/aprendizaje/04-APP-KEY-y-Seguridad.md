# 04. APP_KEY y Seguridad en Laravel

## ¿Qué es el APP_KEY?

El `APP_KEY` es la **clave maestra** de tu aplicación Laravel. Es el secret más importante que existe.

Imaginá que es la llave de una caja fuerte. Todo lo que necesitás cifrar dentro de tu app usa esta llave.

## ¿Para qué se usa?

### 1. Encriptar Cookies y Sesiones
Cuando un usuario inicia sesión, Laravel guarda cookies firmadas:
```php
// Internamente, Laravel hace esto:
Cookie::make('session', $data, $minutes, null, null, null, true, false, 'sha256')
//                         ↑ La firma usa APP_KEY
```

Si alguien intenta modificar una cookie, Laravel lo detecta porque la firma no coincide.

### 2. Generar Tokens JWT (Tu Patovica)
```php
// Cuando generás el token de tu usuario:
$token = auth('api')->login($user);
// ↑ Usa APP_KEY para firmar el payload

// Si alguien modifica el payload, la firma no coincide → Token inválido
```

### 3. Cifrar datos sensibles
```php
// Encriptar datos sensibles (ej: un token de payment)
$encrypted = encrypt('Número de tarjeta');
// Si alguien lo ve en la DB, no sabe qué es sin la APP_KEY
```

### 4. Links de recuperación de contraseña
```php
URL::temporarySignedRoute('password.reset', $now->addMinutes(30), ['user' => $user]);
// ↑ El hash incluye APP_KEY, así nadie puede generar links falsos
```

## Cómo se genera

```bash
# En tu terminal, dentro de viva-app/
php artisan key:generate
```

Eso genera una clave de 256 bits (32 bytes) codificada en Base64.

## Ejemplo visual

```
ANTES (composer install):
.env
APP_KEY=

DESPUÉS (php artisan key:generate):
.env
APP_KEY=base64:rXjKZG9tU25pTmVyZGF0YVZpcnR1YWxJbnNlcnRDb2RlbWFubyRCb2RlamFIZEDheTMS
```

## ⚠️ Reglas de seguridad

1. **NUNCA hagas commit del `.env`** → Ya está en tu `.gitignore`
2. **En producción, cada server tiene su propia APP_KEY**
3. **Si la cambia en producción,所有的 cookies y sesiones se invalidan** (los usuarios se deslogean)

## Verificar que está configurada

```bash
php artisan config:show app.key
```

O en código:
```php
config('app.key');  // Retorna la clave
```

## En caso de emergencia

Si perdés tu APP_KEY y necesitás cambiarla:

1. **Nueva clave:** `php artisan key:generate`
2. **Todas las cookies/sesiones se invalidan** (los usuarios se deslogean)
3. **Si usabas `encrypt()`, esos datos ya no se pueden desencriptar** (perderlos)