<script setup>
import UiKpiCard from '../ui/UiKpiCard.vue';

defineProps({
    summary: {
        type: Object,
        required: true,
        // shape: { totalModules, totalSidebarItems, activeModules, activeSidebarItems }
    },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <UiKpiCard title="Total Baseline Modules" :value="summary.totalModules" />
        <UiKpiCard title="Total Baseline Menu Items" :value="summary.totalSidebarItems" />
        <UiKpiCard title="Active Baseline Modules" :value="summary.activeModules" />
        <UiKpiCard title="Active Baseline Menu Items" :value="summary.activeSidebarItems" />
    </div>
</template>
