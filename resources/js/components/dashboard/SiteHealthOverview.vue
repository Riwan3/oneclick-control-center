<script setup>
import UiCard from '../ui/UiCard.vue';
import UiStatus from '../ui/UiStatus.vue';

defineProps({
    sites: {
        type: Array,
        required: true,
        // shape: [{ code, status: 'success' | 'warning' | 'danger' }]
    },
});

const statusLabels = {
    success: 'Online',
    warning: 'Warning',
    danger: 'Offline',
};
</script>

<template>
    <UiCard>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            <div
                v-for="site in sites"
                :key="site.code"
                class="flex items-center justify-between gap-2 rounded-lg border border-border px-3 py-2.5"
            >
                <span class="text-sm font-semibold text-text-primary">{{ site.code }}</span>
                <UiStatus :variant="site.status" :label="statusLabels[site.status]" />
            </div>
        </div>
    </UiCard>
</template>
