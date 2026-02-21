/**
 * Registro de Vendedor - Navegación Multi-paso
 * Maneja la lógica de navegación entre pasos del formulario
 */

let currentStep = 1;
const totalSteps = 3;

function showSummary() {
    // Helper para obtener texto de select
    const getText = (name) => {
        const select = document.querySelector(`select[name="${name}"]`);
        if (select && select.selectedIndex !== -1) {
            return select.options[select.selectedIndex].text;
        }
        return '';
    };

    // Helper para obtener valor de input
    const getValue = (name) => document.querySelector(`input[name="${name}"]`).value;

    // Poblar datos personales
    document.getElementById('summary-tipo-doc').textContent = getText('tipo_documento');
    document.getElementById('summary-num-doc').textContent = getValue('numero_documento');
    document.getElementById('summary-direccion').textContent = getValue('direccion');
    document.getElementById('summary-departamento').textContent = getText('departamento');
    const ciudadText = getText('ciudad');
    document.getElementById('summary-ciudad').textContent = ciudadText === 'Seleccione un departamento...' ? '' : ciudadText;
    document.getElementById('summary-grupo').textContent = getText('grupo_artesanal');

    // Poblar datos bancarios
    document.getElementById('summary-banco').textContent = getText('banco');
    document.getElementById('summary-tipo-cuenta').textContent = getText('tipo_cuenta');
    document.getElementById('summary-num-cuenta').textContent = getValue('numero_cuenta');
}

/**
 * Actualiza los indicadores visuales de progreso y pasos del formulario
 */
function updateStepIndicators() {
    // Actualizar barra de progreso
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    document.getElementById('progressBar').style.width = progress + '%';

    // Actualizar indicadores de pasos
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const step = index + 1;
        indicator.classList.remove('active', 'completed');

        if (step < currentStep) {
            indicator.classList.add('completed');
        } else if (step === currentStep) {
            indicator.classList.add('active');
        }
    });

    // Actualizar pasos del formulario
    document.querySelectorAll('.form-step').forEach((step, index) => {
        step.classList.remove('active');
        if (index + 1 === currentStep) {
            step.classList.add('active');
        }
    });

    // Scroll suave al inicio del contenedor
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Avanza al siguiente paso del formulario
 * Valida los campos requeridos antes de avanzar
 */
function nextStep() {
    const currentFormStep = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const inputs = currentFormStep.querySelectorAll('input[required], select[required]');

    // Validar campos del paso actual
    let isValid = true;
    inputs.forEach(input => {
        if (!input.checkValidity()) {
            input.reportValidity();
            isValid = false;
            return false;
        }
    });

    // Validación específica para dirección en el paso 1
    if (isValid && currentStep === 1) {
        const addressInput = currentFormStep.querySelector('input[name="direccion"]');
        if (addressInput) {
            const forbiddenChars = /['"*=]/;
            if (forbiddenChars.test(addressInput.value)) {
                if (window.showToast) {
                    showToast('La dirección no puede contener los caracteres: \' " * =', 'error');
                } else {
                    alert('La dirección no puede contener los caracteres: \' " * =');
                }
                addressInput.classList.add('border-red-500'); // Opcional: resaltar error
                addressInput.focus();
                isValid = false;
            } else {
                addressInput.classList.remove('border-red-500');
            }
        }
    }

    // Validación específica para número de cuenta en el paso 2
    if (isValid && currentStep === 2) {
        const accountInput = currentFormStep.querySelector('input[name="numero_cuenta"]');
        if (accountInput && accountInput.value.length > 12) {
            if (window.showToast) {
                showToast('El número de cuenta no puede tener más de 12 dígitos', 'error');
            } else {
                alert('El número de cuenta no puede tener más de 12 dígitos');
            }
            accountInput.classList.add('border-red-500');
            accountInput.focus();
            isValid = false;
        } else if (accountInput) {
            accountInput.classList.remove('border-red-500');
        }
    }

    if (isValid && currentStep < totalSteps) {
        currentStep++;
        if (currentStep === 3) showSummary();
        updateStepIndicators();
    }
}

/**
 * Retrocede al paso anterior del formulario
 */
function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepIndicators();
    }
}

/* Maneja el envío del formulario*/
async function handleFormSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);

    // Mostrar estado de carga en el botón
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
    submitBtn.disabled = true;

    // URL del endpoint API que procesará el registro
    // Apunta a src/api/post_registro_vendedor.php
    const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'src/api/post_registro_vendedor.php';

    try {
        // Enviar datos usando fetch (AJAX) - Método asíncrono moderno
        const response = await fetch(url, {
            method: 'POST',
            body: formData // Contiene todos los campos del formulario automáticamente
        });

        const data = await response.json();

        if (data.success) {
            // Éxito: Mostrar mensaje y redirigir
            // alert('Registro exitoso: ' + data.message); // Opcional, o usar toast
            if (window.showToast) {
                showToast(data.message, 'success');
                setTimeout(() => {
                    window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'mis_productos';
                }, 1500);
            } else {
                alert('Registro exitoso: ' + data.message);
                window.location.href = (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + 'mis_productos';
            }
        } else {
            // Error: Mostrar mensaje y datos debug
            console.error('Debug Params:', data.debug_params);

            if (window.showToast) {
                showToast('Error: ' + data.message, 'error');
            } else {
                alert('Error: ' + data.message);
            }

            const submitBtn = event.target.querySelector('button[type="submit"]');
            submitBtn.innerHTML = 'Finalizar <i class="fas fa-check ml-2"></i>';
            submitBtn.disabled = false;
        }

    } catch (error) {
        console.error('Error:', error);

        if (window.showToast) {
            showToast('Ocurrió un error de conexión.', 'error');
        } else {
            alert('Ocurrió un error de conexión.');
        }

        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function () {
    // Configurar el formulario
    const form = document.getElementById('vendorRegistrationForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Inicializar indicadores de paso
    updateStepIndicators();
});
