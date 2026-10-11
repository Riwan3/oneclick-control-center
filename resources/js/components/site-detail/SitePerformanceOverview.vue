<script setup>
import { computed } from 'vue';
import UiCard from '../ui/UiCard.vue';

const props = defineProps({
    daily: {
        type: Array,
        default: () => [],
        // shape: [{ label, logins, unique_users }] — last 7 days, from the site's login log
    },
    health: {
        type: Object,
        default: null,
        // shape: { score, cpu_pct, memory_pct, disk_pct, db_latency_ms } — null values mean "not readable"
    },
});

function bars(key) {
    const peak = Math.max(1, ...props.daily.map((day) => day[key]));
    return props.daily.map((day) => ({
        label: day.label,
        count: day[key],
        height: Math.round((day[key] / peak) * 100),
    }));
}

const activeUsers = computed(() => bars('unique_users'));
const logins = computed(() => bars('logins'));

const resources = computed(() => [
    { key: 'cpu', label: 'CPU load', value: props.health?.cpu_pct ?? null },
    { key: 'memory', label: 'Memory', value: props.health?.memory_pct ?? null },
    { key: 'disk', label: 'Disk', value: props.health?.disk_pct ?? null },
]);
</script>

<template>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <UiCard>
            <h3 class="text-card-title">Active Users</h3>
            <p class="text-caption mt-1">Unique users logged in per day, last 7 days</p>
            <div class="mt-6 flex h-28 items-stretch gap-1.5">
                <div v-for="point in activeUsers" :key="point.label" class="flex flex-1 flex-col items-center gap-2">
                    <div class="flex w-full flex-1 items-end">
                        <div class="w-full rounded-t-md bg-primary/80" :style="{ height: point.height + '%' }"></div>
                    </div>
                    <span class="text-caption">{{ point.label }}</span>
                    <span class="text-caption font-medium">{{ point.count }}</span>
                </div>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-card-title">Login Trend</h3>
            <p class="text-caption mt-1">Successful logins per day, last 7 days</p>
            <div class="mt-6 flex h-28 items-stretch gap-1.5">
                <div v-for="point in logins" :key="point.label" class="flex flex-1 flex-col items-center gap-2">
                    <div class="flex w-full flex-1 items-end">
                        <div class="w-full rounded-t-md bg-success/70" :style="{ height: point.height + '%' }"></div>
                    </div>
                    <span class="text-caption">{{ point.label }}</span>
                    <span class="text-caption font-medium">{{ point.count }}</span>
                </div>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-card-title">Server Condition</h3>
            <p class="text-caption mt-1">Current resource usage on the site's server</p>
            <div class="mt-6 space-y-4">
                <div v-for="resource in resources" :key="resource.key">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-text-secondary">{{ resource.label }}</span>
                        <span class="text-text-muted">{{ resource.value === null ? '—' : resource.value + '%' }}</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-background">
                        <div
                            class="h-2 rounded-full bg-primary"
                            :style="{ width: (resource.value ?? 0) + '%' }"
                        ></div>
                    </div>
                </div>
                <div class="flex items-center justify-between border-t border-border/60 pt-3 text-sm">
                    <span class="font-medium text-text-secondary">Database latency</span>
                    <span class="text-text-muted">
                        {{ health?.db_latency_ms == null ? '—' : health.db_latency_ms + ' ms' }}
                    </span>
                </div>
            </div>
        </UiCard>
    </div>
</template>
