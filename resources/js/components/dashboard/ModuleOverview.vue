<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    modules: {
        type: Array,
        required: true,
        // shape: [{ key, label, sitesEnabled, totalSites }]
    },
});
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">Module Adoption</h3>
        <p class="text-caption mt-1">Sites with each module enabled</p>

        <div class="mt-6 space-y-4">
            <div v-for="mod in modules" :key="mod.key">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium text-text-secondary">{{ mod.label }}</span>
                    <span class="text-text-muted">{{ mod.sitesEnabled }} / {{ mod.totalSites }} sites</span>
                </div>
                <div class="mt-1.5 h-2 rounded-full bg-background">
                    <div
                        class="h-2 rounded-full bg-primary"
                        :style="{ width: (mod.sitesEnabled / mod.totalSites) * 100 + '%' }"
                    ></div>
                </div>
            </div>
        </div>
    </UiCard>
</template>
