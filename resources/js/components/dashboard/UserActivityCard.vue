<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    activity: {
        type: Object,
        required: true,
        // shape: { activeToday, activeThisWeek, inactive, loginTrend: [{ label, value }] }
    },
});
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">User Activity</h3>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <p class="text-kpi-label">Active Today</p>
                <p class="text-kpi-value mt-1">{{ activity.activeToday.toLocaleString() }}</p>
            </div>
            <div>
                <p class="text-kpi-label">Active This Week</p>
                <p class="text-kpi-value mt-1">{{ activity.activeThisWeek.toLocaleString() }}</p>
            </div>
            <div>
                <p class="text-kpi-label">Inactive</p>
                <p class="text-kpi-value mt-1 text-text-muted">{{ activity.inactive.toLocaleString() }}</p>
            </div>
        </div>

        <div class="mt-6">
            <p class="text-caption mb-2">Login activity — last 7 days</p>
            <div class="flex h-16 items-end gap-2">
                <div
                    v-for="point in activity.loginTrend"
                    :key="point.label"
                    class="flex-1 rounded-t-md bg-maroon/70"
                    :style="{ height: point.value + '%' }"
                ></div>
            </div>
        </div>
    </UiCard>
</template>
