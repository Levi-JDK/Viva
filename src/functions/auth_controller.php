<?php
try {
    require_once(__DIR__ . '/Database.php');
} catch (Error $e) {
    header('Content-Type: application/json');
    echo json_encode([
        "mensaje" => "Error crítico de carga: " . $e->getMessage(),
        "clase" => "mensaje-error"
    ]);
    exit;
}
header('Content-Type: application/json');

try {
    $db = new Database(require(__DIR__ . '/config.php'), $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo = $db->connection;
} catch (Exception $e) {
    echo json_encode([
        "mensaje" => "Error al inicializar la base de datos: " . $e->getMessage(),
        "clase" => "mensaje-error"
    ]);
    exit;
}

// Manejar solicitud GET para verificar sesión
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    session_start();
    
    if (isset($_SESSION['email']) && isset($_SESSION['nombre'])) {
        echo json_encode([
            'loggedIn' => true,
            'nombre' => $_SESSION['nombre'],
            'email' => $_SESSION['email']
        ]);
    } else {
        echo json_encode([
            'loggedIn' => false
        ]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'registro') {
        // Registro
        $nombre = $_POST['nombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $email = $_POST['email'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';

        if (empty($nombre) || empty($apellido) || empty($email) || empty($contrasena)) {
            echo json_encode([
                "mensaje" => "Todos los campos son obligatorios.",
                "clase" => "mensaje-error"
            ]);
            exit;
        }

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "mensaje" => "El correo electrónico no es válido.",
                "clase" => "mensaje-error"
            ]);
            exit;
        }

        $hash = password_hash($contrasena, PASSWORD_ARGON2ID);

        try {
            $sqlcheck = "SELECT fun_val_mail(:email)";
            $stmtcheck = $pdo->prepare($sqlcheck);
            $stmtcheck->bindParam(':email', $email, PDO::PARAM_STR);
            $stmtcheck->execute();
            $result = $stmtcheck->fetchColumn();

            if (!$result) {
                echo json_encode([
                    "mensaje" => "El correo ya está registrado.",
                    "clase" => "mensaje-error"
                ]);
                exit;
            }

            $sql = "SELECT fun_c_user(:email, :contrasena, :nombre, :apellido)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':contrasena', $hash, PDO::PARAM_STR);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $apellido, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode([
                "mensaje" => "Usuario registrado correctamente.",
                "clase" => "mensaje-exito"
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "mensaje" => "Error en el registro: " . $e->getMessage(),
                "clase" => "mensaje-error"
            ]);
        }
    } elseif ($accion === 'login') {
        // Login
        $email = $_POST['email'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';

        if (empty($email) || empty($contrasena)) {
            echo json_encode([
                "mensaje" => "Todos los campos son obligatorios.",
                "clase" => "mensaje-error"
            ]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "mensaje" => "El correo electrónico no es válido.",
                "clase" => "mensaje-error"
            ]);
            exit;
        }

        try {
            // Usar fun_val_log para obtener el hash
            $sqlpass = "SELECT fun_val_log(:email)";
            $stmt = $pdo->prepare($sqlpass);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $hash = $stmt->fetchColumn();

            if ($hash && password_verify($contrasena, $hash)) {
                // Iniciar sesión
                session_start();
                $_SESSION['email'] = $email;

                // Consulta adicional para obtener el nombre y el ID del usuario
                $sqlUsuario = "SELECT id_user, nom_user FROM tab_users WHERE mail_user = :email";
                $stmtUsuario = $pdo->prepare($sqlUsuario);
                $stmtUsuario->bindParam(':email', $email, PDO::PARAM_STR);
                $stmtUsuario->execute();
                $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    $_SESSION['id_user'] = $usuario['id_user'];  // ✅ GUARDAR ID DEL USUARIO
                    $_SESSION['nombre'] = $usuario['nom_user'];
                } else {
                    $_SESSION['nombre'] = $email;
                }

                echo json_encode([
                    "mensaje" => "✅ Inicio de sesión exitoso",
                    "clase" => "mensaje-exito"
                ]);
            } else {
                echo json_encode([
                    "mensaje" => "❌ Correo o contraseña incorrectos",
                    "clase" => "mensaje-error"
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "mensaje" => "Error en la base de datos: " . $e->getMessage(),
                "clase" => "mensaje-error"
            ]);
        }
    } else {
        echo json_encode([
            "mensaje" => "Acción no válida.",
            "clase" => "mensaje-error"
        ]);
    }
}
?>