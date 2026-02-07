document.addEventListener("DOMContentLoaded", function () {
    // Elementos del DOM para UI
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');
    const toastContainer = document.getElementById("toast-container");

    // Exponer showToast globalmente para que auth.js pueda usarlo
    // NOTA: showToast ahora se maneja globalmente en src/scripts/toast.js


    // Funcionalidad de cambio entre formularios (Desktop)
    if (signUpButton && container) {
        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });
    }

    if (signInButton && container) {
        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    }

    // Funcionalidad para botones móviles
    const signUpMobile = document.getElementById('signUp-mobile');
    const signInMobile = document.getElementById('signIn-mobile');

    if (signUpMobile) {
        signUpMobile.addEventListener('click', function (e) {
            e.preventDefault();
            if (container) {
                container.classList.add("mobile-show-register");
                container.classList.remove("mobile-show-login");
                // Asegurar que el formulario de registro sea visible en móvil
                const signUpContainer = document.querySelector('.sign-up-container');
                const signInContainer = document.querySelector('.sign-in-container');
                if (signUpContainer) {
                    signUpContainer.classList.remove('opacity-0', 'z-1');
                    signUpContainer.classList.add('opacity-100', 'z-10');
                }
                if (signInContainer) {
                    signInContainer.classList.add('opacity-0', 'z-1');
                    signInContainer.classList.remove('opacity-100', 'z-10');
                }
            }
        });
    }

    if (signInMobile) {
        signInMobile.addEventListener('click', function (e) {
            e.preventDefault();
            if (container) {
                container.classList.add("mobile-show-login");
                container.classList.remove("mobile-show-register");
                // Asegurar que el formulario de login sea visible en móvil
                const signUpContainer = document.querySelector('.sign-up-container');
                const signInContainer = document.querySelector('.sign-in-container');
                if (signUpContainer) {
                    signUpContainer.classList.add('opacity-0', 'z-1');
                    signUpContainer.classList.remove('opacity-100', 'z-10');
                }
                if (signInContainer) {
                    signInContainer.classList.remove('opacity-0', 'z-1');
                    signInContainer.classList.add('opacity-100', 'z-10');
                }
            }
        });
    }

    // Inicializar vista móvil
    function initializeMobileView() {
        if (window.innerWidth <= 768 && container) {
            // Por defecto mostrar login
            const signUpContainer = document.querySelector('.sign-up-container');
            const signInContainer = document.querySelector('.sign-in-container');

            if (!container.classList.contains('mobile-show-register')) {
                container.classList.add("mobile-show-login");
                if (signUpContainer) signUpContainer.classList.add('opacity-0', 'z-1');
                if (signInContainer) signInContainer.classList.remove('opacity-0', 'z-1');
            }
        }
    }

    initializeMobileView();
    window.addEventListener('resize', initializeMobileView);
});
