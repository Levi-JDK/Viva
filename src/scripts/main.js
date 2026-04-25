/**
 * main.js
 * 
 * Este es el Entry Point (Punto de Entrada) principal de nuestra aplicación frontend.
 * Siguiendo la arquitectura limpia en Vanilla JS, aquí inicializamos la infraestructura core.
 */

import { eventRouter } from './utils/EventRouter.js';

// Importamos los Controladores (UI Layer)
import { authController } from './controllers/AuthController.js';
import { cartController } from './controllers/CartController.js';
import { favoritesController } from './controllers/FavoritesController.js';
import { userMenuController } from './controllers/UserMenuController.js';
import { locationController } from './controllers/LocationController.js';
import { catalogController } from './controllers/CatalogController.js';
import { productDetailController } from './controllers/ProductDetailController.js';
import { checkoutController } from './controllers/CheckoutController.js';
import { adminDashboardController } from './controllers/AdminDashboardController.js';
import { adminMenusController } from './controllers/AdminMenusController.js';
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
    authController.init();
    cartController.init();
    favoritesController.init();
    userMenuController.init();
    locationController.init();
    catalogController.init();
    productDetailController.init();
    checkoutController.init();
    adminDashboardController.init();
    adminMenusController.init();
    productAdminController.init();
    landingController.init();
    passwordRecoveryController.init();
    profileController.init();
    vendorRegistrationController.init();
});

// Registramos las acciones en el EventRouter
eventRouter.register('cart-toggle', (e) => { if(e) e.preventDefault(); cartController.toggleCart(); });
eventRouter.register('cart-add', (e, btn) => cartController.addItem(btn));
eventRouter.register('add-cart', (e, btn) => cartController.addItem(btn));
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
eventRouter.register('show-section', (e, btn) => { 
    if(e) e.preventDefault(); 
    const sectionId = btn.dataset.sectionId;
    if (document.getElementById('profile') && profileController.showSection) {
        profileController.showSection(sectionId);
    } else if (document.getElementById('admin-content') && productAdminController.showSection) {
        productAdminController.showSection(sectionId);
    }
});
eventRouter.register('edit-product', (e, btn) => { if(e) e.preventDefault(); productAdminController.editarProducto(btn.dataset.productId); });
eventRouter.register('delete-product', (e, btn) => { if(e) e.preventDefault(); productAdminController.eliminarProducto(btn.dataset.productId); });
eventRouter.register('preview-background', (e, btn) => productAdminController.previewBackground(btn, btn.dataset.target));
eventRouter.register('preview-image', (e, btn) => productAdminController.previewImage(btn, btn.dataset.target));
eventRouter.register('toggle-password', (e, btn) => passwordRecoveryController.togglePassword(btn.dataset.target, btn));
eventRouter.register('volver-paso-1', (e) => { if(e) e.preventDefault(); passwordRecoveryController.volverAlPaso1(); });
eventRouter.register('scroll-to', (e, btn) => { if(e) e.preventDefault(); landingController.scrollToSection(btn.dataset.target); });

eventRouter.register('enviar-resena', (e, form) => productDetailController.submitReview(e, form));

eventRouter.register('logout', (e) => {
    e.preventDefault();
    window.location.href = window.BASE_URL + 'logout';
});

eventRouter.register('toggle-edit', (e) => { e.preventDefault(); profileController.toggleEdit(); });
eventRouter.register('save-profile', (e) => { e.preventDefault(); profileController.saveProfile(); });
eventRouter.register('cancel-edit', (e) => { e.preventDefault(); profileController.cancelEdit(); });
eventRouter.register('trigger-profile-upload', (e) => { e.preventDefault(); profileController.triggerProfileUpload(); });
eventRouter.register('profile-upload-change', (e, el) => { profileController.handleProfileUpload(e, el); });

eventRouter.register('next-step', (e) => { e.preventDefault(); vendorRegistrationController.nextStep(e); });
eventRouter.register('prev-step', (e) => { e.preventDefault(); vendorRegistrationController.prevStep(e); });
eventRouter.register('submit-vendor-registration', (e, form) => vendorRegistrationController.handleSubmit(e));

eventRouter.register('submit-solicitar', (e, form) => passwordRecoveryController.submitSolicitar(e, form));
eventRouter.register('submit-confirmar', (e, form) => passwordRecoveryController.submitConfirmar(e, form));
eventRouter.register('token-input', (e, el) => passwordRecoveryController.handleTokenInput(e, el));
eventRouter.register('token-keydown', (e, el) => passwordRecoveryController.handleTokenKeydown(e, el));
eventRouter.register('pass-input', (e, el) => passwordRecoveryController.handlePassInput(e, el));
eventRouter.register('pass-keydown', (e, el) => passwordRecoveryController.handlePassKeydown(e, el));

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

eventRouter.register('scrollToReviews', (e) => { e.preventDefault(); productDetailController.scrollToReviews(); });
eventRouter.register('toggleReviewForm', (e) => { e.preventDefault(); productDetailController.toggleReviewForm(); });
eventRouter.register('hideReviewForm', (e) => { e.preventDefault(); productDetailController.hideReviewForm(); });
eventRouter.register('preventEmptyStand', (e) => { productAdminController.preventEmptyStand(e); });
eventRouter.register('triggerPortadaUpload', (e) => { e.preventDefault(); productAdminController.triggerPortadaUpload(); });
eventRouter.register('triggerLogoUpload', (e) => { e.preventDefault(); productAdminController.triggerLogoUpload(); });
eventRouter.register('closeSidebar', (e) => { e.preventDefault(); adminDashboardController.closeSidebar(); });
eventRouter.register('toggleMobileMenu', (e) => { e.preventDefault(); userMenuController.toggleMobileMenu(); });
eventRouter.register('toggle-user-menu', (e) => { userMenuController.toggleDropdown(e); });
eventRouter.register('toggleSidebar', (e) => { e.preventDefault(); profileController.toggleSidebar(); });
eventRouter.register('triggerImageUpload', (e) => { e.preventDefault(); productAdminController.triggerImageUpload(); });
eventRouter.register('goBack', (e) => { e.preventDefault(); history.back(); });

eventRouter.register('numeric-keydown', (e, el) => productAdminController.handleNumericKeydown(e, el));
eventRouter.register('numeric-input', (e, el) => productAdminController.handleNumericInput(e, el));
eventRouter.register('product-images-change', (e, el) => productAdminController.handleImageSelection(e, el));
eventRouter.register('submit-product', (e, form) => productAdminController.submitProduct(e, form));
eventRouter.register('submit-stand', (e, form) => productAdminController.submitStand(e, form));
eventRouter.register('misc-keypress', (e, el) => productAdminController.handleMiscKeypress(e, el));

eventRouter.register('register', (e, form) => { e.preventDefault(); authController.handleRegister(form); });
eventRouter.register('login', (e, form) => { e.preventDefault(); authController.handleLogin(form); });

// Admin Dashboard: Users & Products panels
eventRouter.register('toggle-user', (e, btn) => { e.preventDefault(); adminDashboardController.handleToggleUser(btn); });
eventRouter.register('toggle-product', (e, btn) => { e.preventDefault(); adminDashboardController.handleToggleProduct(btn); });
eventRouter.register('search-usuarios-input', (e, el) => adminDashboardController.filterUsuarios(el.value));
eventRouter.register('search-productos-input', (e, el) => adminDashboardController.filterProductos(el.value));

// Admin Dashboard: Menús panel
eventRouter.register('menus-usuario-select', (e, el) => adminMenusController.onUserSelect(el));
eventRouter.register('gestionar-menu', (e, btn) => { e.preventDefault(); adminDashboardController.handleGestionMenu(btn); });

