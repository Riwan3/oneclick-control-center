<script setup>
import { computed } from 'vue';
import { useHomeDashboard } from '../composables/useHomeDashboard';
import { useModulesOverview } from '../composables/useModulesOverview';
import { useKpiCatalogue } from '../composables/useKpiCatalogue';

import DashboardSection from '../components/dashboard/DashboardSection.vue';
import SiteHealthOverview from '../components/dashboard/SiteHealthOverview.vue';
import ModuleOverview from '../components/dashboard/ModuleOverview.vue';
import RecentActivity from '../components/dashboard/RecentActivity.vue';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import AppIcon from '../components/layout/AppIcon.vue';

const { executiveKpis, siteHealth, recentActivity } = useHomeDashboard();
const { modules, summary, filteredSites, moduleCatalog } = useModulesOverview();
const { summary: kpiSummary } = useKpiCatalogue();

const today = new Date().toLocaleDateString('en-GB', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

// EXECUTIVE HEADER / OVERVIEW: every count below is derived from the
// same siteHealth / useModulesOverview / useKpiCatalogue sources the
// rest of the page (and Monitoring.vue, Modules.vue, Kpi.vue) already
// use — nothing here is a separately invented number.
const totalSitesCount = computed(() => siteHealth.value.length);
const onlineSitesCount = computed(() => siteHealth.value.filter((s) => s.status === 'success').length);
const warningSitesCount = computed(() => siteHealth.value.filter((s) => s.status === 'warning').length);
const offlineSitesCount = computed(() => siteHealth.value.filter((s) => s.status === 'danger').length);
const connectedSitesCount = computed(() => executiveKpis.value.find((k) => k.id === 'connected-sites')?.value ?? 0);

// EXECUTIVE KPI STRIP — Average System Health, Online Sites, Effective
// Active Deployments (with Raw Active shown as context, never merged
// into one number), Governance Exceptions, KPI Readiness.
const systemHealthValue = computed(() => executiveKpis.value.find((k) => k.id === 'system-health')?.value ?? null);
const activeUsersToday = computed(() => executiveKpis.value.find((k) => k.id === 'active-users-today')?.value ?? 0);

const executiveSummaryKpis = computed(() => [
    {
        id: 'system-health',
        title: 'Average System Health',
        value: systemHealthValue.value ?? '—',
        unit: systemHealthValue.value === null ? '' : '%',
        status: systemHealthValue.value === null ? 'neutral' : systemHealthValue.value >= 70 ? 'success' : 'warning',
        statusLabel: systemHealthValue.value === null ? 'No site reports health yet' : 'Reported by site servers',
    },
    {
        id: 'registered-users',
        title: 'Registered Users',
        value: executiveKpis.value.find((k) => k.id === 'total-users')?.value ?? 0,
        unit: 'users',
        status: 'info',
        statusLabel: `${activeUsersToday.value} active today`,
    },
    {
        id: 'online-sites',
        title: 'Online Sites',
        value: `${onlineSitesCount.value}/${totalSitesCount.value}`,
        unit: 'sites',
        status: offlineSitesCount.value > 0 ? 'danger' : warningSitesCount.value > 0 ? 'warning' : 'success',
        statusLabel:
            offlineSitesCount.value > 0
                ? `${offlineSitesCount.value} offline`
                : warningSitesCount.value > 0
                  ? `${warningSitesCount.value} warning`
                  : 'All sites online',
    },
    {
        id: 'effective-active',
        title: 'Effective Active Deployments',
        value: summary.value.effectiveActiveDeployments,
        unit: 'deployments',
        trend: { direction: 'flat', label: `${summary.value.rawActiveDeployments} raw active` },
        status: 'success',
        statusLabel: 'Governance-compliant',
    },
    {
        id: 'governance-exceptions',
        title: 'Governance Exceptions',
        value: summary.value.policyViolations,
        unit: 'violations',
        status: summary.value.policyViolations > 0 ? 'warning' : 'success',
        statusLabel: summary.value.policyViolations > 0 ? 'Requires attention' : 'No exceptions',
    },
    {
        id: 'kpi-readiness',
        title: 'KPI Readiness',
        value: kpiSummary.value.connected,
        unit: 'connected KPIs',
        status: kpiSummary.value.connected > 0 ? 'success' : 'neutral',
        statusLabel: kpiSummary.value.connected > 0 ? 'Live telemetry' : 'Telemetry pending',
    },
]);

// Ecosystem-wide pending-deployment count, correctly derived from the
// resolver's own pendingDeploymentSites — not the raw-deployment alias
// (activeSites) the previous version of this page used, which could
// disagree with Monitoring.vue's equivalent figure for the same data.
const pendingModules = computed(() => modules.value.filter((mod) => mod.pendingDeploymentSites > 0));
const totalPendingDeployments = computed(() =>
    pendingModules.value.reduce((sum, mod) => sum + mod.pendingDeploymentSites, 0)
);

// OPERATIONAL ATTENTION FEED — built entirely from data the resolver
// and siteHealth already expose; no timestamps, events, or metrics are
// invented. filteredSites/moduleCatalog come from useModulesOverview()
// (search is empty by default, so filteredSites is the full site list
// with each site's real effectiveModules map from the resolver).
const policyViolationFeed = computed(() => {
    const items = [];
    for (const site of filteredSites.value) {
        for (const mod of moduleCatalog.value) {
            if (site.effectiveModules[mod.key]?.effectiveStatus === 'policy_violation') {
                items.push({
                    id: `${site.code}-${mod.key}`,
                    code: site.code,
                    message: `${mod.label} — Policy Violation`,
                    detail: 'Deployed but disabled by governance policy',
                    icon: 'audit',
                    variant: 'warning',
                    badgeLabel: 'Policy Violation',
                });
            }
        }
    }
    return items;
});

const siteHealthFeed = computed(() =>
    siteHealth.value
        .filter((s) => s.status === 'warning' || s.status === 'danger')
        .map((s) => ({
            id: `site-${s.code}`,
            code: s.code,
            message: s.status === 'danger' ? 'Site Offline' : 'Site Warning',
            detail: null,
            icon: 'sites',
            variant: s.status === 'danger' ? 'danger' : 'warning',
            badgeLabel: s.status === 'danger' ? 'Offline' : 'Warning',
        }))
);

const attentionFeed = computed(() => [...policyViolationFeed.value, ...siteHealthFeed.value]);

const attentionIconClass = {
    warning: 'bg-warning-soft text-warning',
    danger: 'bg-danger-soft text-danger',
};
</script>

<template>
    <div class="space-y-8">
        <!-- EXECUTIVE HEADER -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-label">{{ today }}</p>
                <h1 class="text-page-title mt-1">Executive Control Center Overview</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Centralized governance, baseline policy release, and operational site deployment status across
                    connected PPA sites.
                </p>
            </div>
            <UiBadge variant="info" class="px-3 py-1 text-xs">Live from connected sites</UiBadge>
        </div>

        <!-- EXECUTIVE KPI STRIP -->
        <DashboardSection
            title="Executive KPIs"
            description="Ecosystem-wide status across all connected sites."
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <UiKpiCard
                    v-for="kpi in executiveSummaryKpis"
                    :key="kpi.id"
                    :title="kpi.title"
                    :value="kpi.value"
                    :unit="kpi.unit"
                    :trend="kpi.trend ?? null"
                    :status="kpi.status"
                    :status-label="kpi.statusLabel"
                />
            </div>
        </DashboardSection>

        <!-- OPERATIONAL OVERVIEW — two-column executive summary -->
        <DashboardSection
            title="Operational Overview"
            description="Site connectivity alongside governance-compliant deployment, side by side."
        >
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <UiCard variant="glass">
                    <h3 class="text-card-title">System Health</h3>
                    <p class="text-caption mt-1">Connectivity across all connected sites.</p>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <p class="text-kpi-label">Total</p>
                            <p class="text-kpi-value mt-1 text-2xl">{{ totalSitesCount }}</p>
                        </div>
                        <div>
                            <p class="text-kpi-label">Online</p>
                            <p class="text-kpi-value mt-1 text-2xl text-success">{{ onlineSitesCount }}</p>
                        </div>
                        <div>
                            <p class="text-kpi-label">Warning</p>
                            <p class="text-kpi-value mt-1 text-2xl text-warning">{{ warningSitesCount }}</p>
                        </div>
                        <div>
                            <p class="text-kpi-label">Offline</p>
                            <p class="text-kpi-value mt-1 text-2xl text-danger">{{ offlineSitesCount }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-border/60 pt-3">
                        <span class="text-caption">Connected Sites</span>
                        <span class="text-sm font-semibold text-text-primary">{{ connectedSitesCount }} / {{ totalSitesCount }}</span>
                    </div>
                </UiCard>

                <UiCard variant="glass">
                    <h3 class="text-card-title">Deployment &amp; Governance</h3>
                    <p class="text-caption mt-1">Baseline policy vs runtime deployment, ecosystem-wide.</p>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <p class="text-kpi-label">Effective</p>
                            <p class="text-kpi-value mt-1 text-2xl text-success">{{ summary.effectiveActiveDeployments }}</p>
                        </div>
                        <div>
                            <p class="text-kpi-label">Pending</p>
                            <p class="text-kpi-value mt-1 text-2xl text-text-secondary">{{ totalPendingDeployments }}</p>
                        </div>
                        <div>
                            <p class="text-kpi-label">Violations</p>
                            <p class="text-kpi-value mt-1 text-2xl text-warning">{{ summary.policyViolations }}</p>
                        </div>
                        <div>
                            <p class="text-kpi-label">Adoption</p>
                            <p class="text-kpi-value mt-1 text-2xl">{{ summary.overallAdoption }}%</p>
                        </div>
                    </div>
                </UiCard>
            </div>
        </DashboardSection>

        <!-- OPERATIONAL ATTENTION -->
        <DashboardSection
            title="Operational Attention"
            description="Governance exceptions and site health issues that need review — nothing here is inferred beyond the resolver's own output."
        >
            <UiCard padding="none">
                <ul v-if="attentionFeed.length" class="divide-y divide-border">
                    <li v-for="item in attentionFeed" :key="item.id" class="flex items-center gap-3 px-4 py-3">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                            :class="attentionIconClass[item.variant]"
                        >
                            <AppIcon :name="item.icon" class="h-4 w-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-text-primary">{{ item.code }} — {{ item.message }}</p>
                            <p v-if="item.detail" class="text-caption mt-0.5">{{ item.detail }}</p>
                        </div>
                        <UiBadge :variant="item.variant">{{ item.badgeLabel }}</UiBadge>
                    </li>
                </ul>
                <p v-else class="py-8 text-center text-body">
                    No governance exceptions or site health issues — ecosystem nominal.
                </p>
            </UiCard>
        </DashboardSection>

        <!-- Detailed drill-down: per-site health -->
        <DashboardSection
            title="Site Health Detail"
            description="Operational status for every connected site."
        >
            <SiteHealthOverview :sites="siteHealth" />
        </DashboardSection>

        <!-- Detailed drill-down: per-module deployment -->
        <DashboardSection
            title="Module Deployment & Adoption Detail"
            description="Dual-metric tracking of baseline policy configuration vs active runtime deployment."
        >
            <ModuleOverview :modules="modules" />
        </DashboardSection>

        <!-- Recent Activity -->
        <DashboardSection
            title="Recent System Activity"
            description="Latest events and release updates across the OneClick ecosystem."
        >
            <RecentActivity :activities="recentActivity" />
        </DashboardSection>
    </div>
</template>
