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

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="option in [
                        { value: 'all', label: 'All' },
                        { value: 'online', label: 'Online' },
                        { value: 'warning', label: 'Warning' },
                        { value: 'offline', label: 'Offline' },
                        { value: 'unknown', label: 'Not Connected' },
                    ]"
                    :key="option.value"
                    type="button"
                    class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                    :class="
                        status === option.value
                            ? 'bg-primary text-white'
                            : 'border border-border bg-surface text-text-secondary hover:bg-background'
                    "
                    @click="$emit('update:status', option.value)"
                >
                    {{ option.label }}
                </button>
            </div>

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
