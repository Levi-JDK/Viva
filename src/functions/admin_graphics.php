<?php

require_once __DIR__ . '/database.php';

function getRevenueVsOrders(): array
{
    return Database::getInstance()
        ->ejecutar('adminRevenueVsOrders')
        ->fetchAll(PDO::FETCH_ASSOC);
}

function getTopProducts(int $limit = 5): array
{
    return Database::getInstance()
        ->ejecutar('adminTopProducts', [':limit' => $limit])
        ->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoryDistribution(): array
{
    return Database::getInstance()
        ->ejecutar('adminCategoryDistribution')
        ->fetchAll(PDO::FETCH_ASSOC);
}
