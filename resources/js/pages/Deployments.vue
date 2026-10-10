<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import DeploymentSummary from '../components/deployments/DeploymentSummary.vue';
import VersionOverview from '../components/deployments/VersionOverview.vue';
import DeploymentToolbar from '../components/deployments/DeploymentToolbar.vue';
import DeploymentMatrix from '../components/deployments/DeploymentMatrix.vue';
import DeploymentActivity from '../components/deployments/DeploymentActivity.vue';
import ModuleSiteMatrix from '../components/modules/ModuleSiteMatrix.vue';
import { useDeploymentsOverview } from '../composables/useDeploymentsOverview';
import { useModulesOverview } from '../composables/useModulesOverview';

const auth = useAuthStore();

// Existing version/release deployment tracking — untouched, still the
// authoritative source for the "Release & Version Deployment" section
// further down this page.
const {
    search,
    versionStatusFilter,
    deploymentStatusFilter,
    versionStatusOptions,
    deploymentStatusOptions,
    summary: versionSummary,
    versionDistribution,
    filteredRecords,
    recentActivity,
} = useDeploymentsOverview();

// Governance-aware module deployment — the same resolver chain
// Modules.vue/Monitoring.vue/Dashboard.vue already consume. No second
// status computation: modules/filteredSites/moduleCatalog/summary are
// all read directly from useModulesOverview()'s existing output.
const { modules, filteredSites, moduleCatalog, summary: moduleSummary } = useModulesOverview();

const totalPossible = computed(() => moduleSummary.value.totalModules * moduleSummary.value.totalSites);
const totalConfigured = computed(() => modules.value.reduce((sum, mod) => sum + mod.configuredSites, 0));
const totalPending = computed(() => modules.value.reduce((sum, mod) => sum + mod.pendingDeploymentSites, 0));

const executiveDeploymentSummary = computed(() => [
    {
        id: 'canonical-modules',
        title: 'Canonical Modules',
        value: moduleSummary.value.totalModules,
        unit: 'modules',
        status: 'info',
        statusLabel: 'Baseline Registry',
    },
    {
        id: 'configured',
        title: 'Configured Deployments',
        value: `${totalConfigured.value}/${totalPossible.value}`,
        unit: 'site-modules',
        status: 'neutral',
        statusLabel: 'Governance-Permitted',
    },
    {
        id: 'effective-active',
        title: 'Effective Active',
        value: `${moduleSummary.value.effectiveActiveDeployments}/${totalPossible.value}`,
        unit: 'site-modules',
        trend: { direction: 'flat', label: `${moduleSummary.value.rawActiveDeployments} raw active` },
        status: 'success',
        statusLabel: 'Governance-Compliant',
    },
    {
        id: 'pending',
        title: 'Pending Deployment',
        value: totalPending.value,
        unit: 'site-modules',
        status: totalPending.value > 0 ? 'info' : 'success',
        statusLabel: totalPending.value > 0 ? 'Awaiting Runtime' : 'None Pending',
    },
    {
        id: 'policy-violations',
        title: 'Policy Violations',
        value: moduleSummary.value.policyViolations,
        unit: 'site-modules',
        status: moduleSummary.value.policyViolations > 0 ? 'warning' : 'success',
        statusLabel: moduleSummary.value.policyViolations > 0 ? 'Requires attention' : 'No exceptions',
    },
]);

// Governance Exceptions — derived from the resolver's own per-site
// effectiveModules map (never a second status computation). Same
// derivation used on Dashboard.vue, Monitoring.vue, and Modules.vue —
// if a violation disappears from the underlying data, it disappears
// from this list automatically.
const policyViolationDetails = computed(() => {
    const items = [];
    for (const site of filteredSites.value) {
        for (const mod of moduleCatalog.value) {
            if (site.effectiveModules[mod.key]?.effectiveStatus === 'policy_violation') {
                items.push({ id: `${site.code}-${mod.key}`, code: site.code, moduleLabel: mod.label });
            }
        }
    }
    return items;
});

const deploymentSemantics = [
    { label: 'Configured', desc: 'Governance allows the module for the site.' },
    { label: 'Raw Active', desc: 'Runtime telemetry reports the module as deployed.' },
    { label: 'Effective Active', desc: 'Governance allows AND runtime reports deployed.' },
    { label: 'Pending', desc: 'Governance allows but runtime deployment is absent.' },
    { label: 'Policy Violation', desc: 'Governance disables the module while runtime still reports it deployed.' },
];
</script>

<template>
    <!-- Page-level capability guard: nav already hides this link when
         version_view is false, but direct URL navigation bypasses nav
         hiding — this stops the page itself from rendering. Same
         auth.can() mechanism as every other capability check in the
         app; no second authorization system. -->
    <div v-if="auth.can('version_view')" class="space-y-8">
        <!-- Header -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Deployments</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Runtime deployment observatory and governance alignment across all connected PPA sites.
                </p>
            </div>
            <UiBadge variant="info" class="px-3 py-1 text-xs">System Telemetry Active (Local Feed)</UiBadge>
        </div>

        <!-- Executive Deployment Summary -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <UiKpiCard
                v-for="kpi in executiveDeploymentSummary"
                :key="kpi.id"
                :title="kpi.title"
                :value="kpi.value"
                :unit="kpi.unit"
                :trend="kpi.trend ?? null"
                :status="kpi.status"
                :status-label="kpi.statusLabel"
            />
        </div>

        <!-- Deployment Observatory -->
        <DashboardSection
            title="Deployment Observatory"
            description="Every connected site's effective status per canonical module — governance policy intersected with runtime telemetry."
        >
            <ModuleSiteMatrix :modules="modules" :sites="filteredSites" selected-module="all" />
        </DashboardSection>

        <!-- Governance Exceptions -->
        <DashboardSection
            title="Governance Exceptions"
            description="Sites where a module is running in runtime telemetry while governance policy has it disabled."
        >
            <UiCard variant="glass">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-card-title">Governance Exceptions</h3>
                    <UiBadge :variant="policyViolationDetails.length ? 'warning' : 'success'">
                        {{ policyViolationDetails.length }} Policy Violation{{ policyViolationDetails.length === 1 ? '' : 's' }}
                    </UiBadge>
                </div>
                <ul v-if="policyViolationDetails.length" class="mt-4 space-y-3">
                    <li
                        v-for="v in policyViolationDetails"
                        :key="v.id"
                        class="rounded-lg border border-border/60 bg-background p-3.5"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-text-primary">{{ v.code }}</span>
                            <span class="text-text-muted">·</span>
                            <span class="text-sm text-text-secondary">{{ v.moduleLabel }}</span>
                        </div>
                        <p class="text-caption mt-1">Running in runtime telemetry but disabled by governance.</p>
                    </li>
                </ul>
                <p v-else class="mt-4 text-body">No governance exceptions currently detected.</p>
            </UiCard>
        </DashboardSection>

        <!-- Deployment Semantics -->
        <DashboardSection
            title="Deployment Semantics"
            description="How governance policy and runtime telemetry combine into effective deployment status."
        >
            <UiCard>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 text-xs">
                    <div v-for="item in deploymentSemantics" :key="item.label" class="rounded-lg border border-border/60 bg-background p-3.5">
                        <p class="font-semibold uppercase tracking-wider text-primary text-[11px]">{{ item.label }}</p>
                        <p class="text-body text-xs mt-1">{{ item.desc }}</p>
                    </div>
                </div>
            </UiCard>
        </DashboardSection>

        <!-- Release & Version Deployment — existing tracking, unchanged behavior -->
        <DashboardSection
            title="Release & Version Deployment"
            description="Application version distribution and release deployment health across all connected sites."
        >
            <div class="space-y-6">
                <DeploymentSummary :summary="versionSummary" />

                <VersionOverview :distribution="versionDistribution" :total-sites="versionSummary.totalSites" />

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
        </DashboardSection>
    </div>

    <UiCard v-else>
        <h2 class="text-page-title">Access Restricted</h2>
        <p class="text-body mt-2 max-w-2xl">
            You don't have permission to view this page. Contact your administrator if you believe
            this is a mistake.
        </p>
        <router-link to="/" class="mt-4 inline-block">
            <UiButton variant="secondary">← Back to Home</UiButton>
        </router-link>
    </UiCard>
</template>
