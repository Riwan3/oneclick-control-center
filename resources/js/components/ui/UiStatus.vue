<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'neutral',
        validator: (v) => ['success', 'warning', 'danger', 'info', 'neutral'].includes(v),
    },
    label: {
        type: String,
        default: '',
    },
});

const dotClasses = {
    success: 'bg-success',
    warning: 'bg-warning',
    danger: 'bg-danger',
    info: 'bg-info',
    neutral: 'bg-neutral',
};
</script>

<template>
    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-text-secondary">
        <span class="h-2 w-2 rounded-full" :class="dotClasses[variant]"></span>
        <slot>{{ label }}</slot>
    </span>
</template>
