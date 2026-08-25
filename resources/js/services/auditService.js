import { apiJson } from '../lib/http';

export const auditService = {
    async fetchLogs(params = {}) {
        const query = new URLSearchParams();
        if (params.page) query.append('page', params.page);
        if (params.per_page) query.append('per_page', params.per_page);
        if (params.action && params.action !== 'all') query.append('action', params.action);
        if (params.user_id && params.user_id !== 'all') query.append('user_id', params.user_id);
        if (params.date && params.date !== 'all') query.append('date', params.date);
        if (params.search && String(params.search).trim() !== '') {
            query.append('search', String(params.search).trim());
        }

        const queryString = query.toString();
        const url = `/api/audit-logs${queryString ? `?${queryString}` : ''}`;
        return apiJson(url);
    },
};
