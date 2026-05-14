import { ApiService } from './ApiService.js';

class AdminValidationService {
    async list({ status = 'pending_review', page = 1, limit = 20 } = {}) {
        const params = new URLSearchParams();
        if (status) params.set('status', status);
        params.set('page', page);
        params.set('limit', limit);

        return ApiService.get(`src/api/admin_validation_list.php?${params.toString()}`);
    }

    async approve(productId, motivo = '') {
        return this.action(productId, 'approve', motivo);
    }

    async reject(productId, motivo = '') {
        return this.action(productId, 'reject', motivo);
    }

    async reprocess(productId) {
        return this.action(productId, 'reprocess');
    }

    async action(productId, action, motivo = '') {
        return ApiService.postJson('src/api/admin_validation_action.php', {
            product_id: Number(productId),
            action,
            motivo,
        });
    }
}

export const adminValidationService = new AdminValidationService();
export { AdminValidationService };
