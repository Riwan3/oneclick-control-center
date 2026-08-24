<script setup>
defineProps({
    distribution: {
        type: Array,
        required: true,
        // shape: [{ version, count }]
    },
    totalSites: {
        type: Number,
        required: true,
    },
});

import UiCard from '../ui/UiCard.vue';
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">Version Distribution</h3>
        <p class="text-caption mt-1">Current OneClick platform version across all sites</p>

        <div class="mt-6 space-y-4">
            <div v-for="item in distribution" :key="item.version">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-text-secondary">{{ item.version }}</span>
                    <span class="text-text-muted">{{ item.count }} {{ item.count === 1 ? 'site' : 'sites' }}</span>
                </div>
                <div class="mt-1.5 h-2 rounded-full bg-background">
                    <div
                        class="h-2 rounded-full bg-primary"
                        :style="{ width: (item.count / totalSites) * 100 + '%' }"
                    ></div>
                </div>
            </div>
        </div>
    </UiCard>
</template>
