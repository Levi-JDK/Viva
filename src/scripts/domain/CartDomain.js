const DEFAULT_CART_QTY = 1;

export function sanitizeCartQuantity(value, fallback = DEFAULT_CART_QTY) {
    const parsed = Number.parseInt(value, 10);
    return Number.isInteger(parsed) && parsed >= 1 ? parsed : fallback;
}

export function sanitizeCartProductId(value) {
    const parsed = Number.parseInt(value, 10);
    return Number.isInteger(parsed) && parsed >= 1 ? parsed : null;
}

export function normalizeCartItem(item = {}) {
    const cantidad = sanitizeCartQuantity(item.cantidad, DEFAULT_CART_QTY);
    const precioUnitario = Number(item.precio_unitario || 0);
    const subtotal = Number(item.subtotal);

    return {
        ...item,
        id_producto: sanitizeCartProductId(item.id_producto),
        cantidad,
        precio_unitario: precioUnitario,
        subtotal: Number.isFinite(subtotal) ? subtotal : precioUnitario * cantidad
    };
}

export function normalizeCartItems(items = []) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items
        .map(normalizeCartItem)
        .filter(item => item.id_producto !== null);
}

export function buildCartSummary(items = []) {
    return normalizeCartItems(items).reduce((acc, item) => {
        acc.total_items += item.cantidad;
        acc.total_precio += Number(item.subtotal || 0);
        return acc;
    }, { total_items: 0, total_precio: 0 });
}

export function buildCartAction(accion, idProducto = null, cantidad = null, clientTs = Date.now()) {
    return {
        accion,
        id_producto: idProducto === null ? null : sanitizeCartProductId(idProducto),
        cantidad: cantidad === null ? null : sanitizeCartQuantity(cantidad),
        client_ts: clientTs
    };
}
