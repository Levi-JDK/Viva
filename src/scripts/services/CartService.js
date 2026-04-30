import { buildCartAction } from '../domain/CartDomain.js';

/**
 * CartService.js - Service Layer
 * Responsible ONLY for HTTP requests to the cart API.
 * No DOM manipulation or UI logic here.
 */
export class CartService {
    static REDIS_SYNC_ACTION = 'redis_update';
    static REDIS_FLUSH_ACTION = 'flush_to_postgres';
    static debounceTimers = new Map();
    static syncInFlight = false;

    static getEndpoint() {
        if (typeof window.buildAppUrl === 'function') {
            return window.buildAppUrl('api/carrito');
        }

        const baseUrl = String(window.BASE_URL || '');
        const normalizedBaseUrl = baseUrl === '/' ? '' : baseUrl.replace(/\/+$/, '');
        return `${normalizedBaseUrl}/api/carrito`;
    }

    static async request(accion, payload = {}) {
        const url = this.getEndpoint();
        
        try {
            const respuesta = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ accion, ...payload })
            });

            if (respuesta.status === 401 || respuesta.status === 403) {
                return { exito: false, mensaje: 'No autorizado', status: respuesta.status };
            }

            if (!respuesta.ok) throw new Error('Error de red: ' + respuesta.status);
            return await respuesta.json();
        } catch (error) {
            console.error('[CartService] Error:', error);
            throw error;
        }
    }

    static buildPendingPayload(actions) {
        return {
            action: this.REDIS_SYNC_ACTION,
            acciones: actions.map(action => this.buildAction(
                action.accion,
                action.id_producto,
                action.cantidad,
                action.client_ts
            ))
        };
    }

    static buildFlushPayload(forceSync = false, actions = []) {
        const payload = {
            action: this.REDIS_FLUSH_ACTION,
            force_sync: Boolean(forceSync)
        };

        if (Array.isArray(actions) && actions.length > 0) {
            payload.acciones = actions.map(action => this.buildAction(
                action.accion,
                action.id_producto,
                action.cantidad,
                action.client_ts
            ));
        }

        return payload;
    }

    static buildAction(accion, id_producto = null, cantidad = null, client_ts = Date.now()) {
        return buildCartAction(accion, id_producto, cantidad, client_ts);
    }

    static async sendPendingActions(actions) {
        if (!Array.isArray(actions) || actions.length === 0) {
            return { success: true, mode: 'noop' };
        }

        this.syncInFlight = true;

        try {
            const payload = this.buildPendingPayload(actions);
            const response = await this.postJson(payload);

            if (response.success !== true) {
                throw new Error(response.message || 'Redis cart update rechazado.');
            }

            return response;
        } finally {
            this.syncInFlight = false;
        }
    }

    /**
     * Debounces Redis cart writes so rapid UI updates collapse into one request.
     * The caller supplies a snapshot builder to avoid sending stale actions.
     */
    static sendPendingActionsDebounced(getActions, delayMs = 500) {
        return new Promise((resolve, reject) => {
            const key = 'cart-pending-actions';
            const currentTimer = this.debounceTimers.get(key);

            if (currentTimer) {
                window.clearTimeout(currentTimer);
            }

            const timer = window.setTimeout(async () => {
                this.debounceTimers.delete(key);

                try {
                    const actions = typeof getActions === 'function' ? getActions() : [];
                    const response = await this.sendPendingActions(actions);
                    resolve({ response, actions });
                } catch (error) {
                    reject(error);
                }
            }, delayMs);

            this.debounceTimers.set(key, timer);
        });
    }

    static cancelPendingActionsDebounce() {
        const key = 'cart-pending-actions';
        const currentTimer = this.debounceTimers.get(key);

        if (currentTimer) {
            window.clearTimeout(currentTimer);
            this.debounceTimers.delete(key);
        }
    }

    static sendPendingActionsKeepalive(actions) {
        if (!Array.isArray(actions) || actions.length === 0) {
            return { ok: false, mode: 'noop' };
        }

        return this.sendKeepalive(this.buildPendingPayload(actions));
    }

    static flushToPostgresKeepalive(forceSync = false, actions = []) {
        if (this.syncInFlight) {
            return { ok: false, mode: 'sync-in-flight' };
        }

        return this.sendKeepalive(this.buildFlushPayload(forceSync, actions));
    }

    static async flushToPostgres(forceSync = false, actions = []) {
        const response = await this.postJson(this.buildFlushPayload(forceSync, actions));

        if (response.success !== true) {
            throw new Error(response.message || 'Flush a Postgres rechazado.');
        }

        return response;
    }

    static async postJson(payload) {
        const respuesta = await fetch(this.getEndpoint(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        if (!respuesta.ok) {
            throw new Error('Error de red: ' + respuesta.status);
        }

        return await respuesta.json();
    }

    static sendKeepalive(payload) {
        const body = JSON.stringify(payload);
        const url = this.getEndpoint();

        if (typeof navigator !== 'undefined' && typeof navigator.sendBeacon === 'function') {
            try {
                const blob = new Blob([body], { type: 'application/json; charset=UTF-8' });
                const sent = navigator.sendBeacon(url, blob);

                if (sent) {
                    return { ok: true, mode: 'beacon' };
                }
            } catch (error) {
                console.error('[CartService] Beacon error:', error);
            }
        }

        try {
            const promise = fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body
            });

            return { ok: true, mode: 'fetch', promise };
        } catch (error) {
            console.error('[CartService] Keepalive error:', error);
            return { ok: false, mode: 'fetch-error', error };
        }
    }

    static getCart() { return this.request('obtener'); }
    static addItem(id, cantidad) { return this.buildAction('agregar', id, cantidad); }
    static removeItem(id) { return this.buildAction('eliminar', id, null); }
    static updateItem(id, cantidad) { return this.buildAction('actualizar', id, cantidad); }
    static clearCart() { return this.buildAction('limpiar', null, null); }
}
