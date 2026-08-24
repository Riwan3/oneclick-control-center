<script setup>
import DeploymentSummary from '../components/deployments/DeploymentSummary.vue';
import VersionOverview from '../components/deployments/VersionOverview.vue';
import DeploymentToolbar from '../components/deployments/DeploymentToolbar.vue';
import DeploymentMatrix from '../components/deployments/DeploymentMatrix.vue';
import DeploymentActivity from '../components/deployments/DeploymentActivity.vue';
import { useDeploymentsOverview } from '../composables/useDeploymentsOverview';

const {
    search,
    versionStatusFilter,
    deploymentStatusFilter,
    versionStatusOptions,
    deploymentStatusOptions,
    summary,
    versionDistribution,
    filteredRecords,
    recentActivity,
} = useDeploymentsOverview();
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-page-title">Version &amp; Deployment Overview</h1>
            <p class="text-body mt-2 max-w-2xl">
                Group-wide view of OneClick application versions and deployment health across all
                PPA sites.
            </p>
        </div>

        <DeploymentSummary :summary="summary" />

        <VersionOverview :distribution="versionDistribution" :total-sites="summary.totalSites" />

        <DeploymentToolbar
            v-model:search="search"
            v-model:version-status="versionStatusFilter"
            v-model:deployment-status="deploymentStatusFilter"
            :version-status-options="versionStatusOptions"
            :deployment-status-options="deploymentStatusOptions"
        />

        <DeploymentMatrix :records="filteredRecords" />

        <DeploymentActivity :activities="recentActivity" />
    </div>
</template>
