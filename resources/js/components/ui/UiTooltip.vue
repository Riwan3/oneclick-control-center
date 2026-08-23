<script setup>
defineProps({
    content: {
        type: String,
        default: '',
    },
    position: {
        type: String,
        default: 'top',
        validator: (v) => ['top', 'bottom', 'left', 'right'].includes(v),
    },
});

const positionClasses = {
    top: 'bottom-full left-1/2 mb-2 -translate-x-1/2',
    bottom: 'top-full left-1/2 mt-2 -translate-x-1/2',
    left: 'right-full top-1/2 mr-2 -translate-y-1/2',
    right: 'left-full top-1/2 ml-2 -translate-y-1/2',
};
</script>

<template>
    <span class="group/tooltip relative inline-flex">
        <slot />
        <span
            class="pointer-events-none absolute z-50 whitespace-nowrap rounded-md bg-text-primary px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-elevated transition-opacity duration-150 group-hover/tooltip:opacity-100"
            :class="positionClasses[position]"
        >
            <slot name="content">{{ content }}</slot>
        </span>
    </span>
</template>
