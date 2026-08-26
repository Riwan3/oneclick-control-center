<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    baselineVersion: {
        type: String,
        required: true,
    },
    latestVersion: {
        type: String,
        default: null,
    },
    versionDistribution: {
        type: Array,
        default: () => [],
        // shape: [{ version, count }]
    },
});
</script>

<template>
    <UiCard>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <p class="text-label">Baseline Version</p>
                <p class="text-kpi-value mt-1">{{ baselineVersion }}</p>
            </div>
            <div>
                <p class="text-label">Latest Observed Version</p>
                <p class="text-kpi-value mt-1">{{ latestVersion ?? '—' }}</p>
            </div>
        </div>

        <div class="mt-6">
            <p class="text-label mb-2">Site Version Distribution</p>
            <div class="space-y-2">
                <div
                    v-for="entry in versionDistribution"
                    :key="entry.version"
                    class="flex items-center justify-between text-sm"
                >
                    <span class="text-text-secondary">{{ entry.version }}</span>
                    <span class="text-text-muted">{{ entry.count }} site{{ entry.count === 1 ? '' : 's' }}</span>
                </div>
                <p v-if="!versionDistribution.length" class="text-caption">No version data available.</p>
            </div>
        </div>
    </UiCard>
</template>
