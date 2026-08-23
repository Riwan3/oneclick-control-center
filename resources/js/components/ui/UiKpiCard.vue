<script setup>
import { computed } from 'vue';
import AppIcon from '../layout/AppIcon.vue';
import UiCard from './UiCard.vue';
import UiStatus from './UiStatus.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    unit: {
        type: String,
        default: '',
    },
    trend: {
        type: Object,
        default: null,
        // Shape: { direction: 'up' | 'down' | 'flat', label: string }
    },
    status: {
        type: String,
        default: null,
        validator: (v) => v === null || ['success', 'warning', 'danger', 'info', 'neutral'].includes(v),
    },
    statusLabel: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: null,
    },
});

const trendColorClasses = {
    up: 'text-success',
    down: 'text-danger',
    flat: 'text-text-muted',
};

const trendIconName = computed(() => {
    if (!props.trend) return null;
    return `trend-${props.trend.direction}`;
});
</script>

<template>
    <UiCard>
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-kpi-label">{{ title }}</p>
                <p class="text-kpi-value mt-2">
                    {{ value }}
                    <span v-if="unit" class="text-base font-medium text-text-secondary">{{ unit }}</span>
                </p>
                <p
                    v-if="trend"
                    class="mt-2 flex items-center gap-1 text-xs font-medium"
                    :class="trendColorClasses[trend.direction]"
                >
                    <AppIcon :name="trendIconName" class="h-3.5 w-3.5" />
                    {{ trend.label }}
                </p>
            </div>

            <div
                v-if="icon"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary"
            >
                <AppIcon :name="icon" />
            </div>
        </div>

        <UiStatus v-if="status" :variant="status" :label="statusLabel" class="mt-4" />
    </UiCard>
</template>
