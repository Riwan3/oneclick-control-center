<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';

defineProps({
    baselineVersion: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <UiCard>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-label">Master Baseline</p>
                <h1 class="text-page-title mt-1">Official OneClick ecosystem baseline</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Central reference for the modules and sidebar structure released across
                    OneClick sites.
                </p>
                <p class="text-caption mt-2 max-w-2xl">
                    The core modules are locked: they are the starting point of OneClick on every site, taken
                    from the OneClick SKS site. A Super Admin can register an extra module a site reports into
                    the baseline (or unregister it again); a site can switch off baseline modules and menus in
                    Site Configuration.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <UiBadge variant="info">Locked</UiBadge>
                <UiBadge v-if="baselineVersion" variant="neutral">Baseline {{ baselineVersion }}</UiBadge>
            </div>
        </div>
    </UiCard>
</template>
