<?php

require_once __DIR__ . '/../functions/database.php';

class VendorService
{
    public static function obtenerDatosRegistro(int $userId): array
    {
        $db = Database::getInstance();

        $esProductor = (bool) $db->ejecutar('validarProductor', [':id_user' => $userId])->fetchColumn();

        if ($esProductor) {
            return [
                'es_productor' => true,
                'tipos_doc' => [],
                'departamentos' => [],
                'grupos' => [],
                'bancos' => [],
            ];
        }

        return [
            'es_productor' => false,
            'tipos_doc' => $db->ejecutar('obtenerTiposDocumento')->fetchAll(PDO::FETCH_ASSOC),
            'departamentos' => $db->ejecutar('obtenerDepartamentos')->fetchAll(PDO::FETCH_ASSOC),
            'grupos' => $db->ejecutar('obtenerGrupos')->fetchAll(PDO::FETCH_ASSOC),
            'bancos' => $db->ejecutar('obtenerBancos')->fetchAll(PDO::FETCH_ASSOC),
        ];
    }
}
