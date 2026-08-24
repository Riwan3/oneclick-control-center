<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';
import AppIcon from '../layout/AppIcon.vue';

defineProps({
    activities: {
        type: Array,
        required: true,
        // shape: [{ timestamp, type, title, description, status }]
    },
});

const typeIcons = {
    system: 'monitoring',
    module: 'dashboard',
    version: 'tag',
    activity: 'trend-up',
};
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">Recent Activity</h3>

        <ul class="mt-4 space-y-3">
            <li
                v-for="(item, index) in activities"
                :key="index"
                class="flex items-start gap-3 rounded-lg border border-border px-3 py-2.5"
            >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                    <AppIcon :name="typeIcons[item.type] ?? 'monitoring'" class="h-4 w-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-text-primary">{{ item.title }}</p>
                    <p class="text-caption mt-0.5">{{ item.description }}</p>
                    <p class="text-caption mt-0.5">{{ item.timestamp }}</p>
                </div>
                <UiBadge :variant="item.status">{{ item.type }}</UiBadge>
            </li>
        </ul>
    </UiCard>
</template>
