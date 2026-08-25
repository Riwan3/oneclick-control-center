<script setup>
import UiBadge from '../ui/UiBadge.vue';
import UiStatus from '../ui/UiStatus.vue';
import UiButton from '../ui/UiButton.vue';

const props = defineProps({
    logs: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    meta: {
        type: Object,
        default: () => ({
            current_page: 1,
            last_page: 1,
            total: 0,
            per_page: 25,
        }),
    },
});

const emit = defineEmits(['select-log', 'change-page']);

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const day = String(d.getDate()).padStart(2, '0');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const month = monthNames[d.getMonth()];
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${day} ${month} ${hours}:${minutes}`;
}

function getActionBadgeVariant(action) {
    switch (action) {
        case 'login':
            return 'info';
        case 'logout':
            return 'neutral';
        case 'login_failed':
            return 'danger';
        case 'user_created':
        case 'user_activated':
            return 'success';
        case 'user_updated':
        case 'user_site_access_updated':
        case 'user_module_access_updated':
            return 'info';
        case 'user_deactivated':
            return 'warning';
        case 'user_deleted':
            return 'danger';
        default:
            return 'neutral';
    }
}

function getTargetLabel(log) {
    if (log.entity_type) {
        return log.entity_id ? `${log.entity_type} #${log.entity_id}` : log.entity_type;
    }
    return 'Authentication';
}

function getResultStatus(log) {
    if (log.action === 'login_failed') {
        return { variant: 'danger', label: 'Failed' };
    }
    return { variant: 'success', label: 'Success' };
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="loading" class="py-12 text-center text-body">
            Loading audit records…
        </div>

        <div v-else-if="logs.length === 0" class="py-12 text-center text-body">
            No audit log entries found.
        </div>

        <div v-else class="overflow-x-auto">
            <table class="w-full min-w-[800px] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border text-left">
                        <th class="px-3 py-2 text-label">Timestamp</th>
                        <th class="px-3 py-2 text-label">User</th>
                        <th class="px-3 py-2 text-label">Action</th>
                        <th class="px-3 py-2 text-label">Target</th>
                        <th class="px-3 py-2 text-label">Description</th>
                        <th class="px-3 py-2 text-label">Result</th>
                        <th class="px-3 py-2 text-right text-label">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="log in logs"
                        :key="log.id"
                        class="border-b border-border last:border-0 hover:bg-surface-hover/50 cursor-pointer transition-colors"
                        @click="emit('select-log', log)"
                    >
                        <td class="px-3 py-2.5 font-mono text-xs text-text-secondary whitespace-nowrap">
                            {{ formatDate(log.created_at) }}
                        </td>
                        <td class="px-3 py-2.5 font-medium text-text-primary">
                            <span v-if="log.user">
                                {{ log.user.name }}
                                <span class="text-caption block text-xs">@{{ log.user.username }}</span>
                            </span>
                            <span v-else class="text-text-muted italic">Unknown</span>
                        </td>
                        <td class="px-3 py-2.5">
                            <UiBadge :variant="getActionBadgeVariant(log.action)">
                                {{ log.action }}
                            </UiBadge>
                        </td>
                        <td class="px-3 py-2.5 text-text-secondary">
                            {{ getTargetLabel(log) }}
                        </td>
                        <td class="px-3 py-2.5 text-text-primary max-w-xs truncate" :title="log.description">
                            {{ log.description }}
                        </td>
                        <td class="px-3 py-2.5">
                            <UiStatus
                                :variant="getResultStatus(log).variant"
                                :label="getResultStatus(log).label"
                            />
                        </td>
                        <td class="px-3 py-2.5 text-right">
                            <button
                                type="button"
                                class="text-xs font-medium text-primary hover:underline focus:outline-none"
                                @click.stop="emit('select-log', log)"
                            >
                                Details
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div v-if="meta.total > 0" class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-border">
            <div class="text-caption">
                Showing page {{ meta.current_page }} of {{ meta.last_page }} ({{ meta.total }} total entries)
            </div>

            <div class="flex items-center space-x-2">
                <UiButton
                    variant="neutral"
                    size="sm"
                    :disabled="meta.current_page <= 1 || loading"
                    @click="emit('change-page', meta.current_page - 1)"
                >
                    Previous
                </UiButton>

                <UiButton
                    variant="neutral"
                    size="sm"
                    :disabled="meta.current_page >= meta.last_page || loading"
                    @click="emit('change-page', meta.current_page + 1)"
                >
                    Next
                </UiButton>
            </div>
        </div>
    </div>
</template>
