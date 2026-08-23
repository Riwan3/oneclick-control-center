<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'surface',
        validator: (v) => ['surface', 'glass'].includes(v),
    },
    padding: {
        type: String,
        default: 'md',
        validator: (v) => ['none', 'sm', 'md', 'lg'].includes(v),
    },
    elevated: {
        type: Boolean,
        default: false,
    },
});

const paddingClasses = {
    none: '',
    sm: 'p-4',
    md: 'p-6',
    lg: 'p-8',
};

const classes = computed(() => [
    'rounded-xl',
    props.variant === 'glass'
        ? 'glass'
        : ['bg-surface', 'border', 'border-border', props.elevated ? 'shadow-elevated' : 'shadow-card'],
    paddingClasses[props.padding],
]);
</script>

<template>
    <div :class="classes">
        <div v-if="$slots.header" class="mb-4">
            <slot name="header" />
        </div>
        <slot />
    </div>
</template>
