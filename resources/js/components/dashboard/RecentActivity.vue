<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';
import AppIcon from '../layout/AppIcon.vue';

defineProps({
    activities: {
        type: Array,
        required: true,
        // shape: [{ id, timestamp, site, message, type }]
    },
});

const typeMeta = {
    module: { icon: 'dashboard', variant: 'info' },
    version: { icon: 'tag', variant: 'neutral' },
    activity: { icon: 'trend-up', variant: 'success' },
    system: { icon: 'monitoring', variant: 'success' },
};
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">Recent System Activity</h3>

        <ul v-if="activities.length" class="mt-4 space-y-3">
            <li
                v-for="item in activities"
                :key="item.id"
                class="flex items-start gap-3 rounded-lg border border-border px-3 py-2.5"
            >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                    <AppIcon :name="typeMeta[item.type]?.icon ?? 'monitoring'" class="h-4 w-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-text-primary">{{ item.message }}</p>
                    <p class="text-caption mt-0.5">{{ item.site }} · {{ item.timestamp }}</p>
                </div>
                <UiBadge :variant="typeMeta[item.type]?.variant ?? 'neutral'">{{ item.type }}</UiBadge>
            </li>
        </ul>
        <p v-else class="mt-4 py-6 text-center text-body">No recent activity available.</p>
    </UiCard>
</template>
