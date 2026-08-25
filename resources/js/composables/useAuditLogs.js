import { ref } from 'vue';
import { auditService } from '../services/auditService';

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

        const { ok, data, status } = await auditService.fetchLogs(params);

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

