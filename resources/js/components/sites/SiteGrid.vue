<script setup>
import SiteCard from './SiteCard.vue';
import UiCard from '../ui/UiCard.vue';

defineProps({
    sites: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="sites.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <SiteCard v-for="site in sites" :key="site.id" :site="site" />
    </div>
    <UiCard v-else>
        <p class="text-body">No sites match the current filters.</p>
    </UiCard>
</template>
