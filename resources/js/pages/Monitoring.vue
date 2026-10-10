<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useHomeDashboard } from '../composables/useHomeDashboard';
import { useModulesOverview } from '../composables/useModulesOverview';
import { useEffectiveAccess } from '../composables/useEffectiveAccess';
import { sites as allSites } from '../data/mock/sites';

import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import SiteHealthOverview from '../components/dashboard/SiteHealthOverview.vue';
import ModuleOverview from '../components/dashboard/ModuleOverview.vue';
import RecentActivity from '../components/dashboard/RecentActivity.vue';
import AppIcon from '../components/layout/AppIcon.vue';

const auth = useAuthStore();
const { canAccessSite } = useEffectiveAccess();
const { siteHealth: baseSiteHealth, recentActivity } = useHomeDashboard();
const { modules, filteredSites, moduleCatalog } = useModulesOverview();

// Enrich site health data with detailed site metadata from sites.js
const monitoredSites = computed(() => {
    return allSites
        .filter((site) => canAccessSite(site.code))
        .map((site) => {
            const healthEntry = baseSiteHealth.value.find((h) => h.code === site.code);
            return {
                ...site,
                healthStatus: healthEntry?.status ?? (site.status === 'online' ? 'success' : site.status),
            };
        });
});

// Executive System Health KPI calculations derived directly from monitoredSites
const totalSitesCount = computed(() => monitoredSites.value.length);
const onlineSitesCount = computed(
    () => monitoredSites.value.filter((s) => s.status === 'online' || s.healthStatus === 'success').length
);
const warningSitesCount = computed(
    () => monitoredSites.value.filter((s) => s.status === 'warning' || s.healthStatus === 'warning').length
);
const offlineSitesCount = computed(
    () => monitoredSites.value.filter((s) => s.status === 'offline' || s.healthStatus === 'danger').length
);

const avgSystemHealth = computed(() => {
    if (!monitoredSites.value.length) return 0;
    const total = monitoredSites.value.reduce((sum, s) => sum + (s.systemHealth ?? 0), 0);
    return (total / monitoredSites.value.length).toFixed(1);
});

const summaryKpis = computed(() => [
    {
        id: 'total-sites',
        title: 'Total Sites',
        value: totalSitesCount.value,
        unit: 'sites',
        status: 'info',
        statusLabel: 'Monitored Scope',
    },
    {
        id: 'online-sites',
        title: 'Online Sites',
        value: onlineSitesCount.value,
        unit: 'sites',
        status: 'success',
        statusLabel: `${onlineSitesCount.value} Sites Healthy`,
    },
    {
        id: 'warning-sites',
        title: 'Warning Sites',
        value: warningSitesCount.value,
        unit: 'sites',
        status: 'warning',
        statusLabel: `${warningSitesCount.value} Sites Degraded`,
    },
    {
        id: 'offline-sites',
        title: 'Offline Sites',
        value: offlineSitesCount.value,
        unit: 'sites',
        status: offlineSitesCount.value > 0 ? 'danger' : 'success',
        statusLabel: offlineSitesCount.value > 0 ? `${offlineSitesCount.value} Site Unreachable` : 'All Sites Reachable',
    },
    {
        id: 'avg-health',
        title: 'Average System Health',
        value: avgSystemHealth.value,
        unit: '%',
        status: avgSystemHealth.value >= 95 ? 'success' : 'warning',
        statusLabel: 'Overall Health',
    },
]);

// Site-level attention items
const offlineSites = computed(() => monitoredSites.value.filter((s) => s.status === 'offline' || s.healthStatus === 'danger'));
const warningSites = computed(() => monitoredSites.value.filter((s) => s.status === 'warning' || s.healthStatus === 'warning'));

// Policy-violation detail, derived from the resolver's own per-site
// effectiveModules map — the same derivation Dashboard.vue's Operational
// Attention feed uses (filteredSites/moduleCatalog from useModulesOverview),
// so the two pages can never disagree about which site+module pairs are
// in violation.
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

// Unified Operational Attention feed — offline sites first (most
// severe), then policy violations, then warning sites. No timestamps
// or invented events; every item traces to monitoredSites or the
// resolver's own effectiveModules output.
const attentionFeed = computed(() => [
    ...offlineSites.value.map((s) => ({
        id: `site-${s.code}`,
        code: s.code,
        message: 'Site Offline',
        detail: s.name,
        icon: 'sites',
        variant: 'danger',
        badgeLabel: 'Offline',
    })),
    ...policyViolationDetails.value.map((v) => ({
        id: v.id,
        code: v.code,
        message: `${v.moduleLabel} — Policy Violation`,
        detail: 'Deployed but disabled by governance policy',
        icon: 'audit',
        variant: 'warning',
        badgeLabel: 'Policy Violation',
    })),
    ...warningSites.value.map((s) => ({
        id: `warn-${s.code}`,
        code: s.code,
        message: 'Site Warning',
        detail: `${s.systemHealth}% system health`,
        icon: 'sites',
        variant: 'warning',
        badgeLabel: 'Warning',
    })),
]);

const attentionIconClass = {
    warning: 'bg-warning-soft text-warning',
    danger: 'bg-danger-soft text-danger',
};

// Module-level runtime figures — pendingDeploymentSites/policyViolationSites
// are the resolver's own named fields (not a re-derived "effectiveActiveSites
// === 0" heuristic, which is what this page used previously and could
// disagree with Dashboard.vue's equivalent figure for the same data).
const violationModules = computed(() => modules.value.filter((m) => m.policyViolationSites > 0));
const pendingModules = computed(() => modules.value.filter((m) => m.pendingDeploymentSites > 0));
const totalPendingDeployments = computed(() => pendingModules.value.reduce((sum, m) => sum + m.pendingDeploymentSites, 0));
</script>

<template>
    <div v-if="auth.can('monitoring')" class="space-y-8">
        <!-- Section A: Executive Monitoring Header -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Monitoring</h1>
                <p class="text-body mt-2 max-w-2xl">
                    System health, deployment telemetry, and operational attention across all connected PPA sites.
                </p>
            </div>
            <UiBadge variant="info" class="px-3 py-1 text-xs">
                System Telemetry Active (Local Feed)
            </UiBadge>
        </div>

        <!-- Section B: Operational Attention — highest visual priority -->
        <DashboardSection
            title="Operational Attention"
            description="What needs review right now — site health issues and governance exceptions, most severe first."
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
                    No operational attention items — every connected site and module is nominal.
                </p>
            </UiCard>
        </DashboardSection>

        <!-- Section C: System Health Command Strip -->
        <DashboardSection
            title="System Health Command Strip"
            description="High-level site connectivity summary across the monitored ecosystem."
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <UiKpiCard
                    v-for="kpi in summaryKpis"
                    :key="kpi.id"
                    :title="kpi.title"
                    :value="kpi.value"
                    :unit="kpi.unit"
                    :status="kpi.status"
                    :status-label="kpi.statusLabel"
                />
            </div>
        </DashboardSection>

        <!-- Section D: Site Health Monitoring -->
        <DashboardSection
            title="Site Health Monitoring"
            description="Operational telemetry status across all monitored site environments."
        >
            <SiteHealthOverview :sites="baseSiteHealth" />
        </DashboardSection>

        <!-- Section E: Module Runtime Health -->
        <DashboardSection
            title="Module Runtime Health"
            description="Configured, Raw Active, Effective Active, and Pending Deployment — governance policy vs runtime telemetry, per module."
        >
            <ModuleOverview :modules="modules" />
        </DashboardSection>

        <!-- Section F: Governance Exceptions -->
        <DashboardSection
            title="Governance Exceptions"
            description="Sites where a module is running in telemetry while governance policy has it disabled."
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
                        <p class="text-caption mt-1">Running in telemetry while disabled by governance policy.</p>
                    </li>
                </ul>
                <p v-else class="mt-4 text-body">No governance exceptions currently detected.</p>

                <p v-if="pendingModules.length" class="mt-4 border-t border-border/60 pt-3 text-caption">
                    {{ totalPendingDeployments }} site-module pair{{ totalPendingDeployments === 1 ? '' : 's' }} configured by
                    governance policy but still pending runtime deployment.
                </p>
            </UiCard>
        </DashboardSection>

        <!-- Section G: Monitoring Semantic Contract -->
        <DashboardSection
            title="Monitoring Data & Governance Contract"
            description="Semantic definitions distinguishing operational health, runtime deployment, and governance entitlements."
        >
            <UiCard>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 text-xs">
                    <div class="rounded-lg border border-border/60 bg-background p-3.5">
                        <p class="font-semibold uppercase tracking-wider text-primary text-[11px]">System Health</p>
                        <p class="text-body text-xs mt-1">Operational site connectivity and infrastructure status reported by site monitoring agents.</p>
                    </div>
                    <div class="rounded-lg border border-border/60 bg-background p-3.5">
                        <p class="font-semibold uppercase tracking-wider text-primary text-[11px]">Runtime Deployment</p>
                        <p class="text-body text-xs mt-1">Raw telemetry indicating whether module code is currently deployed and running on a site.</p>
                    </div>
                    <div class="rounded-lg border border-border/60 bg-background p-3.5">
                        <p class="font-semibold uppercase tracking-wider text-primary text-[11px]">Governance Policy</p>
                        <p class="text-body text-xs mt-1">Baseline entitlement policy configured per site in Site Configuration.</p>
                    </div>
                    <div class="rounded-lg border border-border/60 bg-background p-3.5">
                        <p class="font-semibold uppercase tracking-wider text-primary text-[11px]">Effective Status</p>
                        <p class="text-body text-xs mt-1">Intersection of Governance Policy and Runtime Deployment (Active, Pending, Disabled, Policy Violation).</p>
                    </div>
                </div>
            </UiCard>
        </DashboardSection>

        <!-- Section H: Recent System Activity -->
        <DashboardSection
            title="Recent System Activity"
            description="Latest system events and operational updates logged across the ecosystem."
        >
            <RecentActivity :activities="recentActivity" />
        </DashboardSection>
    </div>

    <UiCard v-else>
        <h2 class="text-page-title">Access Restricted</h2>
        <p class="text-body mt-2 max-w-2xl">
            You don't have permission to view this page. Contact your administrator if you believe this is a mistake.
        </p>
        <router-link to="/" class="mt-4 inline-block">
            <UiButton variant="secondary">← Back to Home</UiButton>
        </router-link>
    </UiCard>
</template>
