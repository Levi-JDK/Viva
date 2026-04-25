<?php

require_once __DIR__ . '/../functions/database.php';

class HomeService
{
    public static function obtenerDatosLanding(): array
    {
        $data = [
            'featured_stands' => [],
            'featured_products' => [],
            'categorias_destacadas' => [],
            'pmtros' => [],
        ];

        try {
            $db = Database::getInstance();
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $stmt = $db->ejecutar('obtenerStandsDestacados', [':limit' => 3]);
            $data['featured_stands'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $stmt = $db->ejecutar('obtenerProductosDestacados', [':limit' => 4]);
            $data['featured_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $stmt = $db->ejecutar('obtenerFiltrosCategorias');
            $data['categorias_destacadas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $data['pmtros'] = $db->obtenerConfiguracion();
        } catch (Exception $e) {
            throw $e;
        }

        return $data;
    }
}
