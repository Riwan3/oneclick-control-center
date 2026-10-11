import { apiJson } from '../lib/http';

// Super-admin site registry + per-site API token (see
// SiteManagementController). The token is returned once, by issueToken.
export const siteManagementService = {
    createSite(payload) {
        return apiJson('/api/sites', { method: 'POST', body: JSON.stringify(payload) });
    },

    updateSite(id, payload) {
        return apiJson(`/api/sites/${id}`, { method: 'PATCH', body: JSON.stringify(payload) });
    },

    deleteSite(id) {
        return apiJson(`/api/sites/${id}`, { method: 'DELETE' });
    },

    issueToken(id) {
        return apiJson(`/api/sites/${id}/token`, { method: 'POST' });
    },

    checkNow(id) {
        return apiJson(`/api/site-connections/${id}/check`, { method: 'POST' });
    },
};
