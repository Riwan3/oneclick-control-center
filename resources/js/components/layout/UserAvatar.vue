<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    src: { type: String, default: null },
    size: { type: String, default: 'h-8 w-8' },
});
</script>

<template>
    <div class="flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-soft text-primary" :class="size">
        <img v-if="src" :src="src" alt="" class="h-full w-full object-cover" />
        <AppIcon v-else name="user" class="h-1/2 w-1/2" />
    </div>
</template>
