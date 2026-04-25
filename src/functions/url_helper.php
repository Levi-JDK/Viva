<?php

if (!function_exists('viva_detect_base_url')) {
    function viva_detect_base_url(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $projectFolder = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $projectFolder = str_replace(['/src/functions', '/src/api', '/src/controllers/mis_productos', '/src/controllers'], '', $projectFolder);
        $projectFolder = rtrim($projectFolder, '/');

        if ($projectFolder === '/' || $projectFolder === '.') {
            $projectFolder = '';
        }

        return $protocol . '://' . $host . $projectFolder;
    }
}

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim(viva_detect_base_url(), '/') . '/');
}

if (!function_exists('base_url_path')) {
    function base_url_path(string $path = ''): string
    {
        $baseUrl = rtrim(BASE_URL, '/');
        $path = trim($path);

        if ($path === '') {
            return $baseUrl;
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        $basePath = $basePath !== '' ? '/' . trim($basePath, '/') : '';

        $scheme = parse_url(BASE_URL, PHP_URL_SCHEME) ?? 'http';
        $host = parse_url(BASE_URL, PHP_URL_HOST) ?? '';
        $port = parse_url(BASE_URL, PHP_URL_PORT);
        $origin = $host !== '' ? $scheme . '://' . $host . ($port !== null ? ':' . $port : '') : '';

        if ($path[0] === '/') {
            if ($basePath !== '' && ($path === $basePath || strpos($path, $basePath . '/') === 0)) {
                return $origin !== '' ? $origin . $path : $path;
            }

            return $baseUrl . $path;
        }

        if ($basePath !== '' && ($path === ltrim($basePath, '/') || strpos($path, ltrim($basePath, '/') . '/') === 0)) {
            return $origin !== '' ? $origin . '/' . ltrim($path, '/') : '/' . ltrim($path, '/');
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('request_relative_path')) {
    function request_relative_path(?string $requestUri = null): string
    {
        $requestPath = parse_url($requestUri ?? ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?? '/';
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        $basePath = rtrim($basePath, '/');

        if ($basePath !== '' && $basePath !== '/' && strpos($requestPath, $basePath) === 0) {
            $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
        }

        $requestPath = '/' . ltrim($requestPath, '/');

        if ($requestPath !== '/') {
            $requestPath = rtrim($requestPath, '/');
        }

        return $requestPath;
    }
}

if (!function_exists('is_api_route_path')) {
    function is_api_route_path(?string $path = null): bool
    {
        return strpos($path ?? request_relative_path(), '/api/') === 0;
    }
}
