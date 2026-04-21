import { cartStore } from '../domain/CartStore.js';
import { CartService } from '../services/CartService.js';

export class CheckoutController {
    init() {
        const btnPagar = document.getElementById('btn-pagar');

        if (!btnPagar) {
            return;
        }

        btnPagar.addEventListener('click', async () => {
            if (btnPagar.disabled) {
                return;
            }

            await this.handlePagar(btnPagar);
        });
    }

    async handlePagar(btnPagar) {
        const checkoutConfig = window.VIVA_CHECKOUT_EPAYCO;

        if (!checkoutConfig?.handler || !checkoutConfig?.data) {
            throw new Error('Checkout de ePayco no inicializado.');
        }

        const textoOriginal = btnPagar.innerHTML;

        this.setLoadingState(btnPagar, true);

        try {
            const pendingActions = cartStore.getPendingActions();

            await this.forceCheckoutSync(pendingActions);

            if (pendingActions.length > 0) {
                cartStore.clearPendingActions();
                cartStore.markSynced();
            }

            checkoutConfig.handler.open(checkoutConfig.data);
        } catch (error) {
            this.reportCheckoutError(error);
            throw error;
        } finally {
            btnPagar.innerHTML = textoOriginal;
            this.setLoadingState(btnPagar, false);
        }
    }

    async forceCheckoutSync(pendingActions = []) {
        return await CartService.flushToPostgres(true, pendingActions);
    }

    setLoadingState(btnPagar, loading) {
        btnPagar.disabled = loading;

        if (loading) {
            btnPagar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando carrito...';
        }
    }

    reportCheckoutError(error) {
        const message = error instanceof Error ? error.message : 'No se pudo sincronizar el carrito antes del pago.';

        if (typeof window.showToast === 'function') {
            window.showToast(message, 'error');
            return;
        }

        window.alert(message);
    }
}

export const checkoutController = new CheckoutController();
