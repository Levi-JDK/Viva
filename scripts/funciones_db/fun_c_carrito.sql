CREATE OR REPLACE FUNCTION fun_carrito(
    p_id_user     INTEGER,
    p_accion      VARCHAR,
    p_id_producto DECIMAL(12,0) DEFAULT NULL,
    p_cantidad    INTEGER       DEFAULT NULL
)
RETURNS JSON
LANGUAGE plpgsql
AS $$
DECLARE
    v_stock         DECIMAL(12,0);  -- Stock disponible del producto
    v_activo        BOOLEAN;        -- Si el producto está activo
    v_existe        BOOLEAN;        -- Si el ítem ya existe en el carrito
    v_cant_actual   INTEGER;        -- Cantidad actual en carrito
    v_resultado     JSON;           -- JSON de respuesta final
BEGIN

    -- ============================================================
    -- VALIDACIÓN GENERAL: El usuario debe existir
    -- ============================================================
    IF NOT EXISTS (SELECT 1 FROM tab_users WHERE id_user = p_id_user) THEN
        RETURN json_build_object(
            'exito', FALSE,
            'mensaje', 'Usuario no encontrado'
        );
    END IF;

    -- ============================================================
    -- ACCIÓN: OBTENER → Solo retorna el carrito sin modificar
    -- ============================================================
    IF p_accion = 'obtener' THEN
        -- No se hace nada, salta directo al RETURN final
        NULL;

    -- ============================================================
    -- ACCIÓN: LIMPIAR → Elimina todos los ítems del carrito
    -- ============================================================
    ELSIF p_accion = 'limpiar' THEN
        DELETE FROM tab_carrito WHERE id_user = p_id_user;

    -- ============================================================
    -- ACCIÓN: AGREGAR → Agrega producto o incrementa cantidad
    -- ============================================================
    ELSIF p_accion = 'agregar' THEN

        -- Validar que se proporcionó un producto
        IF p_id_producto IS NULL THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'Se requiere id_producto para agregar');
        END IF;

        -- Validar que se proporcionó cantidad válida
        IF p_cantidad IS NULL OR p_cantidad < 1 THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'La cantidad debe ser mayor a 0');
        END IF;

        -- Verificar que el producto existe y está activo con stock
        SELECT stock_productor, is_active
        INTO v_stock, v_activo
        FROM tab_productos
        WHERE id_producto = p_id_producto AND is_deleted = FALSE;

        IF NOT FOUND THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'El producto no existe');
        END IF;

        IF NOT v_activo THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'El producto no está disponible');
        END IF;

        -- Verificar si ya existe en el carrito para calcular el total a agregar
        SELECT EXISTS(
            SELECT 1 FROM tab_carrito
            WHERE id_user = p_id_user AND id_producto = p_id_producto
        ) INTO v_existe;

        IF v_existe THEN
            SELECT cantidad INTO v_cant_actual
            FROM tab_carrito
            WHERE id_user = p_id_user AND id_producto = p_id_producto;
        ELSE
            v_cant_actual := 0;
        END IF;

        -- Validar que la cantidad total no supere el stock disponible
        IF (v_cant_actual + p_cantidad) > v_stock THEN
            RETURN json_build_object(
                'exito', FALSE,
                'mensaje', 'Stock insuficiente. Disponible: ' || v_stock || ', En carrito: ' || v_cant_actual
            );
        END IF;

        -- Insertar o actualizar (UPSERT)
        INSERT INTO tab_carrito (id_user, id_producto, cantidad)
        VALUES (p_id_user, p_id_producto, p_cantidad)
        ON CONFLICT (id_user, id_producto)
        DO UPDATE SET
            cantidad    = tab_carrito.cantidad + EXCLUDED.cantidad,
            agregado_at = CURRENT_TIMESTAMP;

    -- ============================================================
    -- ACCIÓN: ELIMINAR → Borra directamente un ítem del carrito
    -- ============================================================
    ELSIF p_accion = 'eliminar' THEN

        IF p_id_producto IS NULL THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'Se requiere id_producto para eliminar');
        END IF;

        -- Verificar que el ítem existe antes de eliminar
        IF NOT EXISTS (
            SELECT 1 FROM tab_carrito
            WHERE id_user = p_id_user AND id_producto = p_id_producto
        ) THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'El producto no estaba en el carrito');
        END IF;

        DELETE FROM tab_carrito
        WHERE id_user = p_id_user AND id_producto = p_id_producto;

    -- ============================================================
    -- ACCIÓN: ACTUALIZAR → Cambia la cantidad exacta de un ítem
    -- ============================================================
    ELSIF p_accion = 'actualizar' THEN

        IF p_id_producto IS NULL THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'Se requiere id_producto para actualizar');
        END IF;

        IF p_cantidad IS NULL OR p_cantidad < 1 THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'La cantidad debe ser mayor a 0');
        END IF;

        -- Verificar stock disponible para la nueva cantidad
        SELECT stock_productor INTO v_stock
        FROM tab_productos
        WHERE id_producto = p_id_producto AND is_deleted = FALSE;

        IF NOT FOUND THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'El producto no existe');
        END IF;

        IF p_cantidad > v_stock THEN
            RETURN json_build_object(
                'exito', FALSE,
                'mensaje', 'Cantidad supera el stock. Disponible: ' || v_stock
            );
        END IF;

        UPDATE tab_carrito
        SET cantidad = p_cantidad, agregado_at = CURRENT_TIMESTAMP
        WHERE id_user = p_id_user AND id_producto = p_id_producto;

        IF NOT FOUND THEN
            RETURN json_build_object('exito', FALSE, 'mensaje', 'El producto no estaba en el carrito');
        END IF;

    ELSE
        -- Acción no reconocida
        RETURN json_build_object(
            'exito', FALSE,
            'mensaje', 'Acción no válida. Use: obtener, agregar, eliminar, actualizar, limpiar'
        );
    END IF;

    -- ============================================================
    -- RETORNO FINAL: Estado actualizado del carrito como JSON
    -- ============================================================
    SELECT json_build_object(
        'exito',   TRUE,
        'mensaje', 'OK',
        'carrito', COALESCE(
            (
                SELECT json_agg(
                    json_build_object(
                        'id_producto',    c.id_producto,
                        'nom_producto',   p.nom_producto,
                        'precio_unitario', p.precio_producto,
                        'cantidad',       c.cantidad,
                        'subtotal',       (p.precio_producto * c.cantidad),
                        'imagen',         COALESCE(
                            (SELECT url_imagen FROM tab_imagenes
                             WHERE id_producto = p.id_producto
                             ORDER BY id_imagen ASC LIMIT 1),
                            'images/default.jpg'
                        )
                    )
                    ORDER BY c.agregado_at ASC
                )
                FROM tab_carrito c
                JOIN tab_productos p ON p.id_producto = c.id_producto
                WHERE c.id_user = p_id_user
            ),
            '[]'::json
        ),
        'resumen', (
            SELECT json_build_object(
                'total_items', COALESCE(SUM(c.cantidad), 0),
                'total_precio', COALESCE(SUM(p.precio_producto * c.cantidad), 0)
            )
            FROM tab_carrito c
            JOIN tab_productos p ON p.id_producto = c.id_producto
            WHERE c.id_user = p_id_user
        )
    ) INTO v_resultado;

    RETURN v_resultado;

END;
$$;