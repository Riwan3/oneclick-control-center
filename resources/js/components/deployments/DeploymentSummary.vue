<script setup>
import UiKpiCard from '../ui/UiKpiCard.vue';

defineProps({
    summary: {
        type: Object,
        required: true,
        // shape: { totalSites, latest, updateAvailable, behind, deploymentIssues }
    },
});
</script>

<template>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
        <UiKpiCard title="Total Sites" :value="summary.totalSites" />
        <UiKpiCard title="Latest" :value="summary.latest" status="success" status-label="Up to date" />
        <UiKpiCard
            title="Update Available"
            :value="summary.updateAvailable"
            status="info"
            status-label="Update available"
        />
        <UiKpiCard title="Behind" :value="summary.behind" status="warning" status-label="Behind target" />
        <UiKpiCard
            title="Deployment Warning / Failed"
            :value="summary.deploymentIssues"
            status="danger"
            status-label="Needs attention"
        />
    </div>
</template>
