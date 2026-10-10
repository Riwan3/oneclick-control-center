<script setup>
import { computed } from 'vue';
import UiCard from '../ui/UiCard.vue';

const props = defineProps({
    menus: {
        type: Array,
        required: true,
        // shape: [{ menu_key, label, parent_key, order, configurable, effectiveEnabled }]
    },
    canManage: {
        type: Boolean,
        default: false,
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['toggle']);

// Two-level tree matches the deepest hierarchy the baseline actually
// has for every module (module -> parent -> child, no grandchildren).
const tree = computed(() => {
    const byParent = new Map();
    for (const menu of props.menus) {
        const key = menu.parent_key ?? 'root';
        if (!byParent.has(key)) byParent.set(key, []);
        byParent.get(key).push(menu);
    }
    for (const list of byParent.values()) list.sort((a, b) => a.order - b.order);

    return (byParent.get('root') ?? []).map((menu) => ({
        ...menu,
        children: byParent.get(menu.menu_key) ?? [],
    }));
});
</script>

<template>
    <UiCard>
        <p v-if="!menus.length" class="text-caption">No manifest available.</p>

        <ul v-else class="space-y-1.5">
            <li v-for="node in tree" :key="node.menu_key">
                <label class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-text-primary">
                    <input
                        type="checkbox"
                        :checked="node.effectiveEnabled"
                        :disabled="!node.configurable || !canManage || saving"
                        :title="node.configurable ? '' : 'Derived from child items — not independently configurable.'"
                        @change="$emit('toggle', node.menu_key, $event.target.checked)"
                    />
                    {{ node.label }}
                </label>

                <ul v-if="node.children.length" class="ml-6 space-y-1.5 border-l border-border pl-3">
                    <li v-for="child in node.children" :key="child.menu_key">
                        <label class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-text-secondary">
                            <input
                                type="checkbox"
                                :checked="child.effectiveEnabled"
                                :disabled="!canManage || saving"
                                @change="$emit('toggle', child.menu_key, $event.target.checked)"
                            />
                            {{ child.label }}
                        </label>
                    </li>
                </ul>
            </li>
        </ul>
    </UiCard>
</template>
