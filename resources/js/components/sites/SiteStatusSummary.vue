<script setup>
import { computed } from 'vue';
import UiKpiCard from '../ui/UiKpiCard.vue';
import { modules as moduleCatalog } from '../../config/modules';

const props = defineProps({
    sites: {
        type: Array,
        required: true,
    },
});

function countByStatus(status) {
    return props.sites.filter((site) => site.status === status).length;
}

const totalUsers = computed(() => props.sites.reduce((sum, site) => sum + site.users, 0));

const activeModules = computed(() =>
    props.sites.reduce(
        (sum, site) => sum + moduleCatalog.filter((mod) => site.modules?.[mod.key]).length,
        0
    )
);

const kpis = computed(() => [
    { id: 'total', title: 'Total Sites', value: props.sites.length },
    { id: 'online', title: 'Online', value: countByStatus('online'), status: 'success', statusLabel: 'Online' },
    { id: 'warning', title: 'Warning', value: countByStatus('warning'), status: 'warning', statusLabel: 'Warning' },
    { id: 'offline', title: 'Offline', value: countByStatus('offline'), status: 'danger', statusLabel: 'Offline' },
    { id: 'users', title: 'Total Users', value: totalUsers.value.toLocaleString() },
    { id: 'modules', title: 'Active Modules', value: activeModules.value, unit: 'enabled' },
]);
</script>

<template>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
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
