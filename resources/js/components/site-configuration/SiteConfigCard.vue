<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    site: {
        type: Object,
        required: true,
        // shape: { siteCode, identity: {name}, summary: {modulesEnabled, modulesTotal, menusEnabled, menusTotal, completeness} }
    },
    selected: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['select']);
</script>

<template>
    <UiCard
        padding="sm"
        :class="['cursor-pointer transition-colors', selected ? 'ring-2 ring-primary' : 'hover:bg-background']"
        @click="$emit('select', site.siteCode)"
    >
        <div>
            <p class="text-sm font-semibold text-text-primary">{{ site.siteCode }}</p>
            <p class="text-caption">{{ site.identity.name }}</p>
        </div>

        <div class="mt-3 space-y-1 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-text-secondary">Modules</span>
                <span class="font-medium text-text-primary">{{ site.summary.modulesEnabled }} / {{ site.summary.modulesTotal }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-text-secondary">Menus</span>
                <span class="font-medium text-text-primary">{{ site.summary.menusEnabled }} / {{ site.summary.menusTotal }}</span>
            </div>
        </div>
    </UiCard>
</template>
