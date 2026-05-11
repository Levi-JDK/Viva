import { Toast } from '../ui/Toast.js';

export class VendorRegistrationController {
    constructor() {
        this.currentStep = 1;
    }
    
    init() {
        this.updateSteps();
    }

    async handleSubmit(e) {
        e.preventDefault();

        const validation = this.validateAllSteps();
        if (!validation.valid) {
            if (validation.step !== this.currentStep) {
                this.currentStep = validation.step;
                this.updateSteps();
            }

            Toast.show(validation.errors[0], 'error');
            return;
        }
        
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        if(submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
        }

        try {
            const formData = new FormData(form);
            const { ApiService } = await import('../services/ApiService.js');
            const data = await ApiService.post(window.BASE_URL + 'registro_vendedor', formData);
            
            if (data.exito) {
                Toast.show(data.mensaje || 'Registro exitoso', 'success');
                
                setTimeout(() => {
                    window.location.href = window.BASE_URL + 'mis_productos';
                }, 2000);
            } else {
                Toast.show(data.mensaje || 'Error en el registro', 'error');
            }
        } catch(err) {
            Toast.show(err.message || 'Error de red conectando al servidor', 'error');
        } finally {
            if(submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Confirmar y Enviar <i class="fas fa-check-circle ml-2"></i>';
            }
        }
    }

    validateStep(stepNumber) {
        const step = document.querySelector(`.form-step[data-step="${stepNumber}"]`);
        const errors = [];

        if (!step) {
            return { valid: true, errors };
        }

        const fields = step.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            const value = field.value.trim();
            const label = this.getFieldLabel(field);

            if (field.type === 'checkbox') {
                if (field.required && !field.checked) {
                    errors.push(this.getCheckboxError(field));
                }
                return;
            }

            if (field.tagName === 'SELECT') {
                if (field.required && value === '') {
                    errors.push(`Debes seleccionar una opción para ${label}`);
                }
                return;
            }

            if (field.required && value === '') {
                errors.push(`El campo ${label} es obligatorio`);
                return;
            }

            if (field.name === 'numero_documento' && !/^\d{10}$/.test(value)) {
                errors.push('El número de documento debe tener exactamente 10 dígitos');
                return;
            }

            if (field.name === 'numero_cuenta' && !/^\d+$/.test(value)) {
                errors.push('El número de cuenta solo puede contener números');
            }
        });

        return { valid: errors.length === 0, errors };
    }

    validateAllSteps() {
        for (let stepNumber = 1; stepNumber <= 3; stepNumber++) {
            const validation = this.validateStep(stepNumber);

            if (!validation.valid) {
                return { ...validation, step: stepNumber };
            }
        }

        return { valid: true, errors: [], step: null };
    }

    getFieldLabel(field) {
        const container = field.closest('div') || field.parentElement;
        const label = container?.querySelector('label');
        const rawText = label?.textContent || field.name || 'requerido';

        return rawText.replace('*', '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    getCheckboxError(field) {
        const messages = {
            acepta_terminos: 'Debes aceptar los términos y condiciones',
            acepta_tratamiento_datos: 'Debes autorizar el tratamiento de datos'
        };

        return messages[field.name] || `El campo ${this.getFieldLabel(field)} es obligatorio`;
    }

    nextStep(e) {
        const validation = this.validateStep(this.currentStep);

        if (!validation.valid) {
            Toast.show(validation.errors[0], 'error');
            return;
        }

        if (this.currentStep < 3) {
            this.currentStep++;
            this.updateSteps();
            if (this.currentStep === 3) {
                this.updateSummary();
            }
        }
    }

    prevStep(e) {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateSteps();
        }
    }

    updateSteps() {
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
            if (parseInt(step.dataset.step) === this.currentStep) {
                step.classList.add('active');
            }
        });
        
        document.querySelectorAll('.step-indicator').forEach(indicator => {
            indicator.classList.remove('active');
            if (parseInt(indicator.dataset.step) <= this.currentStep) {
                indicator.classList.add('active');
            }
        });
        
        const progress = (this.currentStep - 1) * 50;
        const progressBar = document.getElementById('progressBar');
        if (progressBar) progressBar.style.width = progress + '%';
    }

    updateSummary() {
        const getVal = (name) => {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return '';
            if (el.tagName === 'SELECT') return el.options[el.selectedIndex]?.text || '';
            return el.value;
        };

        const ids = {
            'tipo_documento': 'summary-tipo-doc',
            'numero_documento': 'summary-num-doc',
            'direccion': 'summary-direccion',
            'departamento': 'summary-departamento',
            'ciudad': 'summary-ciudad',
            'grupo_artesanal': 'summary-grupo',
            'banco': 'summary-banco',
            'tipo_cuenta': 'summary-tipo-cuenta',
            'numero_cuenta': 'summary-num-cuenta'
        };

        for (const [name, id] of Object.entries(ids)) {
            const el = document.getElementById(id);
            if (el) el.textContent = getVal(name);
        }
    }
}

export const vendorRegistrationController = new VendorRegistrationController();
