<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    userTrend: {
        type: Array,
        required: true,
        // shape: [{ label, value }] — value is 0-100
    },
    moduleUtilization: {
        type: Array,
        required: true,
        // shape: [{ key, label, value }] — value is 0-100
    },
});
</script>

<template>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <UiCard>
            <h3 class="text-card-title">Active User Trend</h3>
            <p class="text-caption mt-1">Last 7 days, relative to peak capacity</p>

            <div class="mt-6 flex h-32 items-end gap-2">
                <div v-for="point in userTrend" :key="point.label" class="flex flex-1 flex-col items-center gap-2">
                    <div class="flex h-full w-full items-end">
                        <div class="w-full rounded-t-md bg-primary/80" :style="{ height: point.value + '%' }"></div>
                    </div>
                    <span class="text-caption">{{ point.label }}</span>
                </div>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-card-title">Module Utilization</h3>
            <p class="text-caption mt-1">Relative usage across active sites</p>

            <div class="mt-6 space-y-4">
                <div v-for="mod in moduleUtilization" :key="mod.key">
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
