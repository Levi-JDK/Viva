document.addEventListener("DOMContentLoaded", function () {
    // Elementos del DOM para UI
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');
    const toastContainer = document.getElementById("toast-container");

    // Exponer showToast globalmente para que auth.js pueda usarlo
    window.showToast = function (message, type = 'success') {
        if (!toastContainer) return;

        const toast = document.createElement('div');

        // Colores y iconos basados en el tipo
        let bgColor, icon;
        if (type === 'success') {
            bgColor = 'bg-green-500';
            icon = '<i class="fas fa-check-circle"></i>';
        } else if (type === 'error') {
            bgColor = 'bg-red-500';
            icon = '<i class="fas fa-exclamation-circle"></i>';
        } else {
            bgColor = 'bg-blue-500';
            icon = '<i class="fas fa-info-circle"></i>';
        }

        toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] toast-enter`;
        toast.innerHTML = `
            <div class="text-xl">${icon}</div>
            <div class="font-medium text-sm">${message}</div>
        `;

        toastContainer.appendChild(toast);

        // Remover después de 3 segundos
        setTimeout(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');
            toast.addEventListener('animationend', () => {
                toast.remove();
            });
        }, 3000);
    };

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
