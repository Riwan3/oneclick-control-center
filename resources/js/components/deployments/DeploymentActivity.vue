<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';
import AppIcon from '../layout/AppIcon.vue';

defineProps({
    activities: {
        type: Array,
        required: true,
        // shape: [{ siteId, site, currentVersion, deploymentStatus, lastDeployedAt }]
    },
});

const statusMeta = {
    healthy: { variant: 'success', verb: 'deployed successfully' },
    warning: { variant: 'warning', verb: 'deployment warning' },
    failed: { variant: 'danger', verb: 'deployment failed' },
};
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">Recent Deployment Activity</h3>

        <ul class="mt-4 space-y-3">
            <li
                v-for="record in activities"
                :key="record.siteId"
                class="flex items-start gap-3 rounded-lg border border-border px-3 py-2.5"
            >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                    <AppIcon name="package" class="h-4 w-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-text-primary">{{ record.site.code }}</p>
                    <p class="text-caption mt-0.5">
                        {{ record.currentVersion }} {{ statusMeta[record.deploymentStatus]?.verb }}
                    </p>
                    <p class="text-caption mt-0.5">{{ record.lastDeployedAt }}</p>
                </div>
                <UiBadge :variant="statusMeta[record.deploymentStatus]?.variant">
                    {{ record.deploymentStatus }}
                </UiBadge>
            </li>
        </ul>
    </UiCard>
</template>
