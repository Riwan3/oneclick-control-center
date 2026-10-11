<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';
import UiStatus from '../ui/UiStatus.vue';

defineProps({
    records: {
        type: Array,
        required: true,
        // shape: [{ siteId, site, currentVersion, targetVersion, versionStatus, deploymentStatus, lastDeployedAt }]
    },
});

const versionStatusMeta = {
    latest: { variant: 'success', label: 'Latest' },
    update_available: { variant: 'info', label: 'Update Available' },
    behind: { variant: 'warning', label: 'Behind' },
};

const deploymentStatusMeta = {
    healthy: { variant: 'success', label: 'Healthy' },
    warning: { variant: 'warning', label: 'Warning' },
    failed: { variant: 'danger', label: 'Failed' },
};
</script>

<template>
    <UiCard padding="sm">
        <div v-if="records.length" class="overflow-x-auto">
            <table class="w-full min-w-[860px] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="px-3 py-2 text-left text-label">Site</th>
                        <th class="px-3 py-2 text-left text-label">Current Version</th>
                        <th class="px-3 py-2 text-left text-label">Target Version</th>
                        <th class="px-3 py-2 text-left text-label">Version Status</th>
                        <th class="px-3 py-2 text-left text-label">Deployment Status</th>
                        <th class="px-3 py-2 text-left text-label">Environment</th>
                        <th class="px-3 py-2 text-left text-label">Last Deployed</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="record in records"
                        :key="record.siteId"
                        class="border-b border-border last:border-0"
                    >
                        <td class="px-3 py-2.5">
                            <p class="font-medium text-text-primary">{{ record.site.code }}</p>
                            <p class="text-caption">{{ record.site.name }}</p>
                        </td>
                        <td class="px-3 py-2.5 text-text-secondary">{{ record.currentVersion }}</td>
                        <td class="px-3 py-2.5 text-text-secondary">{{ record.targetVersion }}</td>
                        <td class="px-3 py-2.5">
                            <UiBadge :variant="versionStatusMeta[record.versionStatus]?.variant">
                                {{ versionStatusMeta[record.versionStatus]?.label }}
                            </UiBadge>
                        </td>
                        <td class="px-3 py-2.5">
                            <UiStatus
                                :variant="deploymentStatusMeta[record.deploymentStatus]?.variant"
                                :label="deploymentStatusMeta[record.deploymentStatus]?.label"
                            />
                        </td>
                        <td class="px-3 py-2.5 text-text-secondary capitalize">{{ record.environment }}</td>
                        <td class="px-3 py-2.5 text-caption">{{ record.lastDeployedAt }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="py-6 text-center text-body">No deployments match the current filters.</p>
    </UiCard>
</template>
