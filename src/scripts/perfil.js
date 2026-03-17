let isTransitioning = false; // Flag to prevent rapid clicks

function showSection(sectionId) {
    // Prevent multiple transitions at once
    if (isTransitioning) return;

    const currentSection = document.querySelector('.content-section.active');
    const targetSection = document.getElementById(sectionId);

    // Si la sección ya es la activa, no hacer nada
    if (currentSection && currentSection.id === sectionId) return;

    // Actualizar menú activo
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active-item');
        // Removemos las clases antiguas por si acaso
        item.classList.remove('bg-beige-suave', 'border-l-4', 'border-naranja-artesanal');

        if (item.getAttribute('onclick')?.includes(sectionId)) {
            item.classList.add('active-item');
        }
    });

    // Animación de salida para la sección actual
    if (currentSection) {
        isTransitioning = true; // Set flag

        currentSection.classList.add('closing');
        currentSection.classList.remove('active');

        // Esperar a que termine la transición (300ms = 0.3s)
        setTimeout(() => {
            currentSection.classList.remove('closing');
            currentSection.style.display = 'none'; // Asegurar que se oculte

            if (targetSection) {
                targetSection.style.display = 'block';
                // Pequeño delay para permitir que el navegador renderice el display:block antes de la opacidad
                requestAnimationFrame(() => {
                    targetSection.classList.add('active');
                    isTransitioning = false; // Clear flag
                });
            } else {
                isTransitioning = false; // Clear flag even if no target
            }
        }, 300);
    } else {
        // Si no hay sección activa (ej: carga inicial), mostrar directamente
        if (targetSection) {
            targetSection.style.display = 'block';
            requestAnimationFrame(() => {
                targetSection.classList.add('active');
            });
        }
    }
}

function logout() {
    if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
        window.location.href = BASE_URL + 'logout';
    }
}

// Profile Edit Functions
let originalFormData = {};

function toggleEdit() {
    const inputs = document.querySelectorAll('.profile-input');
    const btnEditar = document.getElementById('btn-editar');
    const saveCancelButtons = document.getElementById('save-cancel-buttons');

    // Save original data
    originalFormData = {
        nombre: document.getElementById('input-nombre').value,
        apellido: document.getElementById('input-apellido').value,
        email: document.getElementById('input-email').value
    };

    // Enable inputs
    inputs.forEach(input => {
        // No habilitar email si es inmutable
        if (input.id === 'input-email') return;

        input.disabled = false;
        input.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
        input.classList.add('bg-white', 'text-gray-800');
    });

    // Toggle buttons
    btnEditar.classList.add('hidden');
    saveCancelButtons.classList.remove('hidden');
    saveCancelButtons.classList.add('flex');
}

async function saveProfile() {
    const nombre = document.getElementById('input-nombre').value;
    const apellido = document.getElementById('input-apellido').value;
    const btnGuardar = document.querySelector('#save-cancel-buttons button:first-child');
    const originalText = btnGuardar.innerHTML;

    // Validar nombre (# * - ' ")
    if (/[#*\-'"]/.test(nombre)) {
        showToast("El nombre no puede contener caracteres especiales (# * - ' \")", 'error');
        return;
    }

    // Validar apellido (' ")
    if (/['"]/.test(apellido)) {
        showToast("El apellido no puede contener comillas (' \")", 'error');
        return;
    }

    // Mostrar estado de carga
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';

    const formData = new FormData();
    formData.append('accion', 'update_profile');
    formData.append('nombre', nombre);
    formData.append('apellido', apellido);

    try {
        const response = await fetch(BASE_URL + 'perfil', {
            method: 'POST',
            body: formData
        });

        // Verificar si la respuesta es JSON válido
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Respuesta no válida del servidor");
        }

        const data = await response.json();

        if (data.clase === 'mensaje-exito') {
            showToast(data.mensaje, 'success');

            // Actualizar UI
            document.querySelector('h3.text-xl').textContent = `${nombre} ${apellido}`;
            document.getElementById('avatar-image').alt = `${nombre} ${apellido}`;

            // Actualizar datos originales
            originalFormData.nombre = nombre;
            originalFormData.apellido = apellido; // Guardar apellido también

            disableEditMode();
        } else {
            showToast('Error: ' + data.mensaje, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al conectar con el servidor', 'error');
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = originalText;
    }
}

function cancelEdit() {
    // Restore original data
    document.getElementById('input-nombre').value = originalFormData.nombre;
    document.getElementById('input-apellido').value = originalFormData.apellido;
    // No need to restore email as it's disabled

    // Disable editing mode
    disableEditMode();
}

function disableEditMode() {
    const inputs = document.querySelectorAll('.profile-input');
    const btnEditar = document.getElementById('btn-editar');
    const saveCancelButtons = document.getElementById('save-cancel-buttons');

    // Disable all inputs
    inputs.forEach(input => {
        input.disabled = true;
        input.classList.remove('bg-white', 'text-gray-800');
        input.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
    });

    // Toggle buttons
    btnEditar.classList.remove('hidden');
    saveCancelButtons.classList.remove('flex');
    saveCancelButtons.classList.add('hidden');
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function () {
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('hidden');
    });

    // Check for hash in URL on page load and navigate to section
    const hash = window.location.hash.substring(1); // Remove the '#'
    if (hash) {
        // Valid sections: profile, orders, favorites, settings
        const validSections = ['profile', 'orders', 'favorites', 'settings'];
        if (validSections.includes(hash)) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // Remove active class from all menu items
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active-item');
                // Removemos las clases antiguas por si acaso
                item.classList.remove('bg-beige-suave', 'border-l-4', 'border-naranja-artesanal');
            });

            // Show the section from hash
            const targetSection = document.getElementById(hash);
            if (targetSection) {
                targetSection.classList.add('active');

                // Find and activate the corresponding menu item
                const menuItems = document.querySelectorAll('.menu-item');
                menuItems.forEach(item => {
                    if (item.getAttribute('onclick')?.includes(hash)) {
                        item.classList.add('active-item');
                    }
                });
            }
        }
    }

    // Handle automatic upload when a profile picture is selected
    const avatarContainer = document.getElementById('avatar-container');
    const profileInput = document.getElementById('profile-image-input');
    const profileForm = document.getElementById('profile-upload-form');

    if (avatarContainer && profileInput) {
        avatarContainer.addEventListener('click', () => {
            profileInput.click();
        });

        profileInput.addEventListener('change', () => {
            if (profileInput.files && profileInput.files[0]) {
                const file = profileInput.files[0];
                const allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                // Get extension safely
                const fileExtension = file.name.split('.').pop().toLowerCase();

                if (!allowedExtensions.includes(fileExtension)) {
                    showToast('Formato de archivo no admitido. Usa JPG, PNG o WEBP.', 'error');
                    profileInput.value = ''; // Clear input to prevent submission
                    return;
                }

                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    showToast('La imagen es demasiado pesada. El tamaño máximo es 5MB.', 'error');
                    profileInput.value = ''; // Clear input to prevent submission
                    return;
                }

                // Submit form automatically if valid
                profileForm.submit();
            }
        });
    }

    // Check for URL parameters (success or error messages from server)
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('success')) {
        const successType = urlParams.get('success');
        if (successType === 'photo_updated') {
            showToast('Foto de perfil actualizada correctamente.', 'success');
        }
        // Clean URL but keep hash if present
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }

    if (urlParams.has('error')) {
        const errorMsg = urlParams.get('error');
        // Decode + to space if needed (php's urlencode converts spaces to +)
        const decodedMsg = decodeURIComponent(errorMsg.replace(/\+/g, ' '));
        showToast(decodedMsg, 'error');

        // Clean URL
        const newUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
    }
});