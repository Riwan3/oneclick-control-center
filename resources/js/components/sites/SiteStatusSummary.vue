<script setup>
import { computed } from 'vue';
import UiKpiCard from '../ui/UiKpiCard.vue';

const props = defineProps({
    sites: {
        type: Array,
        required: true,
    },
});

function countByStatus(status) {
    return props.sites.filter((site) => site.status === status).length;
}

// Executive Site Summary — exactly the 4 cards this page's registry
// role calls for (site connectivity counts). Module/deployment figures
// intentionally live on Modules.vue / Monitoring.vue via the resolver,
// not re-derived here from raw site.modules flags.
const kpis = computed(() => [
    { id: 'total', title: 'Total Sites', value: props.sites.length, status: 'info', statusLabel: 'Registered' },
    { id: 'online', title: 'Online Sites', value: countByStatus('online'), status: 'success', statusLabel: 'Online' },
    { id: 'warning', title: 'Warning Sites', value: countByStatus('warning'), status: 'warning', statusLabel: 'Warning' },
    { id: 'offline', title: 'Offline Sites', value: countByStatus('offline'), status: 'danger', statusLabel: 'Offline' },
    { id: 'unknown', title: 'Not Connected', value: countByStatus('unknown'), status: 'info', statusLabel: 'No connection yet' },
]);
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <UiKpiCard
            v-for="kpi in kpis"
            :key="kpi.id"
            :title="kpi.title"
            :value="kpi.value"
            :unit="kpi.unit ?? ''"
            :status="kpi.status ?? null"
            :status-label="kpi.statusLabel ?? ''"
        />
    </div>
</template>
