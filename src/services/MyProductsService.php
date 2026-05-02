<?php

require_once __DIR__ . '/../functions/database.php';
require_once ROOT_PATH . 'src/utils/image_uploader.php';

class MyProductsService
{
    public static function obtenerDashboardContext(int $userId): array
    {
        $db = Database::getInstance();
        $esProductor = (bool) $db->ejecutar('validarProductor', [':id_user' => $userId])->fetchColumn();

        if (!$esProductor) {
            return [
                'es_productor' => false,
                'usuario' => null,
                'nombre_usuario' => 'Usuario',
                'foto_usuario' => 'images/default.jpg',
                'id_productor' => null,
            ];
        }

        $usuario = $db->ejecutar('obtenerUsuarioPorId', [':id' => $userId])->fetch(PDO::FETCH_ASSOC) ?: [];
        $idProductor = $db->ejecutar('obtenerIdProductor', [':id_user' => $userId])->fetchColumn();

        return [
            'es_productor' => true,
            'usuario' => $usuario,
            'nombre_usuario' => $usuario['nom_user'] ?? 'Usuario',
            'foto_usuario' => !empty($usuario['foto_user']) ? $usuario['foto_user'] : 'images/default.jpg',
            'id_productor' => $idProductor ? (int) $idProductor : null,
        ];
    }

    public static function obtenerInventario(int $idProductor): array
    {
        $productos = self::obtenerProductosProcesados($idProductor);

        return [
            'productos' => $productos,
            'total_productos' => count($productos),
            'productos_activos' => count(array_filter($productos, static fn(array $producto): bool => !empty($producto['activo']))),
            'vistas_totales' => array_sum(array_column($productos, 'vistas')),
        ];
    }

    public static function obtenerEstadisticas(int $idProductor): array
    {
        $productos = self::obtenerProductosProcesados($idProductor);
        $topProductos = $productos;

        usort($topProductos, static fn(array $a, array $b): int => ($b['vistas'] ?? 0) <=> ($a['vistas'] ?? 0));
        $topProductos = array_slice($topProductos, 0, 3);

        return [
            'stats' => [
                'total_productos' => count($productos),
                'productos_activos' => count(array_filter($productos, static fn(array $producto): bool => !empty($producto['activo']))),
                'productos_inactivos' => count(array_filter($productos, static fn(array $producto): bool => empty($producto['activo']))),
                'vistas_totales' => array_sum(array_column($productos, 'vistas')),
                'stock_total' => array_sum(array_column($productos, 'stock_productor')),
                'promedio_vistas' => count($productos) > 0 ? round(array_sum(array_column($productos, 'vistas')) / count($productos), 1) : 0,
            ],
            'top_productos' => $topProductos,
        ];
    }

    public static function obtenerStand(int $idProductor): array
    {
        $db = Database::getInstance();
        $stand = $db->ejecutar('obtenerStandPrivado', [':id_p' => $idProductor])->fetch(PDO::FETCH_ASSOC);

        return $stand ?: [];
    }

    public static function guardarStand(int $userId, array $post, array $files): array
    {
        if ($userId <= 0) {
            throw new RuntimeException('Usuario no autenticado');
        }

        $db = Database::getInstance();
        $idProductor = $db->ejecutar('obtenerIdProductor', [':id_user' => $userId])->fetchColumn();

        if (!$idProductor) {
            throw new RuntimeException('Productor no encontrado');
        }

        $idStand = $db->ejecutar('verificarStand', [':id_p' => $idProductor])->fetchColumn();
        $imagenes = self::procesarImagenesStand((int) $idProductor, $files);

        $nomStand = self::normalizarTexto($post['nom_stand'] ?? null);
        $sloganStand = self::normalizarTexto($post['slogan_stand'] ?? null);
        $descripcionStand = self::normalizarTexto($post['descripcion_stand'] ?? null);

        if ($idStand) {
            // Si no se subió imagen nueva, mantener la existente
            $standActual = self::obtenerStand((int) $idProductor);
            $imgStand = $imagenes['img_stand'] ?? $standActual['img_stand'] ?? null;
            $portadaStand = $imagenes['portada_stand'] ?? $standActual['portada_stand'] ?? null;

            $stmt = $db->ejecutar('actualizarStand', [
                ':id_productor' => $idProductor,
                ':id_stand' => $idStand,
                ':nom_stand' => $nomStand,
                ':slogan_stand' => $sloganStand,
                ':descripcion_stand' => $descripcionStand,
                ':img_stand' => $imgStand,
                ':portada_stand' => $portadaStand,
            ]);

            return self::normalizarRespuestaStand($stmt->fetchColumn(), 'Stand actualizado correctamente');
        }

        $stmt = $db->ejecutar('registrarStand', [
            ':id_productor' => $idProductor,
            ':nom_stand' => $nomStand ?: 'Mi Emprendimiento',
            ':slogan_stand' => $sloganStand ?: 'Bienvenidos a mi stand virtual',
            ':descripcion_stand' => $descripcionStand ?: 'Aquí encontrarás mis mejores productos artesanales.',
            ':img_stand' => $imagenes['img_stand'] ?: 'images/default.jpg',
            ':portada_stand' => $imagenes['portada_stand'] ?: 'images/default_cover.jpg',
        ]);

        return self::normalizarRespuestaStand($stmt->fetchColumn(), 'Stand creado correctamente');
    }

    public static function obtenerDatosFormularioProducto(int $idProductor, ?int $idProductoEditar = null): array
    {
        $db = Database::getInstance();
        $productoEditar = null;

        if ($idProductoEditar !== null) {
            $productoEditar = $db->ejecutar('obtenerProductoPorId', [':id_producto' => $idProductoEditar])->fetch(PDO::FETCH_ASSOC);

            if (!$productoEditar || (int) ($productoEditar['id_productor'] ?? 0) !== $idProductor) {
                return [
                    'debe_redirigir' => true,
                    'categorias' => [],
                    'colores' => [],
                    'oficios' => [],
                    'materias' => [],
                    'producto_editar' => null,
                ];
            }
        }

        return [
            'debe_redirigir' => false,
            'categorias' => $db->ejecutar('obtenerCategorias')->fetchAll(PDO::FETCH_ASSOC),
            'colores' => $db->ejecutar('obtenerColores')->fetchAll(PDO::FETCH_ASSOC),
            'oficios' => $db->ejecutar('obtenerOficios')->fetchAll(PDO::FETCH_ASSOC),
            'materias' => $db->ejecutar('obtenerMaterias')->fetchAll(PDO::FETCH_ASSOC),
            'producto_editar' => $productoEditar,
        ];
    }

    private static function obtenerProductosProcesados(int $idProductor): array
    {
        if ($idProductor <= 0) {
            return [];
        }

        $db = Database::getInstance();
        $productosRaw = $db->ejecutar('obtenerProductos', [':id_productor' => $idProductor])->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $producto): array {
            $imagenes = json_decode($producto['imagenes'] ?? '[]', true);
            $producto['imagen'] = 'images/default.webp';
            $producto['vistas'] = (int) ($producto['vistas'] ?? 0);
            $producto['stock_productor'] = (int) ($producto['stock_productor'] ?? 0);
            $producto['activo'] = !empty($producto['activo']);

            if (is_array($imagenes) && !empty($imagenes[0]['url'])) {
                $producto['imagen'] = $imagenes[0]['url'];
            }

            return $producto;
        }, $productosRaw);
    }

    private static function procesarImagenesStand(int $idProductor, array $files): array
    {
        $targetDir = ROOT_PATH . 'images/stands/';
        $prefix = 'stand_' . $idProductor . '_';

        return [
            'img_stand' => self::subirImagenStand($files['img_stand'] ?? null, $targetDir, $prefix . 'logo_'),
            'portada_stand' => self::subirImagenStand($files['portada_stand'] ?? null, $targetDir, $prefix . 'cover_'),
        ];
    }

    private static function subirImagenStand(?array $archivo, string $targetDir, string $prefix): ?string
    {
        if (!$archivo) {
            return null;
        }

        $error = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException('La imagen supera el tamaño máximo permitido (5MB).');
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir la imagen (código ' . $error . ').');
        }

        $resultado = handleImageUpload($archivo, $targetDir, $prefix, 'images/stands/');

        if (!$resultado['success']) {
            throw new RuntimeException('Error de imagen: ' . $resultado['message']);
        }

        return $resultado['path'] ?? ($resultado['paths'][0] ?? null);
    }

    private static function normalizarRespuestaStand(mixed $resultado, string $message): array
    {
        return [
            'success' => $resultado === true || $resultado === 't',
            'message' => ($resultado === true || $resultado === 't') ? $message : 'Error al guardar en base de datos',
        ];
    }

    private static function normalizarTexto(?string $valor): ?string
    {
        $valor = is_string($valor) ? trim($valor) : null;

        return $valor === '' ? null : $valor;
    }
}
