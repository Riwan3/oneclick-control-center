<script setup>
import { modules as moduleCatalog } from '../../config/modules';
import UiCard from '../ui/UiCard.vue';

defineProps({
    search: {
        type: String,
        default: '',
    },
    status: {
        type: String,
        default: 'all',
    },
    module: {
        type: String,
        default: 'all',
    },
});

defineEmits(['update:search', 'update:status', 'update:module']);

const fieldClasses =
    'rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';
</script>

<template>
    <UiCard padding="sm">
        <div class="flex flex-wrap items-center gap-3">
            <input
                type="text"
                :value="search"
                placeholder="Search by code, name, or location…"
                :class="fieldClasses"
                class="min-w-[220px] flex-1"
                @input="$emit('update:search', $event.target.value)"
            />

            <select
                :value="status"
                :class="fieldClasses"
                @change="$emit('update:status', $event.target.value)"
            >
                <option value="all">All statuses</option>
                <option value="online">Online</option>
                <option value="warning">Warning</option>
                <option value="offline">Offline</option>
            </select>

            <select
                :value="module"
                :class="fieldClasses"
                @change="$emit('update:module', $event.target.value)"
            >
                <option value="all">All modules</option>
                <option v-for="mod in moduleCatalog" :key="mod.key" :value="mod.key">
                    {{ mod.label }}
                </option>
            </select>
        </div>
    </UiCard>
</template>
