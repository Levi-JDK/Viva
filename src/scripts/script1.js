// Script1.js - User dropdown functionality
// User header HTML is managed by PHP in index.php controller

document.addEventListener("DOMContentLoaded", () => {
    setupUserDropdown();
});

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
