import { apiJson } from '../lib/http';

export const userService = {
    async fetchUsers() {
        return apiJson('/api/users');
    },

    async createUser(payload) {
        return apiJson('/api/users', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
    },

    async updateUser(id, payload) {
        return apiJson(`/api/users/${id}`, {
            method: 'PATCH',
            body: JSON.stringify(payload),
        });
    },

    async deleteUser(id) {
        return apiJson(`/api/users/${id}`, {
            method: 'DELETE',
        });
    },

    // Read-only catalogue listings backing the site/module access
    // pickers in the create/edit forms — see SiteController/
    // ModuleController on the backend. Not a second catalogue.
    async fetchSites() {
        return apiJson('/api/sites');
    },

    async fetchModules() {
        return apiJson('/api/modules');
    },

    // "Lupa Password" requests from the login page (super_admin only).
    async fetchPasswordRequests() {
        return apiJson('/api/password-requests');
    },

    async actOnPasswordRequest(id, action) {
        return apiJson(`/api/password-requests/${id}/${action}`, { method: 'POST' });
    },
};
