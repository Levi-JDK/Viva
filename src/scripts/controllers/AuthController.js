import { Toast } from '../ui/Toast.js';
import { AuthValidator } from '../domain/AuthValidator.js';
import { ApiService } from '../services/ApiService.js';

export class AuthController {
    constructor() {
        this.registerSuccessKey = 'viva:auth:register-success';
        this.authSlideKey = 'auth_slide';
        this.authShellSelector = '#auth-shell';
        this.swapDuration = 300;
        this.entryTransitionClasses = ['transform-gpu', 'transition-transform', 'transition-opacity', 'duration-300', 'ease-out'];
        this.exitTransitionClasses = ['transform-gpu', 'transition-transform', 'transition-opacity', 'duration-300', 'ease-out'];
        this.initialStateClasses = ['transform-gpu', 'will-change-transform', 'opacity-0'];
        this.prefetchCache = new Map();
        this.prefetchRequests = new Map();
        this.isSwapping = false;
    }

    init() {
        document.body.classList.add('overflow-x-hidden');
        this.prefetchOppositeView();
        this.bindNavigationTransitions();
        this.hydrateLoginTransition();
        this.hydrateRegisterTransition();
    }

    async handleRegister(form) {
        const contrasena = form.querySelector('input[name="contrasena"]').value;
        const errorContrasena = AuthValidator.validatePassword(contrasena);

        if (errorContrasena) {
            Toast.show(errorContrasena, 'error');
            return;
        }

        const nombre = form.querySelector('input[name="nombre"]').value;
        const apellido = form.querySelector('input[name="apellido"]').value;

        const errorNombre = AuthValidator.validateName(nombre);
        if (errorNombre) {
            Toast.show(errorNombre, 'error');
            return;
        }

        const errorApellido = AuthValidator.validateLastName(apellido);
        if (errorApellido) {
            Toast.show(errorApellido, 'error');
            return;
        }

        const email = form.querySelector('input[name="email"]').value.trim();
        const formData = new FormData(form);
        formData.append('accion', 'registro');

        try {
            const data = await ApiService.post(BASE_URL + 'registro', formData);
            const type = data.clase === 'mensaje-exito' ? 'success' : 'error';

            Toast.show(data.mensaje, type);

            if (data.clase === 'mensaje-exito') {
                const redirectUrl = form.querySelector('input[name="redirect"]')?.value?.trim() || '';

                this.persistRegisterSuccess(email, redirectUrl);
                this.runRegisterToLoginTransition(form);
            }
        } catch (error) {
            Toast.show("Error en la conexión con el servidor", "error");
            throw error;
        }
    }

    async handleLogin(form) {
        const formData = new FormData(form);
        formData.append('accion', 'login');

        const hiddenRedirect = form.querySelector('input[name="redirect"]')?.value || '';
        const urlRedirect = new URLSearchParams(window.location.search).get('redirect') || '';
        const redirectUrl = hiddenRedirect || urlRedirect;

        try {
            const data = await ApiService.post(BASE_URL + 'login', formData);
            const type = data.clase === 'mensaje-exito' ? 'success' : 'error';

            Toast.show(data.mensaje, type);

            if (data.clase === 'mensaje-exito') {
                form.reset();
                const destino = redirectUrl || BASE_URL;
                form.classList.add('pointer-events-none');
                this.runSlideOutTransition('login', 'translate-x-[100vw]');

                setTimeout(() => {
                    window.location.href = destino;
                }, 320);
            }
        } catch (error) {
            Toast.show("Error en la conexión con el servidor", "error");
            throw error;
        }
    }

    persistRegisterSuccess(email, redirectUrl = '') {
        if (!email) {
            return;
        }

        window.sessionStorage.setItem(this.registerSuccessKey, JSON.stringify({
            email,
            redirectUrl,
            createdAt: Date.now(),
        }));
    }

    runRegisterToLoginTransition(form) {
        form.classList.add('pointer-events-none');

        const transitionState = this.readRegisterSuccessState();
        const loginUrl = new URL(BASE_URL + 'login', window.location.origin);

        if (transitionState?.redirectUrl) {
            loginUrl.searchParams.set('redirect', transitionState.redirectUrl);
        }

        this.swapAuthView({
            panel: 'register',
            exitClass: '-translate-x-[100vw]',
            slideState: 'from_right',
            targetUrl: loginUrl.toString(),
        });
    }

    hydrateLoginTransition() {
        const loginForm = document.getElementById('form-login');

        if (!loginForm) {
            return;
        }

        const slideDirection = this.readAuthSlideState();
        const state = this.readRegisterSuccessState();
        const shouldHydrateFromRight = slideDirection === 'from_right' || Boolean(state);

        if (!shouldHydrateFromRight) {
            if (slideDirection) {
                window.sessionStorage.removeItem(this.authSlideKey);
            }

            return;
        }

        if (state) {
            window.sessionStorage.removeItem(this.registerSuccessKey);

            if (state?.email && Date.now() - Number(state.createdAt || 0) <= 15000) {
                const emailInput = loginForm.querySelector('input[name="email"]');
                if (emailInput) {
                    emailInput.value = state.email;
                }
            }
        }

        window.sessionStorage.removeItem(this.authSlideKey);
        this.hydrateAuthSlide('login', 'translate-x-[100vw]');
    }

    hydrateRegisterTransition() {
        const registerForm = document.getElementById('form-registro');

        if (!registerForm) {
            return;
        }

        const slideDirection = this.readAuthSlideState();
        if (slideDirection !== 'from_left') {
            if (slideDirection) {
                window.sessionStorage.removeItem(this.authSlideKey);
            }

            return;
        }

        window.sessionStorage.removeItem(this.authSlideKey);
        this.hydrateAuthSlide('register', '-translate-x-[100vw]');
    }

    bindNavigationTransitions() {
        this.bindTransitionLinks({
            formId: 'form-login',
            hrefFragment: 'registro',
            slideState: 'from_left',
            exitClass: 'translate-x-[100vw]',
            panel: 'login',
        });

        this.bindTransitionLinks({
            formId: 'form-registro',
            hrefFragment: 'login',
            slideState: 'from_right',
            exitClass: '-translate-x-[100vw]',
            panel: 'register',
        });
    }

    bindTransitionLinks({ formId, hrefFragment, slideState, exitClass, panel }) {
        if (!document.getElementById(formId)) {
            return;
        }

        const links = document.querySelectorAll(`a[href*="${hrefFragment}"]`);

        links.forEach((link) => {
            if (link.dataset.authSwapBound === 'true') {
                return;
            }

            link.dataset.authSwapBound = 'true';
            link.addEventListener('click', (event) => {
                if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
                    return;
                }

                event.preventDefault();
                this.swapAuthView({
                    panel,
                    exitClass,
                    slideState,
                    targetUrl: link.href,
                });
            });
        });
    }

    async swapAuthView({ panel, exitClass, slideState, targetUrl }) {
        const currentShell = this.getAuthShell();

        if (!currentShell || this.isSwapping) {
            return;
        }

        this.isSwapping = true;
        window.sessionStorage.setItem(this.authSlideKey, slideState);
        this.runSlideOutTransition(panel, exitClass);

        try {
            const [html] = await Promise.all([
                this.getPrefetchedHtml(targetUrl),
                this.wait(this.swapDuration),
            ]);

            const parser = new DOMParser();
            const documentFragment = parser.parseFromString(html, 'text/html');
            const nextShell = documentFragment.querySelector(this.authShellSelector) || documentFragment.querySelector('[data-auth-shell]');

            if (!nextShell) {
                throw new Error('Auth shell not found in prefetched HTML');
            }

            currentShell.replaceWith(nextShell);
            document.title = documentFragment.title || document.title;
            window.history.pushState(null, '', targetUrl);
            this.init();
        } catch (error) {
            window.sessionStorage.removeItem(this.authSlideKey);
            this.restoreAuthView(panel);
            Toast.show('No pudimos cargar vista de autenticación', 'error');
            throw error;
        } finally {
            this.isSwapping = false;
        }
    }

    runSlideOutTransition(panelName, exitClass) {
        const shell = this.getAuthShell();
        const panel = document.querySelector(`[data-auth-panel="${panelName}"]`);
        const overlay = document.querySelector(`[data-auth-overlay="${panelName}"]`);

        [shell, panel, overlay].forEach((element) => {
            this.prepareAnimatedElement(element);
        });

        shell?.classList.add('pointer-events-none');
        panel?.classList.add('pointer-events-none');

        window.requestAnimationFrame(() => {
            [shell, panel, overlay].forEach((element) => {
                element?.classList.add(...this.exitTransitionClasses);
            });

            shell?.classList.add(exitClass);
            panel?.classList.add('opacity-0', exitClass);
            overlay?.classList.add('opacity-0', exitClass);
        });
    }

    hydrateAuthSlide(panelName, startClass) {
        const shell = this.getAuthShell();
        const panel = document.querySelector(`[data-auth-panel="${panelName}"]`);
        const overlay = document.querySelector(`[data-auth-overlay="${panelName}"]`);

        [shell, panel, overlay].forEach((element) => {
            this.prepareAnimatedElement(element);
            element?.classList.add(...this.initialStateClasses, startClass);
        });

        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => {
                [shell, panel, overlay].forEach((element) => {
                    element?.classList.add(...this.entryTransitionClasses, 'translate-x-0');
                    element?.classList.remove(startClass, 'opacity-0');
                });
            });
        });
    }

    prepareAnimatedElement(element) {
        if (!element) {
            return;
        }

        this.resetTranslateClasses(element);
        element.classList.remove(...this.entryTransitionClasses, ...this.exitTransitionClasses, 'opacity-0');
        element.classList.add('transform-gpu', 'will-change-transform');
    }

    resetTranslateClasses(element) {
        element?.classList.remove('-translate-x-[100vw]', 'translate-x-[100vw]', 'translate-x-0');
    }

    restoreAuthView(panelName) {
        const shell = this.getAuthShell();
        const panel = document.querySelector(`[data-auth-panel="${panelName}"]`);
        const overlay = document.querySelector(`[data-auth-overlay="${panelName}"]`);

        [shell, panel, overlay].forEach((element) => {
            this.prepareAnimatedElement(element);
            element?.classList.remove('pointer-events-none');
        });
    }

    readAuthSlideState() {
        return window.sessionStorage.getItem(this.authSlideKey);
    }

    getAuthShell() {
        return document.querySelector(this.authShellSelector) || document.querySelector('[data-auth-shell]');
    }

    getCurrentAuthTarget() {
        const loginForm = document.getElementById('form-login');
        const registerForm = document.getElementById('form-registro');
        const redirectUrl = loginForm?.querySelector('input[name="redirect"]')?.value?.trim()
            || registerForm?.querySelector('input[name="redirect"]')?.value?.trim()
            || new URLSearchParams(window.location.search).get('redirect')
            || '';

        if (loginForm) {
            return this.buildAuthUrl('registro', redirectUrl);
        }

        if (registerForm) {
            return this.buildAuthUrl('login', redirectUrl);
        }

        return null;
    }

    buildAuthUrl(path, redirectUrl = '') {
        const url = new URL(BASE_URL + path, window.location.origin);

        if (redirectUrl) {
            url.searchParams.set('redirect', redirectUrl);
        }

        return url.toString();
    }

    prefetchOppositeView() {
        const targetUrl = this.getCurrentAuthTarget();

        if (!targetUrl) {
            return;
        }

        this.prefetchAuthView(targetUrl);
    }

    prefetchAuthView(targetUrl) {
        const cacheKey = this.normalizeUrl(targetUrl);

        if (this.prefetchCache.has(cacheKey)) {
            return Promise.resolve(this.prefetchCache.get(cacheKey));
        }

        if (this.prefetchRequests.has(cacheKey)) {
            return this.prefetchRequests.get(cacheKey);
        }

        const request = window.fetch(cacheKey, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'AuthPrefetch',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Prefetch failed with status ${response.status}`);
                }

                return response.text();
            })
            .then((html) => {
                this.prefetchCache.set(cacheKey, html);
                this.prefetchRequests.delete(cacheKey);

                return html;
            })
            .catch((error) => {
                this.prefetchRequests.delete(cacheKey);
                throw error;
            });

        this.prefetchRequests.set(cacheKey, request);

        return request;
    }

    getPrefetchedHtml(targetUrl) {
        const cacheKey = this.normalizeUrl(targetUrl);

        if (this.prefetchCache.has(cacheKey)) {
            return Promise.resolve(this.prefetchCache.get(cacheKey));
        }

        return this.prefetchAuthView(cacheKey);
    }

    normalizeUrl(targetUrl) {
        return new URL(targetUrl, window.location.origin).toString();
    }

    wait(ms) {
        return new Promise((resolve) => {
            window.setTimeout(resolve, ms);
        });
    }

    readRegisterSuccessState() {
        const rawState = window.sessionStorage.getItem(this.registerSuccessKey);
        if (!rawState) {
            return null;
        }

        try {
            return JSON.parse(rawState);
        } catch (_error) {
            return null;
        }
    }
}

export const authController = new AuthController();
