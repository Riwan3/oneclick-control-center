<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    performance: {
        type: Object,
        required: true,
        // shape: { activeUserTrend, moduleUtilization, systemHealthTrend }
    },
});
</script>

<template>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <UiCard>
            <h3 class="text-card-title">Active User Trend</h3>
            <p class="text-caption mt-1">Last 7 days, relative to peak</p>
            <div class="mt-6 flex h-28 items-stretch gap-1.5">
                <div
                    v-for="point in performance.activeUserTrend"
                    :key="point.label"
                    class="flex flex-1 flex-col items-center gap-2"
                >
                    <div class="flex w-full flex-1 items-end">
                        <div class="w-full rounded-t-md bg-primary/80" :style="{ height: point.value + '%' }"></div>
                    </div>
                    <span class="text-caption">{{ point.label }}</span>
                </div>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-card-title">System Health Trend</h3>
            <p class="text-caption mt-1">Last 7 days</p>
            <div class="mt-6 flex h-28 items-stretch gap-1.5">
                <div
                    v-for="point in performance.systemHealthTrend"
                    :key="point.label"
                    class="flex flex-1 flex-col items-center gap-2"
                >
                    <div class="flex w-full flex-1 items-end">
                        <div class="w-full rounded-t-md bg-success/70" :style="{ height: point.value + '%' }"></div>
                    </div>
                    <span class="text-caption">{{ point.label }}</span>
                </div>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-card-title">Module Utilization</h3>
            <p class="text-caption mt-1">Relative usage for this site</p>
            <div class="mt-6 space-y-4">
                <div v-for="mod in performance.moduleUtilization" :key="mod.key">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-text-secondary">{{ mod.label }}</span>
                        <span class="text-text-muted">{{ mod.value }}%</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-background">
                        <div class="h-2 rounded-full bg-primary" :style="{ width: mod.value + '%' }"></div>
                    </div>
                </div>
            </div>
        </UiCard>
    </div>
</template>
