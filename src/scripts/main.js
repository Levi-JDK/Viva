/**
 * main.js
 * 
 * Este es el Entry Point (Punto de Entrada) principal de nuestra aplicación frontend.
 * Siguiendo la arquitectura limpia en Vanilla JS, aquí inicializamos la infraestructura core.
 */

import { eventRouter } from './utils/EventRouter.js';

// Importamos los Controladores (UI Layer)
import { AuthController } from './controllers/AuthController.js';
import { cartController } from './controllers/CartController.js';
import { favoritesController } from './controllers/FavoritesController.js';
import { userMenuController } from './controllers/UserMenuController.js';
import { loginUIController } from './controllers/LoginUIController.js';
import { locationController } from './controllers/LocationController.js';
import { catalogController } from './controllers/CatalogController.js';
import { productDetailController } from './controllers/ProductDetailController.js';
import { adminDashboardController } from './controllers/AdminDashboardController.js';
import { productAdminController } from './controllers/ProductAdminController.js';
import { landingController } from './controllers/LandingController.js';
import { passwordRecoveryController } from './controllers/PasswordRecoveryController.js';
import { profileController } from './controllers/ProfileController.js';
import { vendorRegistrationController } from './controllers/VendorRegistrationController.js';

import { Toast } from './ui/Toast.js';

// Bridge for legacy code that still uses window.showToast
window.showToast = (message, type) => Toast.show(message, type);

// Inicializamos el enrutador de eventos. 
eventRouter.init();

// Inicialización de estados al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    new AuthController();
    cartController.init();
    favoritesController.init();
    userMenuController.init();
    loginUIController.init();
    locationController.init();
    catalogController.init();
    productDetailController.init();
    adminDashboardController.init();
    productAdminController.init();
    landingController.init();
    passwordRecoveryController.init();
    profileController.init();
    vendorRegistrationController.init();
});

// Registramos las acciones en el EventRouter
eventRouter.register('cart-toggle', (e) => { if(e) e.preventDefault(); cartController.toggleCart(); });
eventRouter.register('cart-add', (e, btn) => cartController.addItem(btn));
eventRouter.register('cart-remove', (e, btn) => cartController.removeItem(btn));
eventRouter.register('cart-update', (e, btn) => cartController.updateQuantity(btn));
eventRouter.register('cart-clear', (e) => { if(e) e.preventDefault(); cartController.clearCartPrompt(); });
eventRouter.register('cart-clear-cancel', (e) => { if(e) e.preventDefault(); cartController.cancelClearCart(); });
eventRouter.register('cart-clear-confirm', (e) => { if(e) e.preventDefault(); cartController.executeClearCart(); });

eventRouter.register('fav-toggle', (e, btn) => favoritesController.toggleFavorite(btn, e));

// Additional UI interactions extracted from spaghetti files
eventRouter.register('show-panel', (e, btn) => { if(e) e.preventDefault(); adminDashboardController.showPanel(btn.dataset.panelId || btn.dataset.panel); });
eventRouter.register('toggle-sidebar', (e) => {
    if(e) e.preventDefault();
    if (document.getElementById('admin-sidebar')) adminDashboardController.toggleSidebar();
    else productAdminController.toggleSidebarSeller();
});
eventRouter.register('show-section', (e, btn) => { if(e) e.preventDefault(); productAdminController.showSection(btn.dataset.sectionId); });
eventRouter.register('edit-product', (e, btn) => { if(e) e.preventDefault(); productAdminController.editarProducto(btn.dataset.productId); });
eventRouter.register('delete-product', (e, btn) => { if(e) e.preventDefault(); productAdminController.eliminarProducto(btn.dataset.productId); });
eventRouter.register('preview-background', (e, btn) => productAdminController.previewBackground(btn, btn.dataset.target));
eventRouter.register('preview-image', (e, btn) => productAdminController.previewImage(btn, btn.dataset.target));
eventRouter.register('toggle-password', (e, btn) => passwordRecoveryController.togglePassword(btn.dataset.target, btn));
eventRouter.register('volver-paso-1', (e) => { if(e) e.preventDefault(); passwordRecoveryController.volverAlPaso1(); });
eventRouter.register('scroll-to', (e, btn) => { if(e) e.preventDefault(); landingController.scrollToSection(btn.dataset.target); });

eventRouter.register('enviar-resena', async (e, actionElement) => {
    e.preventDefault();
    const formResena = actionElement.closest('form') || actionElement;
    const id_producto = formResena.dataset.productoId;
    const calificacionInput = document.getElementById('calificacion_input');
    const calificacion = calificacionInput ? calificacionInput.value : formResena.calificacion?.value;
    const texto = formResena.texto.value;

    if (!calificacion) {
        if (typeof showToast !== 'undefined') showToast('Por favor selecciona una calificación de 1 a 5 estrellas.', 'error');
        return;
    }

    try {
        const { ReviewService } = await import('./services/ReviewService.js');
        const data = await ReviewService.submitReview(id_producto, calificacion, texto);
        if (typeof showToast !== 'undefined') showToast(data.mensaje, data.exito ? 'success' : 'error');
        if (data.exito) setTimeout(() => window.location.reload(), 1500);
    } catch (err) {
        if (typeof showToast !== 'undefined') showToast('Error de conexión con el servidor', 'error');
    }
});

eventRouter.register('logout', (e) => {
    e.preventDefault();
    window.location.href = window.BASE_URL + 'logout';
});

eventRouter.register('toggle-edit', (e) => { e.preventDefault(); profileController.toggleEdit(); });
eventRouter.register('save-profile', (e) => { e.preventDefault(); profileController.saveProfile(); });
eventRouter.register('cancel-edit', (e) => { e.preventDefault(); profileController.cancelEdit(); });

eventRouter.register('next-step', (e) => { e.preventDefault(); vendorRegistrationController.nextStep(e); });
eventRouter.register('prev-step', (e) => { e.preventDefault(); vendorRegistrationController.prevStep(e); });

eventRouter.register('departamento-change', (e, el) => locationController.handleDepartamentoChange(el));

eventRouter.register('change-main-image', (e, btn) => {
    e.preventDefault();
    const mainImg = document.getElementById('mainImage');
    if (mainImg && btn.dataset.src) {
        mainImg.src = btn.dataset.src;
    }
});

eventRouter.register('decrease-stock', (e, btn) => {
    e.preventDefault();
    const input = btn.nextElementSibling;
    if (input && input.value > 1) input.value--;
});

eventRouter.register('increase-stock', (e, btn) => {
    e.preventDefault();
    const input = btn.previousElementSibling;
    const max = parseInt(input.max) || Infinity;
    if (input && input.value < max) input.value++;
});

eventRouter.register('close-sidebar', (e) => {
    e.preventDefault();
    adminDashboardController.closeSidebar();
});

console.log('App inicializada con Clean Architecture.');