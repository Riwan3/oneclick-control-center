<script setup>
import { computed } from 'vue';
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    menus: {
        type: Array,
        default: () => [],
    },
    // Optional: { added, removed, modified } from diffMenus() — when
    // provided, items are tagged with what changed relative to the
    // baseline. Pass null when rendering the baseline tree itself (it
    // has nothing to compare against).
    diff: {
        type: Object,
        default: null,
    },
});

function badgeFor(menuId) {
    if (!props.diff) return null;
    if (props.diff.added.some((menu) => menu.id === menuId)) return { variant: 'success', label: 'Added' };
    if (props.diff.modified.some((entry) => entry.current.id === menuId)) return { variant: 'warning', label: 'Modified' };
    return null;
}

// Builds a parent/child tree from the flat manifest (id/parentId/order),
// plus synthetic nodes for any baseline item the diff says was removed
// — those aren't in `menus` at all, but the tree still needs to show
// them (struck through) so a removal is visible in place. Two levels
// deep matches the deepest manifest this step's mock data actually
// has ("Personal Data Operator" -> its children); extend the template
// if a future manifest goes deeper.
const tree = computed(() => {
    const removedNodes = (props.diff?.removed ?? []).map((menu) => ({ ...menu, _removed: true }));
    const all = [...props.menus, ...removedNodes];

    const byParent = new Map();
    for (const menu of all) {
        const key = menu.parentId ?? 'root';
        if (!byParent.has(key)) byParent.set(key, []);
        byParent.get(key).push(menu);
    }
    for (const list of byParent.values()) {
        list.sort((a, b) => a.order - b.order);
    }

    return (byParent.get('root') ?? []).map((menu) => ({
        ...menu,
        children: byParent.get(menu.id) ?? [],
    }));
});
</script>

<template>
    <UiCard>
        <p class="text-label mb-3">{{ title }}</p>

        <p v-if="!menus.length && !diff?.removed.length" class="text-caption">No manifest available.</p>

        <ul v-else class="space-y-1">
            <li v-for="node in tree" :key="node.id">
                <div
                    class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm"
                    :class="node._removed ? 'text-text-muted line-through' : 'text-text-primary'"
                >
                    {{ node.label }}
                    <UiBadge v-if="node._removed" variant="danger">Removed</UiBadge>
                    <UiBadge v-else-if="badgeFor(node.id)" :variant="badgeFor(node.id).variant">
                        {{ badgeFor(node.id).label }}
                    </UiBadge>
                </div>

                <ul v-if="node.children.length" class="ml-5 space-y-1 border-l border-border pl-3">
                    <li v-for="child in node.children" :key="child.id">
                        <div
                            class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm"
                            :class="child._removed ? 'text-text-muted line-through' : 'text-text-secondary'"
                        >
                            {{ child.label }}
                            <UiBadge v-if="child._removed" variant="danger">Removed</UiBadge>
                            <UiBadge v-else-if="badgeFor(child.id)" :variant="badgeFor(child.id).variant">
                                {{ badgeFor(child.id).label }}
                            </UiBadge>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </UiCard>
</template>
