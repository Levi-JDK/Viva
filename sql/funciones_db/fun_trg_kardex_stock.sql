CREATE OR REPLACE FUNCTION fun_trg_kardex_stock()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.tipo_movim = TRUE THEN
        -- Entrada: sumar al stock
        UPDATE tab_productos
           SET stock_productor = stock_productor + NEW.cantidad
         WHERE id_producto = NEW.id_producto
           AND id_productor = NEW.id_productor
           AND is_deleted = FALSE;
    ELSE
        -- Salida (venta): restar del stock y desactivar si llega a 0
        UPDATE tab_productos
           SET stock_productor = stock_productor - NEW.cantidad,
               is_active = CASE 
                             WHEN (stock_productor - NEW.cantidad) <= 0 THEN FALSE 
                             ELSE is_active 
                           END
         WHERE id_producto = NEW.id_producto
           AND id_productor = NEW.id_productor
           AND is_deleted = FALSE;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;