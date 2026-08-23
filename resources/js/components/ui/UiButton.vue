<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'ghost', 'danger'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    type: {
        type: String,
        default: 'button',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const sizeClasses = {
    sm: 'px-3 py-1.5 text-xs gap-1.5',
    md: 'px-4 py-2 text-sm gap-2',
    lg: 'px-5 py-2.5 text-sm gap-2',
};

const variantClasses = {
    primary: 'bg-primary text-white hover:bg-primary-hover',
    secondary: 'bg-surface text-text-primary border border-border hover:bg-background',
    ghost: 'bg-transparent text-text-secondary hover:bg-background hover:text-text-primary',
    danger: 'bg-danger text-white hover:opacity-90',
};

const classes = computed(() => [
    'inline-flex items-center justify-center rounded-md font-medium transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:cursor-not-allowed disabled:opacity-50',
    sizeClasses[props.size],
    variantClasses[props.variant],
]);
</script>

<template>
    <button :type="type" :disabled="disabled" :class="classes">
        <slot />
    </button>
</template>
