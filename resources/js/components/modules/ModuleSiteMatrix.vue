<script setup>
import UiCard from '../ui/UiCard.vue';

defineProps({
    modules: {
        type: Array,
        required: true,
    },
    sites: {
        type: Array,
        required: true,
    },
    selectedModule: {
        type: String,
        default: 'all',
    },
});
</script>

<template>
    <UiCard padding="sm">
        <div class="mb-3 flex items-center gap-4 text-caption">
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-success"></span>
                Active
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block h-2.5 w-2.5 rounded-full border border-border bg-neutral-soft"></span>
                Not Installed
            </span>
        </div>

        <div v-if="sites.length" class="overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse text-sm">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-surface px-3 py-2 text-left text-label">Module</th>
                        <th v-for="site in sites" :key="site.id" class="px-2 py-2 text-center text-label">
                            {{ site.code }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="mod in modules"
                        :key="mod.key"
                        :class="selectedModule === mod.key ? 'bg-primary-soft' : ''"
                    >
                        <td class="sticky left-0 z-10 bg-inherit px-3 py-2 font-medium text-text-primary">
                            {{ mod.label }}
                        </td>
                        <td v-for="site in sites" :key="site.id" class="px-2 py-2 text-center">
                            <span
                                class="inline-block h-2.5 w-2.5 rounded-full"
                                :class="site.modules?.[mod.key] ? 'bg-success' : 'border border-border bg-neutral-soft'"
                                :title="`${mod.label} — ${site.code}: ${site.modules?.[mod.key] ? 'Active' : 'Not Installed'}`"
                            ></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-else class="py-6 text-center text-body">No sites match the current filters.</p>
    </UiCard>
</template>
