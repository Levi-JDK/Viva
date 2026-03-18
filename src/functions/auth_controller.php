<?php
require_once __DIR__ . '/auth_helper.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/mail_service.php';
require_once dirname(__DIR__) . '/workers/Config/RedisConfig.php';
require_once dirname(__DIR__) . '/workers/Services/ValidationService.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
}
catch (Exception $e) {
    echo json_encode([
        "mensaje" => "Error al inicializar la base de datos: " . $e->getMessage(),
        "clase" => "mensaje-error"
    ]);
    exit;
}


// ── GET: Verificar sesión actual via JWT ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userData = AuthHelper::verifyToken();

    if ($userData) {
        echo json_encode([
            'loggedIn' => true,
            'nombre' => $userData->nombre ?? '',
            'email' => $userData->email ?? ''
        ]);
    }
    else {
        echo json_encode(['loggedIn' => false]);
    }
    exit;
}


// ── POST: Registro, Login o Logout ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // ── Registro ─────────────────────────────────────────────────────────────
    if ($accion === 'registro') {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        $datosRegistro = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'password' => $contrasena
        ];

        $validacion = ValidationService::validarRegistro($datosRegistro);

        if (!$validacion['valido']) {
            echo json_encode([
                "mensaje" => implode("\n", $validacion['errores']),
                "clase" => "mensaje-error"
            ]);
            exit;
        }

        $hash = password_hash($contrasena, PASSWORD_ARGON2ID);

        try {
            // ======= INTEGRACIÓN REDIS ASÍNCRONA =======
            try {
                $redis = RedisConfig::getConnection();
                $prefix = RedisConfig::getPrefix();

                // Validar ultra-rápido en Redis
                $isRegistered = $redis->sismember($prefix . 'emails:registrados', $email);
                if ($isRegistered) {
                    echo json_encode(["mensaje" => "El correo ya está registrado.", "clase" => "mensaje-error"]);
                    exit;
                }

                // Asegurarse de que no haya otro intento en la cola con este email
                $lockKey = $prefix . 'lock:email:' . $email;
                $existsLock = $redis->exists($lockKey);
                if ($existsLock) {
                    echo json_encode(["mensaje" => "Ya hay un registro en proceso para este correo.", "clase" => "mensaje-error"]);
                    exit;
                }

                // SADD optimista para reservar el email inmediatamente
                $redis->sadd($prefix . 'emails:registrados', $email);

                // Bloquear el email temporalmente mientras se procesa (expira en 1 hora por seguridad)
                $redis->set($lockKey, '1', 'EX', 3600);

                // Generar ID único usando el contador global (inicializando rango inaccesible si no existe)
                $redis->setnx($prefix . 'contador:usuarios', 900000000);
                $idWorker = $redis->incr($prefix . 'contador:usuarios');

                // Guardar los datos en el Hash listos para el RegisterUserJob que espera [email, password, nombre, apellido]
                $redis->hset(
                    $prefix . 'user:' . $idWorker,
                    'nombre', $nombre,
                    'apellido', $apellido,
                    'mail', $email,
                    'password', $hash,
                    'created_at', date('Y-m-d H:i:s')
                );

                // Mantener índice HSET para login híbrido
                $redis->hset($prefix . 'email_to_id', $email, $idWorker);

                // Encolar ID para procesamiento
                $redis->lpush($prefix . 'cola:registros', $idWorker);

                // Respuesta exitosa inmediata al usuario
                echo json_encode([
                    "mensaje" => "Registro aceptado. Estamos procesando su solicitud...",
                    "clase" => "mensaje-exito"
                ]);

            }
            catch (\Exception $redisEx) {
                error_log('[Auth] Redis no disponible para registro asíncrono. Fallback... Error: ' . $redisEx->getMessage());

                // Validar que el email no exista en DB (validación rápida síncrona) fallback
                $stmtCheck = $db->ejecutar('validarEmail', [':email' => $email]);
                $existeEmail = $stmtCheck->fetchColumn();

                if ($existeEmail) {
                    echo json_encode(["mensaje" => "El correo ya está registrado.", "clase" => "mensaje-error"]);
                    exit;
                }

                // Fallback de emergencia a la base de datos síncrona si Redis falla
                $db->ejecutar('crearUsuario', [
                    ':email' => $email,
                    ':contrasena' => $hash,
                    ':nombre' => $nombre,
                    ':apellido' => $apellido
                ]);

                try {
                    $mail = MailService::getInstance();
                    $mail->sendWelcomeEmail($email, $nombre . ' ' . $apellido);
                }
                catch (Exception $e) {
                }

                echo json_encode(["mensaje" => "Usuario registrado correctamente.", "clase" => "mensaje-exito"]);
            }

        }
        catch (PDOException $e) {
            echo json_encode(["mensaje" => "Error validando datos: " . $e->getMessage(), "clase" => "mensaje-error"]);
        }

    // ── Login ─────────────────────────────────────────────────────────────────
    }
    elseif ($accion === 'login') {
        $email = trim($_POST['email'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if (empty($email) || empty($contrasena)) {
            echo json_encode(["mensaje" => "Todos los campos son obligatorios.", "clase" => "mensaje-error"]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["mensaje" => "El correo electrónico no es válido.", "clase" => "mensaje-error"]);
            exit;
        }

        try {
            $stmt = $db->ejecutar('obtenerHashLogin', [':email' => $email]);
            $hash = $stmt->fetchColumn();
            
            $loginSuccess = false;
            $usuario = null;

            if ($hash && password_verify($contrasena, $hash)) {
                $stmtUsuario = $db->ejecutar('obtenerUsuarioPorEmail', [':email' => $email]);
                $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
                $usuario['id_user'] = $usuario['id_user'];
                $usuario['nom_user'] = $usuario['nom_user'];
                $loginSuccess = true;
            } else {
                // Login Híbrido: Si no existe en BD o el hash no coincide, buscamos en Redis
                try {
                    $redis = RedisConfig::getConnection();
                    $prefix = RedisConfig::getPrefix();
                    
                    // Buscar en Redis usando el índice
                    $idTemporal = $redis->hget($prefix . 'email_to_id', $email);
                    if ($idTemporal) {
                        $userHash = $redis->hgetall($prefix . 'user:' . $idTemporal);
                        if (!empty($userHash) && isset($userHash['password']) && password_verify($contrasena, $userHash['password'])) {
                            $usuario = [
                                'id_user' => $idTemporal,
                                'nom_user' => $userHash['nombre'],
                                'email' => $email
                            ];
                            $loginSuccess = true;
                        }
                    }
                } catch (\Exception $e) {
                    error_log('[Auth] Error comprobando Redis para login híbrido: ' . $e->getMessage());
                }
            }

            if ($loginSuccess && $usuario) {
                $token = AuthHelper::generateToken([
                    'id_user' => $usuario['id_user'],
                    'nombre' => $usuario['nom_user'],
                    'email' => $email
                ]);
                AuthHelper::setAuthCookie($token);

                $redirectTo = !empty($_POST['redirect']) ? $_POST['redirect'] : BASE_URL;
                echo json_encode(["mensaje" => "Inicio de sesión exitoso", "clase" => "mensaje-exito", "redirect" => $redirectTo]);
            }
            else {
                echo json_encode(["mensaje" => "❌ Correo o contraseña incorrectos", "clase" => "mensaje-error"]);
            }

        }
        catch (PDOException $e) {
            echo json_encode(["mensaje" => "Error en la base de datos: " . $e->getMessage(), "clase" => "mensaje-error"]);
        }

    // ── Logout ────────────────────────────────────────────────────────────────
    }
    elseif ($accion === 'logout') {
        AuthHelper::clearAuthCookie();
        echo json_encode(["mensaje" => "Sesión cerrada.", "clase" => "mensaje-exito"]);

    }
    else {
        echo json_encode(["mensaje" => "Acción no válida.", "clase" => "mensaje-error"]);
    }
}
?>
