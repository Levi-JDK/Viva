document.addEventListener("DOMContentLoaded", () => {
    checkSession();
});

async function checkSession() {
    const userSection = document.getElementById("user-section");
    if (!userSection) return;

    try {
        const response = await fetch(BASE_URL + "src/functions/auth_controller.php", {
            method: "GET",
            credentials: "same-origin" // Para incluir cookies/sesiones
        });

        const data = await response.json();

        if (data.loggedIn) {
            // Crear menú desplegable de usuario con avatar
            userSection.innerHTML = `
                <div class="relative user-menu">
                    <button id="userMenuBtn" class="flex items-center space-x-2 hover:opacity-80 transition-opacity focus:outline-none">
                        <div class="w-10 h-10 bg-gradient-to-br from-naranja-artesanal to-tierra-medio rounded-full flex items-center justify-center text-white font-semibold shadow-md">
                            ${data.nombre.charAt(0).toUpperCase()}
                        </div>
                        <span class="hidden md:block font-medium text-tierra-oscuro">${data.nombre}</span>
                        <i class="fas fa-chevron-down text-sm text-gray-600"></i>
                    </button>
                    
                    <!-- Menú desplegable -->
                    <div id="userDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible transform scale-95 transition-all duration-200 z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-tierra-oscuro">${data.nombre}</p>
                            <p class="text-xs text-gray-500">${data.email || 'usuario@email.com'}</p>
                        </div>
                        <ul class="py-2">
                            <li>
                                <a href="${BASE_URL}dashboard#profile" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-beige-suave transition-colors">
                                    <i class="fas fa-user w-5"></i>
                                    <span class="ml-3">Mi Perfil</span>
                                </a>
                            </li>
                            <li>
                                <a href="${BASE_URL}dashboard#orders" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-beige-suave transition-colors">
                                    <i class="fas fa-shopping-bag w-5"></i>
                                    <span class="ml-3">Mis Pedidos</span>
                                </a>
                            </li>
                            <li>
                                <a href="${BASE_URL}dashboard#favorites" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-beige-suave transition-colors">
                                    <i class="fas fa-heart w-5"></i>
                                    <span class="ml-3">Favoritos</span>
                                </a>
                            </li>
                            <li>
                                <a href="${BASE_URL}dashboard#settings" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-beige-suave transition-colors">
                                    <i class="fas fa-cog w-5"></i>
                                    <span class="ml-3">Configuración</span>
                                </a>
                            </li>
                            <li class="border-t border-gray-100 mt-2 pt-2">
                                <a href="${BASE_URL}src/functions/logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span class="ml-3">Cerrar Sesión</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            `;

            // Agregar funcionalidad del dropdown
            setupUserDropdown();
        } else {
            // Mostrar botón de login con estilos originales
            userSection.innerHTML = `<a href="${BASE_URL}login" class="btn-primary text-white px-6 py-2 rounded-full font-medium hover:shadow-lg">Iniciar Sesión</a>`;
        }
    } catch (error) {
        console.error("Error al verificar sesión:", error);
        userSection.innerHTML = `<a href="${BASE_URL}login" class="btn-primary text-white px-6 py-2 rounded-full font-medium hover:shadow-lg">Iniciar Sesión</a>`;
    }
}

function setupUserDropdown() {
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (!userMenuBtn || !userDropdown) return;

    // Toggle dropdown al hacer clic en el botón
    userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown();
    });

    // Cerrar dropdown al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
            closeDropdown();
        }
    });

    // Cerrar dropdown al presionar ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    function toggleDropdown() {
        const isVisible = !userDropdown.classList.contains('invisible');
        if (isVisible) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }

    function openDropdown() {
        userDropdown.classList.remove('opacity-0', 'invisible', 'scale-95');
        userDropdown.classList.add('opacity-100', 'visible', 'scale-100');
    }

    function closeDropdown() {
        userDropdown.classList.add('opacity-0', 'invisible', 'scale-95');
        userDropdown.classList.remove('opacity-100', 'visible', 'scale-100');
    }
}