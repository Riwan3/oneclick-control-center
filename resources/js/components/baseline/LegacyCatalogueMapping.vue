<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';

defineProps({
    entries: {
        type: Array,
        default: () => [],
        // shape: [{ legacyKey, legacyLabel, suggestedBaselineKey, confidence, note }]
    },
});

const CONFIDENCE_VARIANT = {
    likely: 'info',
    unconfirmed: 'warning',
    unresolved: 'danger',
};

const CONFIDENCE_LABEL = {
    likely: 'Likely Match',
    unconfirmed: 'Unconfirmed',
    unresolved: 'Unresolved',
};
</script>

<template>
    <UiCard v-if="entries.length">
        <p class="text-label mb-1">Legacy Catalogue Mapping</p>
        <p class="text-caption mb-4">
            config/modules.js (the existing Control Center module-access catalogue) is not modified
            by this baseline — it backs a separate, still-functioning system. This is how its 4
            entries relate to the 8 official baseline modules, where known.
        </p>

        <div class="space-y-3">
            <div v-for="entry in entries" :key="entry.legacyKey" class="flex items-start gap-3">
                <UiBadge :variant="CONFIDENCE_VARIANT[entry.confidence]">{{ CONFIDENCE_LABEL[entry.confidence] }}</UiBadge>
                <div class="min-w-0 text-sm">
                    <p class="font-medium text-text-primary">
                        {{ entry.legacyLabel }}
                        <span class="font-normal text-text-muted">({{ entry.legacyKey }})</span>
                        <span class="text-text-muted">→</span>
                        {{ entry.suggestedBaselineKey ?? 'no baseline match' }}
                    </p>
                    <p class="text-caption mt-0.5">{{ entry.note }}</p>
                </div>
            </div>
        </div>
    </UiCard>
</template>
