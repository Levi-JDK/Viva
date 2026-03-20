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
        
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        if(submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...';
        }

        try {
            const formData = new FormData(form);
            const { ApiService } = await import('../services/ApiService.js');
            const data = await ApiService.post(window.BASE_URL + 'api/registro_vendedor', formData);
            
            if (data.success) {
                Toast.show(data.message || 'Registro exitoso', 'success');
                
                setTimeout(() => {
                    window.location.href = window.BASE_URL + 'mis_productos';
                }, 2000);
            } else {
                Toast.show(data.message || 'Error en el registro', 'error');
            }
        } catch(err) {
            console.error(err);
            Toast.show('Error de red conectando al servidor', 'error');
        } finally {
            if(submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Confirmar y Enviar <i class="fas fa-check-circle ml-2"></i>';
            }
        }
    }
    nextStep(e) {
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