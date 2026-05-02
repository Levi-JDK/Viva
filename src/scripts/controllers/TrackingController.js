export class TrackingController {
    constructor() {
        this.card = null;
        this.timeline = null;
        this.latest = null;
        this.modal = null;
        this.modalTimeline = null;
        this.confirmModal = null;
        this.progress = null;
        this.confirmButton = null;
        this.checkpoints = [];
        this.orderId = null;
        this.failureCount = 0;
        this.pollInterval = null;
        this.steps = [
            { key: 'creado', label: 'Creado', aliases: ['pedido creado', 'creado'] },
            { key: 'en-recoleccion', label: 'En Recolección', aliases: ['pedido en recolección', 'pedido en recoleccion', 'en recolección', 'en recoleccion'] },
            { key: 'en-sucursal', label: 'En Sucursal', aliases: ['en sucursal', 'sucursal'] },
            { key: 'en-despacho', label: 'En Despacho', aliases: ['en despacho', 'despacho'] },
            { key: 'en-reparto', label: 'En Reparto', aliases: ['en reparto', 'reparto'] },
            { key: 'entregado', label: 'Entregado', aliases: ['entregado'] },
        ];
    }

    init() {
        this.card = document.getElementById('tracking-card');
        this.latest = document.getElementById('tracking-latest');
        this.modal = document.getElementById('tracking-modal');
        this.modalTimeline = document.getElementById('tracking-modal-timeline');
        this.confirmModal = document.getElementById('tracking-confirm-modal');
        this.progress = document.getElementById('tracking-progress');
        this.confirmButton = document.getElementById('tracking-confirm-button');
        this.timeline = this.modalTimeline;

        if (!this.card || !this.latest || !this.card.dataset.numGuia) {
            return;
        }

        this.orderId = this.card.dataset.orderId;
        this.fetchAndRender();
        this.pollInterval = window.setInterval(() => this.fetchAndRender(false), 60000);
    }

    async fetchAndRender(showLoading = true) {
        if (!this.orderId || !this.latest) {
            return;
        }

        if (showLoading) {
            this.renderLoading();
        }

        try {
            const response = await fetch(this.getCheckpointsUrl(), {
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json();
            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'No se pudo cargar la información de envío.');
            }

            this.failureCount = 0;
            this.checkpoints = this.extractCheckpoints(data);
            this.renderProgressStepper(this.checkpoints);
            this.renderLatest(this.checkpoints);
            this.renderConfirmButton(this.checkpoints);

            if (this.modal && !this.modal.classList.contains('hidden')) {
                this.renderFullHistory(this.checkpoints);
            }
        } catch (error) {
            if (!this.card.dataset.usedTrackingProxy) {
                this.card.dataset.usedTrackingProxy = '1';
                return this.fetchAndRender(showLoading);
            }

            this.failureCount += 1;
            if (this.failureCount >= 3 && this.pollInterval) {
                window.clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
            this.renderError();
        }
    }

    getCheckpointsUrl() {
        if (this.card.dataset.usedTrackingProxy === '1') {
            return this.getProxyCheckpointsUrl();
        }

        const explicitUrl = this.card.dataset.checkpointsUrl;
        if (explicitUrl) {
            return explicitUrl;
        }

        const apiBaseUrl = (window.PUNTOENVIO_API_URL || '').replace(/\/$/, '');
        const trackingNumber = encodeURIComponent(this.card.dataset.numGuia);

        if (!apiBaseUrl) {
            this.card.dataset.usedTrackingProxy = '1';
            return this.getProxyCheckpointsUrl();
        }

        return `${apiBaseUrl}/envios/${trackingNumber}/checkpoints`;
    }

    getProxyCheckpointsUrl() {
        return `${window.location.pathname}?ajax=tracking&id=${encodeURIComponent(this.orderId)}`;
    }

    extractCheckpoints(data) {
        if (Array.isArray(data)) {
            return data;
        }

        if (Array.isArray(data?.checkpoints)) {
            return data.checkpoints;
        }

        if (Array.isArray(data?.data)) {
            return data.data;
        }

        if (Array.isArray(data?.data?.checkpoints)) {
            return data.data.checkpoints;
        }

        return [];
    }

    retry() {
        this.failureCount = 0;
        this.fetchAndRender();

        if (!this.pollInterval) {
            this.pollInterval = window.setInterval(() => this.fetchAndRender(false), 60000);
        }
    }

    async copyTrackingNumber(btn) {
        const trackingNumber = btn?.dataset?.trackingNumber || this.card?.dataset?.numGuia;
        if (!trackingNumber) {
            return;
        }

        try {
            await navigator.clipboard.writeText(trackingNumber);
            if (typeof window.showToast === 'function') {
                window.showToast('Guía copiada al portapapeles.', 'success');
            }
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast('No se pudo copiar la guía.', 'error');
            }
        }
    }

    openModal() {
        if (!this.modal) {
            return;
        }

        this.renderFullHistory(this.checkpoints);
        this.modal.classList.remove('hidden');
        this.modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    closeModal() {
        if (!this.modal) {
            return;
        }

        this.modal.classList.add('hidden');
        this.modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    openConfirmModal() {
        if (!this.confirmModal || !this.orderId || this.isDelivered(this.checkpoints)) {
            return;
        }

        this.confirmModal.classList.remove('hidden');
        this.confirmModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    closeConfirmModal() {
        if (!this.confirmModal) {
            return;
        }

        this.confirmModal.classList.add('hidden');
        this.confirmModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    renderLoading() {
        this.latest.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <i class="fas fa-spinner fa-spin text-tierra-medio"></i>
                <span>Cargando estado...</span>
            </div>
        `;

        this.renderProgressStepper([]);
    }

    renderError() {
        this.latest.innerHTML = `
            <div class="bg-red-50 border border-red-100 text-red-700 rounded-lg p-4 text-sm">
                <p class="font-medium mb-3">No se pudo cargar la información de envío.</p>
                <button type="button" data-action="tracking-retry" class="inline-flex items-center px-3 py-2 bg-white border border-red-200 rounded-lg font-bold hover:bg-red-100 transition-colors">
                    <i class="fas fa-redo mr-2"></i> Reintentar
                </button>
            </div>
        `;
    }

    renderLatest(checkpoints) {
        const sorted = this.sortCheckpoints(checkpoints);

        if (sorted.length === 0) {
            this.latest.innerHTML = '<p class="text-sm text-gray-500">Aún no hay movimientos para esta guía.</p>';
            return;
        }

        const checkpoint = sorted[0];
        const statusText = this.formatStatus(checkpoint.observations || checkpoint.estado || checkpoint.status || 'Sin estado');
        const dateValue = checkpoint.fecha || checkpoint.scanned_at || '';
        const fecha = this.formatDate(dateValue);
        const sucursal = this.getSucursalName(checkpoint.id_sucursal || checkpoint.ubicacion || checkpoint.location || '');
        const color = this.getStatusColor(checkpoint.observations || statusText);

        this.latest.innerHTML = `
            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-700">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ${color.badge}">
                    <span class="w-2 h-2 rounded-full ${color.dot}"></span>
                    ${this.escapeHtml(statusText)}
                </span>
                ${sucursal ? `<span class="text-gray-400">—</span><span>${this.escapeHtml(sucursal)}</span>` : ''}
                ${fecha ? `<span class="text-gray-400">—</span><span class="text-gray-500">${this.escapeHtml(fecha)}</span>` : ''}
            </div>
        `;

        if (this.confirmButton) {
            this.confirmButton.classList.toggle('hidden', !this.normalizeStatus(statusText).includes('reparto'));
        }
    }

    renderProgressStepper(checkpoints) {
        if (!this.progress) {
            return;
        }

        const sorted = this.sortCheckpoints(checkpoints);
        const latest = sorted[0] || null;
        const currentIndex = latest ? this.getStepIndex(latest.observations || latest.estado || latest.status || '') : -1;

        this.progress.innerHTML = `
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3" aria-label="Progreso del envío">
                ${this.steps.map((step, index) => this.renderProgressStep(step, index, currentIndex)).join('')}
            </div>
        `;
    }

    renderProgressStep(step, index, currentIndex) {
        const isCompleted = currentIndex >= index;
        const isCurrent = currentIndex === index;
        const circleClass = isCompleted ? 'bg-naranja-artesanal text-white border-naranja-artesanal' : 'bg-gray-100 text-gray-400 border-gray-200';
        const labelClass = isCompleted ? 'text-tierra-oscuro' : 'text-gray-400';
        const icon = isCompleted ? 'fa-check' : 'fa-circle';

        return `
            <div class="flex items-center gap-2 ${isCurrent ? 'font-black' : 'font-bold'}">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full border-2 ${circleClass}">
                    <i class="fas ${icon} text-[0.65rem]"></i>
                </span>
                <span class="text-xs leading-tight uppercase tracking-wide ${labelClass}">${this.escapeHtml(step.label)}</span>
            </div>
        `;
    }

    renderConfirmButton(checkpoints) {
        if (!this.confirmButton) {
            return;
        }

        const sorted = this.sortCheckpoints(checkpoints);
        const latest = sorted[0] || null;
        const statusText = latest ? this.formatStatus(latest.observations || latest.estado || latest.status || '') : '';
        const shouldShow = this.normalizeStatus(statusText).includes('reparto') && !this.isDelivered(checkpoints);

        this.confirmButton.classList.toggle('hidden', !shouldShow);
    }

    confirmDelivery() {
        this.openConfirmModal();
    }

    async submitConfirm(btn) {
        if (!this.orderId || !btn || this.isDelivered(this.checkpoints)) {
            return;
        }

        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Confirmando...';

        try {
            const response = await fetch(`${window.location.pathname}?ajax=confirm_delivery&id=${encodeURIComponent(this.orderId)}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();
            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'No se pudo confirmar la recepción del paquete.');
            }

            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Recepción confirmada.', 'success');
            }

            this.confirmButton.classList.add('hidden');
            this.closeConfirmModal();
            await this.fetchAndRender(false);
        } catch (error) {
            if (typeof window.showToast === 'function') {
                window.showToast(error.message || 'No se pudo confirmar la recepción del paquete.', 'error');
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    }

    renderFullHistory(checkpoints) {
        if (!this.modalTimeline) {
            return;
        }

        const sorted = this.sortCheckpoints(checkpoints);

        if (sorted.length === 0) {
            this.modalTimeline.innerHTML = '<p class="text-sm text-gray-500">Aún no hay movimientos para esta guía.</p>';
            return;
        }

        this.modalTimeline.innerHTML = `
            <div class="space-y-5">
                ${sorted.map((checkpoint) => this.renderCheckpoint(checkpoint)).join('')}
            </div>
        `;
    }

    renderTimeline(checkpoints) {
        this.renderFullHistory(checkpoints);
    }

    renderCheckpoint(checkpoint) {
        const rawStatus = checkpoint.observations || checkpoint.estado || checkpoint.status || 'Sin estado';
        const statusText = this.formatStatus(rawStatus);
        const dateValue = checkpoint.fecha || checkpoint.scanned_at || '';
        const fecha = this.formatDate(dateValue);
        const sucursal = this.getSucursalName(checkpoint.id_sucursal || checkpoint.ubicacion || checkpoint.location || '');
        const color = this.getStatusColor(rawStatus);

        return `
            <div class="relative pl-8 pb-1 border-l-2 border-gray-200 last:border-l-transparent">
                <span class="absolute -left-[9px] top-0 w-4 h-4 rounded-full ring-4 ring-white ${color.dot}"></span>
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold ${color.badge}">${this.escapeHtml(statusText)}</span>
                        ${fecha ? `<span class="text-xs text-gray-400">${this.escapeHtml(fecha)}</span>` : ''}
                    </div>
                    ${sucursal ? `<p class="text-sm text-gray-700 font-medium"><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>${this.escapeHtml(sucursal)}</p>` : ''}
                </div>
            </div>
        `;
    }

    sortCheckpoints(checkpoints) {
        return [...(checkpoints || [])].sort((a, b) => new Date(b.fecha || b.scanned_at || 0) - new Date(a.fecha || a.scanned_at || 0));
    }

    getSucursalName(id) {
        const sucursales = {
            'SUC-ORIGEN': 'Sucursal Medellin',
        };

        const value = String(id || '').trim();

        if (value === 'SISTEMA' || value === 'SUC-bb535de5a016' || value === 'Sucursal Central Viva') {
            return '';
        }

        return sucursales[value] || value;
    }

    formatStatus(observations) {
        const status = String(observations || '').trim();
        const statuses = {
            'Pedido creado': 'Creado',
            'Pedido En Recolección': 'En Recolección',
            'En Sucursal': 'En Sucursal',
            'En Despacho': 'En Despacho',
            'En Reparto': 'En Reparto',
            'Entregado': 'Entregado',
        };

        return statuses[status] || status;
    }

    getStepIndex(status) {
        const normalized = this.normalizeStatus(status);

        return this.steps.findIndex((step) => step.aliases.some((alias) => normalized.includes(alias)));
    }

    isDelivered(checkpoints) {
        return (checkpoints || []).some((checkpoint) => this.getStepIndex(checkpoint.observations || checkpoint.estado || checkpoint.status || '') === this.steps.length - 1);
    }

    normalizeStatus(status) {
        return String(status || '').trim().toLowerCase();
    }

    formatDate(value) {
        if (!value) {
            return '';
        }

        return new Date(value).toLocaleString('es-CO', {
            day: 'numeric',
            month: '2-digit',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        });
    }

    getStatusColor(status) {
        const normalized = String(status || '').toLowerCase();

        if (normalized.includes('creado')) {
            return { badge: 'bg-gray-100 text-gray-700', dot: 'bg-gray-400' };
        }
        if (normalized.includes('entregado')) {
            return { badge: 'bg-green-100 text-green-700', dot: 'bg-green-500' };
        }
        if (normalized.includes('reparto')) {
            return { badge: 'bg-purple-100 text-purple-700', dot: 'bg-purple-500' };
        }
        if (normalized.includes('recolección') || normalized.includes('recoleccion') || normalized.includes('despacho') || normalized.includes('tránsito') || normalized.includes('transito')) {
            return { badge: 'bg-blue-100 text-blue-700', dot: 'bg-blue-500' };
        }
        if (normalized.includes('sucursal') || normalized.includes('bodega')) {
            return { badge: 'bg-orange-100 text-orange-700', dot: 'bg-orange-500' };
        }
        if (normalized.includes('devuelto') || normalized.includes('fallido')) {
            return { badge: 'bg-red-100 text-red-700', dot: 'bg-red-500' };
        }

        return { badge: 'bg-gray-100 text-gray-700', dot: 'bg-gray-400' };
    }

    escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value);
        return div.innerHTML;
    }
}

export const trackingController = new TrackingController();
