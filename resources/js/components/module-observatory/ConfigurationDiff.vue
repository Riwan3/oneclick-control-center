<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    moduleName: {
        type: String,
        required: true,
    },
    baselineCount: {
        type: Number,
        required: true,
    },
    currentCount: {
        type: Number,
        required: true,
    },
    diff: {
        type: Object,
        required: true,
        // shape: { added, removed, modified }
    },
});
</script>

<template>
    <UiCard>
        <p class="text-label mb-3">{{ moduleName }} Configuration Diff</p>

        <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
            <div>
                <p class="text-caption">Baseline</p>
                <p class="text-kpi-value mt-1">
                    {{ baselineCount }} <span class="text-base font-medium text-text-secondary">menus</span>
                </p>
            </div>
            <div>
                <p class="text-caption">Current</p>
                <p class="text-kpi-value mt-1">
                    {{ currentCount }} <span class="text-base font-medium text-text-secondary">menus</span>
                </p>
            </div>
            <div>
                <p class="text-caption">Added</p>
                <p class="text-kpi-value mt-1" :class="diff.added.length ? 'text-success' : ''">
                    +{{ diff.added.length }}
                </p>
            </div>
            <div>
                <p class="text-caption">Removed</p>
                <p class="text-kpi-value mt-1" :class="diff.removed.length ? 'text-danger' : ''">
                    -{{ diff.removed.length }}
                </p>
            </div>
        </div>

        <div class="mt-4 space-y-3 text-sm">
            <div>
                <p v-if="diff.added.length" class="font-medium text-success">Added</p>
                <ul v-if="diff.added.length" class="ml-4 list-disc text-text-secondary">
                    <li v-for="menu in diff.added" :key="menu.id">{{ menu.label }}</li>
                </ul>
                <p v-else class="text-caption">No added items</p>
            </div>

            <div>
                <p v-if="diff.removed.length" class="font-medium text-danger">Removed</p>
                <ul v-if="diff.removed.length" class="ml-4 list-disc text-text-secondary">
                    <li v-for="menu in diff.removed" :key="menu.id">{{ menu.label }}</li>
                </ul>
                <p v-else class="text-caption">No removed items</p>
            </div>

            <div>
                <p v-if="diff.modified.length" class="font-medium text-warning">Modified</p>
                <ul v-if="diff.modified.length" class="ml-4 list-disc text-text-secondary">
                    <li v-for="entry in diff.modified" :key="entry.baseline.id">
                        {{ entry.baseline.label }} <span class="text-text-muted">→</span> {{ entry.current.label }}
                    </li>
                </ul>
                <p v-else class="text-caption">No modified items</p>
            </div>
        </div>
    </UiCard>
</template>
