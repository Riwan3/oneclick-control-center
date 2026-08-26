<script setup>
import UiCard from '../ui/UiCard.vue';
import UiStatus from '../ui/UiStatus.vue';

defineProps({
    sites: {
        type: Array,
        required: true,
    },
    selectedSiteId: {
        type: String,
        default: null,
    },
});

defineEmits(['select']);

const STATUS_VARIANT = {
    synced: 'success',
    updated: 'info',
    changed: 'warning',
    drifted: 'danger',
    not_synced: 'neutral',
};

const STATUS_LABEL = {
    synced: 'Synced',
    updated: 'Updated',
    changed: 'Changed',
    drifted: 'Drifted',
    not_synced: 'Not Synced',
};
</script>

<template>
    <UiCard padding="sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-caption">
                        <th class="px-3 py-2">Site</th>
                        <th class="px-3 py-2">Version</th>
                        <th class="px-3 py-2">Menus</th>
                        <th class="px-3 py-2">Added</th>
                        <th class="px-3 py-2">Removed</th>
                        <th class="px-3 py-2">Modified</th>
                        <th class="px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="site in sites"
                        :key="site.siteId"
                        class="cursor-pointer border-b border-border last:border-0 hover:bg-background"
                        :class="site.siteId === selectedSiteId ? 'bg-primary-soft/40' : ''"
                        @click="$emit('select', site.siteId)"
                    >
                        <td class="px-3 py-2 font-medium text-text-primary">
                            {{ site.siteCode }}
                            <span class="text-caption font-normal">{{ site.siteName }}</span>
                        </td>
                        <td class="px-3 py-2 text-text-secondary">{{ site.version ?? '—' }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ site.menus ? site.menus.length : '—' }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ site.diff.added.length }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ site.diff.removed.length }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ site.diff.modified.length }}</td>
                        <td class="px-3 py-2">
                            <UiStatus :variant="STATUS_VARIANT[site.syncStatus]" :label="STATUS_LABEL[site.syncStatus]" />
                        </td>
                    </tr>
                    <tr v-if="!sites.length">
                        <td colspan="7" class="px-3 py-6 text-center text-caption">No sites available.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </UiCard>
</template>
