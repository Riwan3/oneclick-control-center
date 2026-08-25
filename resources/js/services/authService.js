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

    async logout() {
        return apiJson('/logout', {
            method: 'POST',
        });
    },
};
