<script setup>
import UiKpiCard from '../ui/UiKpiCard.vue';

defineProps({
    summary: {
        type: Object,
        required: true,
        // shape: { sitesMonitored, synced, updated, changed, drifted, notSynced, totalConfigurationChanges }
    },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
        <UiKpiCard title="Sites Monitored" :value="summary.sitesMonitored" />
        <UiKpiCard title="Synced" :value="summary.synced" />
        <UiKpiCard title="Changed" :value="summary.changed" />
        <UiKpiCard title="Drifted" :value="summary.drifted" />
        <UiKpiCard title="Configuration Changes" :value="summary.totalConfigurationChanges" />
    </div>
</template>
