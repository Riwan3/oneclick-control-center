<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    search: {
        type: String,
        default: '',
    },
    versionStatus: {
        type: String,
        default: 'all',
    },
    deploymentStatus: {
        type: String,
        default: 'all',
    },
    versionStatusOptions: {
        type: Array,
        required: true,
    },
    deploymentStatusOptions: {
        type: Array,
        required: true,
    },
});

defineEmits(['update:search', 'update:versionStatus', 'update:deploymentStatus']);

const fieldClasses =
    'rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';
</script>

<template>
    <UiCard padding="sm">
        <div class="flex flex-wrap items-center gap-3">
            <input
                type="text"
                :value="search"
                placeholder="Search by site code or name…"
                :class="fieldClasses"
                class="min-w-[220px] flex-1"
                @input="$emit('update:search', $event.target.value)"
            />

            <select
                :value="versionStatus"
                :class="fieldClasses"
                @change="$emit('update:versionStatus', $event.target.value)"
            >
                <option v-for="opt in versionStatusOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                </option>
            </select>

            <select
                :value="deploymentStatus"
                :class="fieldClasses"
                @change="$emit('update:deploymentStatus', $event.target.value)"
            >
                <option v-for="opt in deploymentStatusOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                </option>
            </select>
        </div>
    </UiCard>
</template>
