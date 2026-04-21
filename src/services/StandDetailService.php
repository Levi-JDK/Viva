<?php

require_once __DIR__ . '/../functions/database.php';

class StandDetailService
{
    public static function obtenerContexto(?int $idStand): array
    {
        $data = [
            'redirect' => null,
            'stand' => null,
            'productos_stand' => [],
            'promedio_estrellas_stand' => 0,
            'total_resenas_stand' => 0,
        ];

        if (!$idStand) {
            $data['redirect'] = BASE_URL . 'test-stands';

            return $data;
        }

        $db = Database::getInstance();
        $stmt = $db->ejecutar('obtenerStand', [':id_s' => $idStand]);
        $stand = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stand) {
            $data['redirect'] = BASE_URL . 'test-stands';

            return $data;
        }

        $data['stand'] = $stand;

        try {
            $productosRaw = $db->ejecutar('obtenerProductosCatalogo', [])->fetchAll(PDO::FETCH_ASSOC);
            $data['productos_stand'] = array_values(array_filter($productosRaw, static function (array $producto) use ($stand): bool {
                return (int) ($producto['id_productor'] ?? 0) === (int) ($stand['id_productor'] ?? 0);
            }));

            $stmtPromedio = $db->ejecutar('obtenerPromedioEstrellasStand', [':id_stand' => $idStand]);
            $promedioRow = $stmtPromedio->fetch(PDO::FETCH_ASSOC) ?: [];
            $data['promedio_estrellas_stand'] = round((float) ($promedioRow['promedio'] ?? 0), 1);
            $data['total_resenas_stand'] = (int) ($promedioRow['total_resenas'] ?? 0);
        } catch (Exception $e) {
            throw $e;
        }

        return $data;
    }
}
