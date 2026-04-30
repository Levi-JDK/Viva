import { buildCartSummary, normalizeCartItems, normalizeCartItem, sanitizeCartProductId, sanitizeCartQuantity } from './CartDomain.js';

/**
 * CartStore.js - Domain Layer
 * Singleton that holds the state of the shopping cart.
 */
class CartStore {
    constructor() {
        if (!CartStore.instance) {
            this.state = {
                items: [],
                resumen: { total_items: 0, total_precio: 0 },
                pendingActions: [],
                lastSyncedAt: null,
                isFlushing: false
            };
            CartStore.instance = this;
        }
        return CartStore.instance;
    }

    getState() {
        return this.state;
    }

    setState(items, resumen) {
        this.state.items = normalizeCartItems(items);
        this.state.resumen = resumen
            ? {
                total_items: Number(resumen?.total_items ?? 0),
                total_precio: Number(resumen?.total_precio ?? 0)
            }
            : buildCartSummary(this.state.items);
    }

    getTotalItems() {
        return this.state.resumen.total_items;
    }

    /**
     * Reconciles a fresh server cart without clobbering local optimistic state.
     * Local cart items remain the source of truth for the current page session;
     * server data only enriches metadata and contributes items missing locally.
     */
    mergeServerStatePreservingPending(items, resumen) {
        const localItems = normalizeCartItems(this.state.items);
        const serverItems = normalizeCartItems(items);
        const serverItemsById = new Map(serverItems.map(item => [item.id_producto, item]));
        const mergedItems = localItems.map(localItem => {
            const serverItem = serverItemsById.get(localItem.id_producto);
            if (!serverItem) {
                return localItem;
            }

            const precioUnitario = Number(serverItem.precio_unitario || localItem.precio_unitario || 0);

            return {
                ...localItem,
                nom_producto: serverItem.nom_producto || localItem.nom_producto,
                imagen: serverItem.imagen || localItem.imagen,
                precio_unitario: precioUnitario,
                subtotal: precioUnitario * localItem.cantidad
            };
        });

        serverItems.forEach(serverItem => {
            const existsLocally = localItems.some(localItem => localItem.id_producto === serverItem.id_producto);
            if (!existsLocally) {
                mergedItems.push(serverItem);
            }
        });

        this.state.items = mergedItems;
        this.state.resumen = buildCartSummary(mergedItems);
    }

    enqueuePendingAction(action) {
        if (!action || !action.accion) {
            return;
        }

        this.state.pendingActions.push({ ...action });
    }

    addItemOptimistic(item) {
        const normalizedItem = normalizeCartItem(item);
        if (normalizedItem.id_producto === null) {
            return;
        }

        const existingItem = this.state.items.find(({ id_producto }) => id_producto === normalizedItem.id_producto);

        if (existingItem) {
            existingItem.cantidad += normalizedItem.cantidad;
            existingItem.subtotal = existingItem.precio_unitario * existingItem.cantidad;
        } else {
            this.state.items = [...this.state.items, normalizedItem];
        }

        this.state.resumen = buildCartSummary(this.state.items);
    }

    removeItemOptimistic(idProducto) {
        const normalizedId = sanitizeCartProductId(idProducto);
        if (normalizedId === null) {
            return;
        }

        this.state.items = this.state.items.filter(item => item.id_producto !== normalizedId);
        this.state.resumen = buildCartSummary(this.state.items);
    }

    updateItemQuantityOptimistic(idProducto, cantidad) {
        const normalizedId = sanitizeCartProductId(idProducto);
        const normalizedCantidad = sanitizeCartQuantity(cantidad);

        if (normalizedId === null) {
            return;
        }

        this.state.items = this.state.items.map(item => {
            if (item.id_producto !== normalizedId) {
                return { ...item };
            }

            return {
                ...item,
                cantidad: normalizedCantidad,
                subtotal: Number(item.precio_unitario || 0) * normalizedCantidad
            };
        });

        this.state.resumen = buildCartSummary(this.state.items);
    }

    clearItemsOptimistic() {
        this.state.items = [];
        this.state.resumen = buildCartSummary([]);
    }

    getPendingActions() {
        return this.state.pendingActions.map(action => ({ ...action }));
    }

    clearPendingActions() {
        this.state.pendingActions = [];
    }


    clearSyncedPendingActions(actions) {
        if (!Array.isArray(actions) || actions.length === 0) {
            return;
        }

        const syncedKeys = new Set(actions.map(action => this.buildPendingActionKey(action)));
        this.state.pendingActions = this.state.pendingActions.filter(action => !syncedKeys.has(this.buildPendingActionKey(action)));
    }

    buildPendingActionKey(action) {
        return [
            action?.accion || '',
            action?.id_producto ?? '',
            action?.cantidad ?? '',
            action?.client_ts ?? ''
        ].join('|');
    }

    restorePendingActions(actions) {
        this.state.pendingActions = Array.isArray(actions)
            ? actions.map(action => ({ ...action }))
            : [];
    }

    hasPendingActions() {
        return this.state.pendingActions.length > 0;
    }

    setFlushing(isFlushing) {
        this.state.isFlushing = Boolean(isFlushing);
    }

    markSynced() {
        this.state.lastSyncedAt = Date.now();
    }
}

export const cartStore = new CartStore();
