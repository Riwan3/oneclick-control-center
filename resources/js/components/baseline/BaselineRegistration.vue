<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';
import UiButton from '../ui/UiButton.vue';

defineProps({
    candidates: {
        type: Array,
        required: true,
        // shape: [{ moduleKey, label, sites: [code], menuCount }]
    },
    canManage: {
        type: Boolean,
        default: false,
    },
    busy: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['register']);
</script>

<template>
    <UiCard padding="sm">
        <ul v-if="candidates.length" class="divide-y divide-border">
            <li
                v-for="candidate in candidates"
                :key="candidate.moduleKey"
                class="flex flex-col gap-3 px-3 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="min-w-0">
                    <p class="font-medium text-text-primary">
                        {{ candidate.label }}
                        <UiBadge variant="info" class="ml-2">Extra</UiBadge>
                    </p>
                    <p class="text-caption mt-0.5">
                        {{ candidate.moduleKey }} · {{ candidate.menuCount }} menu{{ candidate.menuCount === 1 ? '' : 's' }}
                    </p>
                    <p class="text-caption mt-0.5">Reported by {{ candidate.sites.join(', ') }}</p>
                </div>

                <UiButton
                    v-if="canManage"
                    size="sm"
                    class="shrink-0 self-start sm:self-auto"
                    :disabled="busy"
                    @click="$emit('register', candidate.moduleKey)"
                >
                    Register to baseline
                </UiButton>
            </li>
        </ul>

        <p v-else class="px-3 py-6 text-center text-caption">
            No extra modules are waiting. Every module the sites report is already in the baseline.
        </p>
    </UiCard>
</template>
