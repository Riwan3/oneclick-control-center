import { ref } from 'vue';
import { apiJson } from '../lib/http';

export function useAuditLogs() {
    const logs = ref([]);
    const summary = ref({
        total: 0,
        today: 0,
        successful_logins: 0,
        failed_logins: 0,
    });
    const users = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const meta = ref({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 25,
    });

    const fetchLogs = async (params = {}) => {
        loading.value = true;
        error.value = null;

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

        const { ok, data, status } = await apiJson(url);

        if (ok && data) {
            logs.value = data.data ?? [];
            meta.value = data.meta ?? {};
            if (data.meta?.summary) {
                summary.value = data.meta.summary;
            }
            if (data.meta?.users) {
                users.value = data.meta.users;
            }
        } else {
            logs.value = [];
            error.value =
                status === 403
                    ? 'Access denied. You do not have permission to view audit logs.'
                    : (data?.message ?? 'Failed to fetch audit logs.');
        }

        loading.value = false;
    };

    return {
        logs,
        summary,
        users,
        loading,
        error,
        meta,
        fetchLogs,
    };
}

