<script setup>
import UiKpiCard from '../ui/UiKpiCard.vue';

defineProps({
    kpis: {
        type: Array,
        required: true,
        // shape: [{ id, title, value, unit, icon, trend?, status?, statusLabel? }]
    },
});
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <UiKpiCard
            v-for="kpi in kpis"
            :key="kpi.id"
            :title="kpi.title"
            :value="kpi.value"
            :unit="kpi.unit"
            :icon="kpi.icon"
            :trend="kpi.trend ?? null"
            :status="kpi.status ?? null"
            :status-label="kpi.statusLabel ?? ''"
        />
    </div>
</template>
