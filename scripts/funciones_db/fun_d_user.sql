-- Soft delete de usuario 
CREATE OR REPLACE FUNCTION fun_d_user(
    p_id_user tab_users.id_user%TYPE
) RETURNS BOOLEAN AS $$
BEGIN
    -- 1) Marcar usuario como eliminado
    UPDATE tab_users
       SET is_deleted = TRUE
     WHERE id_user = p_id_user;

    IF NOT FOUND THEN
        -- Validacion: No existe usuario con id
        RETURN FALSE;
    END IF;

    --  Marcar productor asociados como eliminados
    UPDATE tab_productores
       SET is_deleted = TRUE
     WHERE id_user = p_id_user;

    --  Marcar oferta asociada a esos productores como eliminada
    UPDATE tab_producto_productor ppp
       SET is_deleted = TRUE
     WHERE ppp.id_productor IN (
         SELECT pr.id_productor
           FROM tab_productores pr
          WHERE pr.id_user = p_id_user
     );
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

-- SELECT fun_d_user(2);
-- SELECT id_user FROM tab_users WHERE is_deleted = TRUE;
-- select * from tab_producto_productor

-- SELECT  prd.id_producto,
--         prd.nom_producto,
--         ppp.id_productor,
--         ppp.precio_prod,
--         ppp.stock_prod,
-- 		u.id_user
-- FROM tab_producto_productor ppp
-- JOIN tab_productores       pr  ON pr.id_productor = ppp.id_productor
-- JOIN tab_users             u   ON u.id_user      = pr.id_user
-- JOIN tab_productos         prd ON prd.id_producto = ppp.id_producto
-- WHERE ppp.is_deleted = TRUE
--   AND pr.is_deleted  = TRUE
--   AND u.is_deleted   = TRUE
 