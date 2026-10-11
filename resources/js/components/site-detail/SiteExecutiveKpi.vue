<script setup>
import { computed } from 'vue';
import UiKpiCard from '../ui/UiKpiCard.vue';
import { modules as moduleCatalog } from '../../config/modules';

const props = defineProps({
    site: {
        type: Object,
        required: true,
    },
});

const statusMeta = {
    online: { status: 'success', label: 'Healthy' },
    warning: { status: 'warning', label: 'Degraded' },
    offline: { status: 'danger', label: 'Offline' },
    unknown: { status: 'info', label: 'Not Connected' },
};

const activeModuleCount = computed(
    () => moduleCatalog.filter((mod) => props.site.modules?.[mod.key]).length
);

const kpis = computed(() => [
    {
        id: 'system-health',
        title: 'System Health',
        value: props.site.systemHealth ?? '—',
        unit: props.site.systemHealth === null ? '' : '%',
        icon: 'monitoring',
        status: statusMeta[props.site.status]?.status,
        statusLabel: statusMeta[props.site.status]?.label,
    },
    {
        id: 'active-users',
        title: 'Active Users',
        value: props.site.users.toLocaleString(),
        icon: 'user',
    },
    {
        id: 'active-modules',
        title: 'Active Modules',
        value: activeModuleCount.value,
        unit: `/ ${moduleCatalog.length}`,
        icon: 'dashboard',
    },
    {
        id: 'version',
        title: 'OneClick Version',
        value: props.site.version,
        icon: 'tag',
    },
]);
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <UiKpiCard
            v-for="kpi in kpis"
            :key="kpi.id"
            :title="kpi.title"
            :value="kpi.value"
            :unit="kpi.unit ?? ''"
            :icon="kpi.icon"
            :status="kpi.status ?? null"
            :status-label="kpi.statusLabel ?? ''"
        />
    </div>
</template>
