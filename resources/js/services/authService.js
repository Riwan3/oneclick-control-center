import { apiJson } from '../lib/http';

export const authService = {
    async fetchCurrentUser() {
        return apiJson('/api/user');
    },

    async login(username, password) {
        return apiJson('/login', {
            method: 'POST',
            body: JSON.stringify({ username, password }),
        });
    },

    async requestPasswordReset(payload) {
        return apiJson('/api/password-requests', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
    },

    async logout() {
        return apiJson('/logout', {
            method: 'POST',
        });
    },
};

export const profileService = {
    updateName(name) {
        return apiJson('/api/profile', { method: 'PATCH', body: JSON.stringify({ name }) });
    },

    updatePassword(current_password, password, password_confirmation) {
        return apiJson('/api/profile/password', {
            method: 'PUT',
            body: JSON.stringify({ current_password, password, password_confirmation }),
        });
    },

    uploadAvatar(file) {
        const body = new FormData();
        body.append('avatar', file);
        return apiJson('/api/profile/avatar', { method: 'POST', body });
    },

    removeAvatar() {
        return apiJson('/api/profile/avatar', { method: 'DELETE' });
    },
};
