/**
 * CartStore.js - Domain Layer
 * Singleton that holds the state of the shopping cart.
 */
class CartStore {
    constructor() {
        if (!CartStore.instance) {
            this.state = {
                items: [],
                resumen: { total_items: 0, total_precio: 0 }
            };
            CartStore.instance = this;
        }
        return CartStore.instance;
    }

    getState() {
        return this.state;
    }

    setState(items, resumen) {
        this.state.items = items;
        this.state.resumen = resumen;
    }

    getTotalItems() {
        return this.state.resumen.total_items;
    }
}

export const cartStore = new CartStore();
