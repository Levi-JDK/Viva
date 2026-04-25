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
        this.state.items = Array.isArray(items) ? [...items] : [];
        this.state.resumen = {
            total_items: Number(resumen?.total_items ?? 0),
            total_precio: Number(resumen?.total_precio ?? 0)
        };
    }

    getTotalItems() {
        return this.state.resumen.total_items;
    }

    enqueuePendingAction(action) {
        this.state.pendingActions.push({ ...action });
    }

    getPendingActions() {
        return this.state.pendingActions.map(action => ({ ...action }));
    }

    clearPendingActions() {
        this.state.pendingActions = [];
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
