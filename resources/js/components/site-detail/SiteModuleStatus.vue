<script setup>
import UiCard from '../ui/UiCard.vue';
import UiStatus from '../ui/UiStatus.vue';
import UiBadge from '../ui/UiBadge.vue';
import { modules as moduleCatalog } from '../../config/modules';

defineProps({
    modules: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <UiCard>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="mod in moduleCatalog"
                :key="mod.key"
                class="rounded-lg border border-border p-4"
                :class="modules?.[mod.key] ? '' : 'opacity-60'"
            >
                <div class="flex items-center justify-between gap-2">
                    <p class="text-card-title">{{ mod.label }}</p>
                    <UiBadge :variant="modules?.[mod.key] ? 'success' : 'neutral'">
                        {{ modules?.[mod.key] ? 'Enabled' : 'Disabled' }}
                    </UiBadge>
                </div>
                <UiStatus
                    class="mt-3"
                    :variant="modules?.[mod.key] ? 'success' : 'neutral'"
                    :label="modules?.[mod.key] ? 'Active' : 'Not installed'"
                />
            </div>
        </div>
    </UiCard>
</template>
