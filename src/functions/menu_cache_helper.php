<?php

require_once __DIR__ . '/../workers/Config/RedisConfig.php';

function invalidate_user_menu_cache(int $userId, string $context = 'menu_cache'): void
{
    if ($userId <= 0) {
        return;
    }

    try {
        $cacheKey = RedisConfig::getPrefix() . "user:{$userId}:menus";
        RedisConfig::getConnection()->del($cacheKey);
    } catch (Exception $e) {
        error_log("[MenuCache] No se pudo invalidar el cache de menús del usuario {$userId} ({$context}): " . $e->getMessage());
    }
}
