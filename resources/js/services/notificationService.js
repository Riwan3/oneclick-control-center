import { apiJson } from '../lib/http';

export const notificationService = {
    async fetchNotifications({ page = 1, perPage = 20, status = 'all', type = 'all' } = {}) {
        const params = new URLSearchParams({ page, per_page: perPage, status, type });
        return apiJson(`/api/notifications?${params}`);
    },

    async fetchUnreadCount() {
        return apiJson('/api/notifications/unread-count');
    },

    async markAsRead(id) {
        return apiJson(`/api/notifications/${id}/read`, { method: 'POST' });
    },

    async markAllAsRead() {
        return apiJson('/api/notifications/read-all', { method: 'POST' });
    },
};
