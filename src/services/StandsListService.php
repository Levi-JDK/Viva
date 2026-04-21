<?php

require_once __DIR__ . '/../functions/database.php';

class StandsListService
{
    public static function obtenerContexto(array $query): array
    {
        $search = isset($query['q']) ? trim((string) $query['q']) : '';

        $db = Database::getInstance();

        if ($search !== '') {
            $stmt = $db->ejecutar('buscarStandsActivos', [':search' => '%' . $search . '%']);
        } else {
            $stmt = $db->ejecutar('obtenerStandsActivos');
        }

        return [
            'search' => $search,
            'stands' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ];
    }
}
