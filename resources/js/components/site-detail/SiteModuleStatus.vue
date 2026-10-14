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
    // Modules this site reports that are not in the baseline: [{ key, label, version, menu_count }]
    extensions: {
        type: Array,
        default: () => [],
    },
    siteCode: {
        type: String,
        default: '',
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

        <div v-if="extensions.length" class="mt-6 border-t border-border pt-5">
            <p class="text-card-title">Additional modules</p>
            <p class="text-caption mt-1 max-w-2xl">
                Reported by this site but not part of the baseline. They are monitored here and can be switched off in
                Site Configuration.
            </p>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <router-link
                    v-for="ext in extensions"
                    :key="ext.key"
                    :to="{ path: '/module-observatory', query: { module: ext.key, site: siteCode } }"
                    class="rounded-lg border border-border p-4 transition-colors hover:bg-background"
                >
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-card-title">{{ ext.label }}</p>
                        <UiBadge variant="info">Extra</UiBadge>
                    </div>
                    <p class="text-caption mt-3">
                        {{ ext.version ?? 'Version not reported' }} · {{ ext.menu_count }}
                        menu{{ ext.menu_count === 1 ? '' : 's' }}
                    </p>
                </router-link>
            </div>
        </div>
    </UiCard>
</template>
