<?php

require_once __DIR__ . '/database.php';

function getProducerRevenueVsSales(int $idProductor): array
{
    return Database::getInstance()
        ->ejecutar('producerRevenueVsSales', [':id_productor' => $idProductor])
        ->fetchAll(PDO::FETCH_ASSOC);
}

function getProducerTopProducts(int $idProductor, int $limit = 3): array
{
    return Database::getInstance()
        ->ejecutar('producerTopProducts', [':id_productor' => $idProductor, ':limit' => $limit])
        ->fetchAll(PDO::FETCH_ASSOC);
}

function getProducerShippingStatus(int $idProductor): array
{
    return Database::getInstance()
        ->ejecutar('producerShippingStatus', [':id_productor' => $idProductor])
        ->fetchAll(PDO::FETCH_ASSOC);
}
