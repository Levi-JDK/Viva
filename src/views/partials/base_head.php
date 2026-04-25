<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="view-transition" content="same-origin">
    <title><?= htmlspecialchars($page_title ?? 'VIVA | Artesanías Colombianas') ?></title>
    <link rel="icon" href="<?= base_url_path('images/Logo.png') ?>" type="image/png">
    
    <script>
        const BASE_URL = '<?= defined('BASE_URL') ? BASE_URL : '/' ?>';
        window.BASE_URL = BASE_URL; // Exponer explícitamente al DOM para ES6 modules
        
        window.buildAppUrl = function (path = '') {
            const rawBaseUrl = String(window.BASE_URL || '');
            const baseUrl = rawBaseUrl === '/' ? '' : rawBaseUrl.replace(/\/+$/, '');
            const normalizedPath = String(path || '').replace(/^\/+/, '');
            if (!normalizedPath) return baseUrl || '/';
            return `${baseUrl}/${normalizedPath}`;
        };

        <?php
        $is_logged_in_global = false;
        if (class_exists('AuthHelper')) {
            $is_logged_in_global = AuthHelper::verifyToken() !== false;
        } else if (file_exists(__DIR__ . '/../../functions/auth_helper.php')) {
            require_once __DIR__ . '/../../functions/auth_helper.php';
            $is_logged_in_global = AuthHelper::verifyToken() !== false;
        }
        ?>
        window.USER_IS_LOGGED_IN = <?= $is_logged_in_global ? 'true' : 'false' ?>;
        window.LOGIN_URL = '<?= base_url_path('login') ?>';
    </script>
    <link rel="stylesheet" href="<?= base_url_path('src/styles/output.css') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS Extra dinámico (solo para páginas que lo necesiten) -->
    <?= $extra_css ?? '' ?>
    <script type="module" defer src="<?= base_url_path('src/scripts/main.js') ?>"></script>
</head>
<body class="<?= htmlspecialchars($body_class ?? 'bg-fondo-claro font-sans text-oscuro flex flex-col min-h-screen') ?>">
