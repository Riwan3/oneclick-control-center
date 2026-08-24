<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';
import UiStatus from '../ui/UiStatus.vue';

defineProps({
    module: {
        type: Object,
        required: true,
        // shape: { key, label, description, activeSites, inactiveSites, totalSites, adoption }
    },
    highlighted: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <UiCard :class="highlighted ? 'ring-2 ring-primary' : ''">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-card-title">{{ module.label }}</h3>
                <p class="text-caption mt-1">{{ module.description }}</p>
            </div>
            <UiBadge variant="info">{{ module.adoption }}%</UiBadge>
        </div>

        <p class="text-kpi-value mt-4">
            {{ module.activeSites }}
            <span class="text-base font-medium text-text-secondary">/ {{ module.totalSites }} sites</span>
        </p>

        <div class="mt-4 flex items-center gap-4 border-t border-border pt-3">
            <UiStatus variant="success" :label="`Active ${module.activeSites}`" />
            <UiStatus variant="neutral" :label="`Not Installed ${module.inactiveSites}`" />
        </div>
    </UiCard>
</template>
