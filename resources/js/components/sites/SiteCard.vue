<script setup>
import { computed } from 'vue';
import UiCard from '../ui/UiCard.vue';
import UiStatus from '../ui/UiStatus.vue';
import UiBadge from '../ui/UiBadge.vue';
import UiTooltip from '../ui/UiTooltip.vue';
import { modules as moduleCatalog } from '../../config/modules';

const props = defineProps({
    site: {
        type: Object,
        required: true,
    },
});

const statusVariant = { online: 'success', warning: 'warning', offline: 'danger' };
const statusLabel = { online: 'Online', warning: 'Warning', offline: 'Offline' };

const enabledCount = computed(
    () => moduleCatalog.filter((mod) => props.site.modules?.[mod.key]).length
);
</script>

<template>
    <router-link :to="`/sites/${site.id}`" class="block h-full">
        <UiCard padding="sm" class="flex h-full flex-col gap-3 transition-shadow hover:shadow-elevated">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-text-primary">{{ site.code }}</p>
                    <p class="text-caption truncate">{{ site.name }}</p>
                    <p class="text-caption">{{ site.location }}</p>
                </div>
                <UiStatus :variant="statusVariant[site.status]" :label="statusLabel[site.status]" />
            </div>

            <div class="grid grid-cols-3 gap-2 border-t border-border pt-3">
                <div>
                    <p class="text-label">Users</p>
                    <p class="mt-1 text-sm font-semibold text-text-primary">{{ site.users.toLocaleString() }}</p>
                </div>
                <div>
                    <p class="text-label">Health</p>
                    <p class="mt-1 text-sm font-semibold text-text-primary">{{ site.systemHealth }}%</p>
                </div>
                <div>
                    <p class="text-label">Version</p>
                    <p class="mt-1 text-sm font-semibold text-text-primary">{{ site.version }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-1.5 border-t border-border pt-3">
                <UiTooltip
                    v-for="mod in moduleCatalog"
                    :key="mod.key"
                    :content="`${mod.label} — ${site.modules?.[mod.key] ? 'Enabled' : 'Not enabled'}`"
                >
                    <UiBadge :variant="site.modules?.[mod.key] ? 'info' : 'neutral'">
                        {{ mod.label }}
                    </UiBadge>
                </UiTooltip>
                <span v-if="enabledCount === 0" class="text-caption">No modules enabled</span>
            </div>

            <p class="text-caption mt-auto">Last activity: {{ site.lastActivity }}</p>
        </UiCard>
    </router-link>
</template>
