<script setup>
import UiCard from '../ui/UiCard.vue';
import UiButton from '../ui/UiButton.vue';
import UiBadge from '../ui/UiBadge.vue';
import UiStatus from '../ui/UiStatus.vue';

const props = defineProps({
    log: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleString();
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
            return 'success';
        default:
            return 'neutral';
    }
}
</script>

<template>
    <div
        v-if="log"
        class="fixed inset-0 z-50 flex items-center justify-center bg-text-primary/40 p-4 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <UiCard class="w-full max-w-2xl max-h-[90vh] overflow-y-auto space-y-6">
            <div class="flex items-center justify-between border-b border-border pb-4">
                <div>
                    <h3 class="text-card-title">Audit Record Details</h3>
                    <p class="text-caption mt-1">Event ID #{{ log.id }}</p>
                </div>
                <UiButton variant="neutral" size="sm" @click="emit('close')">
                    Close
                </UiButton>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-label block">Timestamp</span>
                    <span class="font-mono text-text-primary">{{ formatDate(log.created_at) }}</span>
                </div>

                <div>
                    <span class="text-label block">Action</span>
                    <UiBadge :variant="getActionBadgeVariant(log.action)">
                        {{ log.action }}
                    </UiBadge>
                </div>

                <div>
                    <span class="text-label block">Performed By (User)</span>
                    <div v-if="log.user" class="text-text-primary font-medium">
                        {{ log.user.name }} (@{{ log.user.username }})
                    </div>
                    <div v-else class="text-text-muted italic">
                        System / Unauthenticated
                    </div>
                </div>

                <div>
                    <span class="text-label block">Status</span>
                    <UiStatus
                        :variant="log.action === 'login_failed' ? 'danger' : 'success'"
                        :label="log.action === 'login_failed' ? 'Failed' : 'Success'"
                    />
                </div>

                <div class="sm:col-span-2">
                    <span class="text-label block">Target / Entity</span>
                    <span class="text-text-primary">
                        {{ log.entity_type ? `${log.entity_type}${log.entity_id ? ' (ID: ' + log.entity_id + ')' : ''}` : 'Authentication System' }}
                    </span>
                </div>

                <div class="sm:col-span-2">
                    <span class="text-label block">Description</span>
                    <p class="text-text-primary bg-background p-3 rounded-md border border-border">
                        {{ log.description }}
                    </p>
                </div>

                <div class="sm:col-span-2" v-if="log.metadata && Object.keys(log.metadata).length > 0">
                    <span class="text-label block mb-1">Metadata</span>
                    <pre class="bg-background p-3 rounded-md border border-border text-xs font-mono text-text-primary overflow-x-auto">{{ JSON.stringify(log.metadata, null, 2) }}</pre>
                </div>

                <div>
                    <span class="text-label block">IP Address</span>
                    <span class="font-mono text-text-secondary">{{ log.ip_address || 'N/A' }}</span>
                </div>

                <div class="sm:col-span-2" v-if="log.user_agent">
                    <span class="text-label block">User Agent</span>
                    <span class="font-mono text-xs text-text-muted break-all">{{ log.user_agent }}</span>
                </div>
            </div>

            <div class="pt-4 border-t border-border flex justify-end">
                <UiButton variant="primary" @click="emit('close')">
                    Close Details
                </UiButton>
            </div>
        </UiCard>
    </div>
</template>
