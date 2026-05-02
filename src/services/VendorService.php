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

    public static function registrarVendedor(int $userId, array $data): array
    {
        $tipoDocumento = filter_var($data['tipo_documento'] ?? null, FILTER_VALIDATE_INT);
        $numeroDocumento = trim((string) ($data['numero_documento'] ?? ''));
        $direccion = trim((string) ($data['direccion'] ?? ''));
        $departamento = filter_var($data['departamento'] ?? null, FILTER_VALIDATE_INT);
        $ciudad = filter_var($data['ciudad'] ?? null, FILTER_VALIDATE_INT);
        $grupoArtesanal = filter_var($data['grupo_artesanal'] ?? null, FILTER_VALIDATE_INT);
        $banco = filter_var($data['banco'] ?? null, FILTER_VALIDATE_INT);
        $tipoCuenta = trim((string) ($data['tipo_cuenta'] ?? ''));
        $tiposCuentaPermitidos = [
            'Ahorros' => 1,
            'Corriente' => 2,
        ];
        $numeroCuenta = trim((string) ($data['numero_cuenta'] ?? ''));

        if (
            !$tipoDocumento ||
            !$departamento ||
            !$ciudad ||
            !$grupoArtesanal ||
            !$banco ||
            $direccion === ''
        ) {
            return [
                'success' => false,
                'message' => 'Completá todos los campos obligatorios.',
            ];
        }

        if (!preg_match('/^\d{10}$/', $numeroDocumento)) {
            return [
                'success' => false,
                'message' => 'El número de documento debe tener exactamente 10 dígitos.',
            ];
        }

        if (!preg_match('/^\d{1,12}$/', $numeroCuenta)) {
            return [
                'success' => false,
                'message' => 'El número de cuenta debe contener solo números y hasta 12 dígitos.',
            ];
        }

        if (!array_key_exists($tipoCuenta, $tiposCuentaPermitidos)) {
            return [
                'success' => false,
                'message' => 'El tipo de cuenta seleccionado no es válido.',
            ];
        }

        $tipoCuentaDb = $tiposCuentaPermitidos[$tipoCuenta];

        if (empty($data['acepta_terminos']) || empty($data['acepta_tratamiento_datos'])) {
            return [
                'success' => false,
                'message' => 'Debés aceptar los términos y el tratamiento de datos para continuar.',
            ];
        }

        $db = Database::getInstance();

        if ((bool) $db->ejecutar('validarProductor', [':id_user' => $userId])->fetchColumn()) {
            return [
                'success' => false,
                'message' => 'Ya tenés un registro de vendedor activo.',
            ];
        }

        $registrado = (bool) $db->ejecutar('crearProductor', [
            ':tipo_doc' => $tipoDocumento,
            ':id_prod' => $numeroDocumento,
            ':id_user' => $userId,
            ':dir' => $direccion,
            ':pais' => 1,
            ':dpto' => $departamento,
            ':ciudad' => $ciudad,
            ':grupo' => $grupoArtesanal,
            ':banco' => $banco,
            ':cuenta' => $numeroCuenta,
            ':tipo_cuenta' => $tipoCuentaDb,
        ])->fetchColumn();

        if (!$registrado) {
            return [
                'success' => false,
                'message' => 'No se pudo completar el registro. Verificá los datos ingresados.',
            ];
        }

        // Asignar menús de vendedor y revocar "Vender en VIVA"
        $db->ejecutar('asignarMenuUsuario', [':id_user' => $userId, ':id_menu' => 10]); // Mis Productos
        $db->ejecutar('asignarMenuUsuario', [':id_user' => $userId, ':id_menu' => 11]); // Mi Stand
        $db->ejecutar('revocarMenuUsuario', [':id_user' => $userId, ':id_menu' => 9]);  // Vender en VIVA

        return [
            'success' => true,
            'message' => 'Registro de vendedor completado correctamente.',
        ];
    }
}
