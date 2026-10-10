<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import UiCard from '../components/ui/UiCard.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import ModuleCard from '../components/modules/ModuleCard.vue';
import ModuleSiteMatrix from '../components/modules/ModuleSiteMatrix.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import { useModulesOverview } from '../composables/useModulesOverview';

const auth = useAuthStore();

const { search, selectedModule, moduleCatalog, modules, visibleModules, summary, filteredSites } =
    useModulesOverview();

const fieldClasses =
    'rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

// Ecosystem-wide aggregates for the Executive Module Summary — simple
// sums over useModulesOverview()'s own per-module output, nothing
// re-derived or fabricated. totalPossible mirrors the same
// (modules x sites) denominator the composable itself uses for
// overallAdoption.
const totalPossible = computed(() => summary.value.totalModules * summary.value.totalSites);
const totalConfigured = computed(() => modules.value.reduce((sum, mod) => sum + mod.configuredSites, 0));
const totalPending = computed(() => modules.value.reduce((sum, mod) => sum + mod.pendingDeploymentSites, 0));

const executiveModuleSummary = computed(() => [
    {
        id: 'canonical-modules',
        title: 'Canonical Modules',
        value: summary.value.totalModules,
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
        value: `${summary.value.effectiveActiveDeployments}/${totalPossible.value}`,
        unit: 'site-modules',
        trend: { direction: 'flat', label: `${summary.value.rawActiveDeployments} raw active` },
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
        value: summary.value.policyViolations,
        unit: 'site-modules',
        status: summary.value.policyViolations > 0 ? 'warning' : 'success',
        statusLabel: summary.value.policyViolations > 0 ? 'Requires attention' : 'No exceptions',
    },
]);

// Governance Exceptions — derived from the resolver's own per-site
// effectiveModules map (never a second status computation). Same
// derivation used on Dashboard.vue and Monitoring.vue, so all three
// pages can never disagree about which site+module pairs are in
// violation.
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

const semanticContract = [
    { label: 'Configured', desc: 'Governance policy permits this module for the site.' },
    { label: 'Raw Active', desc: 'Runtime telemetry reports the module as deployed.' },
    { label: 'Effective Active', desc: 'Governance permits the module AND runtime reports it deployed.' },
    { label: 'Policy Violation', desc: 'Runtime reports deployment while governance policy prohibits it.' },
    { label: 'Pending', desc: 'Governance permits the module, but runtime deployment is not yet active.' },
];
</script>

<template>
    <!-- Page-level capability guard: nav already hides this link when
         module_view is false, but direct URL navigation bypasses nav
         hiding — this stops the page itself from rendering. Same
         auth.can() mechanism as every other capability check in the
         app; no second authorization system. -->
    <div v-if="auth.can('module_view')" class="space-y-8">
        <!-- Executive Module Header -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Modules</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Module deployment observatory — canonical module catalogue and governance-aware deployment
                    status across every connected PPA site.
                </p>
            </div>
            <UiBadge variant="info" class="px-3 py-1 text-xs">System Telemetry Active (Local Feed)</UiBadge>
        </div>

        <!-- Executive Module Summary -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <UiKpiCard
                v-for="kpi in executiveModuleSummary"
                :key="kpi.id"
                :title="kpi.title"
                :value="kpi.value"
                :unit="kpi.unit"
                :trend="kpi.trend ?? null"
                :status="kpi.status"
                :status-label="kpi.statusLabel"
            />
        </div>

        <!-- Governance Exceptions -->
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
                        <p class="text-caption mt-1">Running in runtime telemetry while disabled by governance policy.</p>
                    </li>
                </ul>
                <p v-else class="mt-4 text-body">No governance exceptions currently detected.</p>
            </UiCard>
        </DashboardSection>

        <!-- Filter Toolbar -->
        <UiCard padding="sm">
            <div class="flex flex-wrap items-center gap-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search sites by code, name, or location…"
                    :class="fieldClasses"
                    class="min-w-[220px] flex-1"
                />

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            selectedModule === 'all'
                                ? 'bg-primary text-white'
                                : 'border border-border bg-surface text-text-secondary hover:bg-background'
                        "
                        @click="selectedModule = 'all'"
                    >
                        All
                    </button>
                    <button
                        v-for="mod in moduleCatalog"
                        :key="mod.key"
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            selectedModule === mod.key
                                ? 'bg-primary text-white'
                                : 'border border-border bg-surface text-text-secondary hover:bg-background'
                        "
                        @click="selectedModule = mod.key"
                    >
                        {{ mod.label }}
                    </button>
                </div>
            </div>
        </UiCard>

        <!-- Module Deployment Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <ModuleCard
                v-for="mod in visibleModules"
                :key="mod.key"
                :module="mod"
                :highlighted="selectedModule === mod.key"
            />
        </div>

        <!-- Site x Module Matrix — the primary observability tool -->
        <DashboardSection
            title="Site × Module Deployment Matrix"
            description="Every connected site's effective status per canonical module — governance policy intersected with runtime telemetry."
        >
            <ModuleSiteMatrix :modules="modules" :sites="filteredSites" :selected-module="selectedModule" />
        </DashboardSection>

        <!-- Data Semantics -->
        <DashboardSection
            title="Deployment Data Semantics"
            description="How governance policy and runtime telemetry combine into effective status."
        >
            <UiCard>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 text-xs">
                    <div v-for="item in semanticContract" :key="item.label" class="rounded-lg border border-border/60 bg-background p-3.5">
                        <p class="font-semibold uppercase tracking-wider text-primary text-[11px]">{{ item.label }}</p>
                        <p class="text-body text-xs mt-1">{{ item.desc }}</p>
                    </div>
                </div>
            </UiCard>
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
