// Talks to the real GET/POST /api/notifications endpoints (see
// routes/web.php + NotificationController). No mock notification data —
// every value here (list, unreadCount) is backend-confirmed, never
// guessed/faked locally.
import { defineStore } from 'pinia';
import { notificationService } from '../services/notificationService';

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        loading: false,
        error: null,
        pagination: null, // { currentPage, lastPage, total, perPage }
        // Not user-facing state — a sequence token so that if filters
        // change quickly, an older fetchNotifications() response arriving
        // after a newer one can't overwrite it.
        _fetchToken: 0,
    }),
    actions: {
        async fetchUnreadCount() {
            const { ok, data } = await notificationService.fetchUnreadCount();
            if (ok) {
                this.unreadCount = data?.count ?? 0;
            }
        },

        // Notification history is only loaded when the notification
        // center is actually opened (topbar panel or /notifications),
        // never eagerly on app boot — only the unread count is.
        async fetchNotifications({ page = 1, perPage = 20, status = 'all', type = 'all' } = {}) {
            const token = ++this._fetchToken;
            this.loading = true;
            this.error = null;

            const { ok, data } = await notificationService.fetchNotifications({ page, perPage, status, type });

            // A newer fetchNotifications() call (e.g. the user changed the
            // filter again before this one finished) has already landed —
            // discard this now-stale response instead of overwriting it.
            if (token !== this._fetchToken) return;

            if (ok) {
                this.notifications = data?.data ?? [];
                this.pagination = {
                    currentPage: data?.meta?.current_page ?? page,
                    lastPage: data?.meta?.last_page ?? 1,
                    total: data?.meta?.total ?? this.notifications.length,
                    perPage: data?.meta?.per_page ?? perPage,
                };
            } else {
                this.error = data?.message ?? 'Unable to load notifications.';
            }

            this.loading = false;
        },

        async markAsRead(id) {
            const { ok, data } = await notificationService.markAsRead(id);

            if (ok) {
                const updated = data?.data ?? {};
                const target = this.notifications.find((n) => n.id === id);
                if (target) target.read_at = updated.read_at ?? target.read_at;
                this.unreadCount = data?.unreadCount ?? this.unreadCount;
            }

            return ok;
        },

        async markAllAsRead() {
            const { ok, data } = await notificationService.markAllAsRead();

            if (ok) {
                const now = new Date().toISOString();
                this.notifications = this.notifications.map((n) => ({ ...n, read_at: n.read_at ?? now }));
                this.unreadCount = data?.unreadCount ?? 0;
            }

            return ok;
        },
    },
});

