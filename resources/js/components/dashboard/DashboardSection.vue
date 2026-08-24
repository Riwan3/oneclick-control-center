<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <section>
        <div class="mb-4">
            <h2 class="text-section-title">{{ title }}</h2>
            <p v-if="description" class="text-caption mt-1">{{ description }}</p>
        </div>
        <slot />
    </section>
</template>
