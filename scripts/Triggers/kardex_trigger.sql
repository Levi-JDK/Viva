DROP TRIGGER IF EXISTS trg_kardex_actualizar_stock ON tab_kardex;

CREATE TRIGGER trg_kardex_actualizar_stock
AFTER INSERT ON tab_kardex
FOR EACH ROW
EXECUTE FUNCTION fun_trg_kardex_stock();