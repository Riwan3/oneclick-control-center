<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'neutral',
        validator: (v) => ['success', 'warning', 'danger', 'info', 'neutral'].includes(v),
    },
});

const variantClasses = {
    success: 'bg-success-soft text-success',
    warning: 'bg-warning-soft text-warning',
    danger: 'bg-danger-soft text-danger',
    info: 'bg-info-soft text-info',
    neutral: 'bg-neutral-soft text-neutral',
};

const classes = computed(() => [
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
    variantClasses[props.variant],
]);
</script>

<template>
    <span :class="classes">
        <slot />
    </span>
</template>
