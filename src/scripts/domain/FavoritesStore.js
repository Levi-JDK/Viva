/**
 * FavoritesStore.js - Domain Layer
 * Singleton that holds the Set of active favorite product IDs.
 */
class FavoritesStore {
    constructor() {
        if (!FavoritesStore.instance) {
            this.activeFavorites = new Set();
            FavoritesStore.instance = this;
        }
        return FavoritesStore.instance;
    }

    getFavorites() {
        return this.activeFavorites;
    }

    setFavorites(arrayIds) {
        this.activeFavorites = new Set(arrayIds.map(id => parseInt(id)));
    }

    has(id) {
        return this.activeFavorites.has(parseInt(id));
    }

    add(id) {
        this.activeFavorites.add(parseInt(id));
    }

    remove(id) {
        this.activeFavorites.delete(parseInt(id));
    }
}

export const favoritesStore = new FavoritesStore();
