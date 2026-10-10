<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useKpiCatalogue } from '../composables/useKpiCatalogue';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';

const auth = useAuthStore();
const { catalogue, moduleDomains, summary, readinessLabel, readinessVariant } = useKpiCatalogue();

// KPI Telemetry Readiness — explains the 4 canonical readiness states
// using only the existing KPI_READINESS_LABEL/VARIANT (via readinessLabel/
// readinessVariant, already exposed by useKpiCatalogue()). No new
// readiness enum; these descriptions are definitional copy, not data.
const readinessStates = [
    { key: 'connected', desc: 'The KPI is actively receiving operational telemetry.' },
    { key: 'telemetry_pending', desc: 'The KPI is defined; its telemetry source is not yet connected.' },
    { key: 'not_configured', desc: 'No KPI has been defined for this scope yet.' },
    { key: 'unavailable', desc: 'A telemetry source exists but is not currently reachable.' },
];

// Telemetry Integration Boundary — where the Control Center currently
// stands in the KPI pipeline. Purely descriptive (stage labels/status
// text), not a data source — no numeric values are involved.
const pipelineStages = [
    { label: 'KPI Definition', status: 'Catalogue Ready', ready: true },
    { label: 'KPI Data Source', status: 'Pending', ready: false },
    { label: 'Operational Telemetry', status: 'Pending', ready: false },
    { label: 'KPI Calculation', status: 'Pending', ready: false },
    { label: 'Performance Result', status: 'Pending', ready: false },
];

const summaryKpis = computed(() => [
    {
        id: 'catalogue',
        title: 'KPI Catalogue',
        value: summary.value.catalogueTotal,
        unit: 'definitions',
        status: 'neutral',
        statusLabel: 'Structural Registry',
    },
    {
        id: 'connected',
        title: 'Connected KPIs',
        value: summary.value.connected,
        unit: 'KPIs',
        status: summary.value.connected > 0 ? 'success' : 'neutral',
        statusLabel: summary.value.connected > 0 ? 'Live Telemetry' : 'None Connected',
    },
    {
        id: 'telemetry-pending',
        title: 'Telemetry Pending',
        value: summary.value.telemetryPending,
        unit: 'KPIs',
        status: readinessVariant.telemetry_pending,
        statusLabel: 'Awaiting Integration',
    },
    {
        id: 'sites',
        title: 'Configured Sites',
        value: summary.value.configuredSites,
        unit: 'sites',
        status: 'success',
        statusLabel: 'Site Scope',
    },
    {
        id: 'modules',
        title: 'Canonical Modules',
        value: summary.value.canonicalModules,
        unit: 'modules',
        status: 'info',
        statusLabel: 'Registered Domains',
    },
]);

const dataContractFields = [
    { label: 'Definition', desc: 'Standardized operational metric formulation' },
    { label: 'Formula', desc: 'Deterministic calculation logic' },
    { label: 'Unit', desc: 'Standard unit of measure (%, hours, count)' },
    { label: 'Target', desc: 'Baseline benchmark & threshold targets' },
    { label: 'Period / Frequency', desc: 'Execution frequency (Real-time, Daily, Monthly)' },
    { label: 'Data Source', desc: 'Operational telemetry or API endpoint feed' },
    { label: 'Site Scope', desc: 'Site-level or ecosystem-wide application' },
    { label: 'Owner', desc: 'Domain governance lead' },
    { label: 'Status', desc: 'Operational readiness state' },
];
</script>

<template>
    <!-- Page-level capability guard: nav already hides this link when
         kpi is false, but direct URL navigation bypasses nav hiding —
         this stops the page itself from rendering. Same auth.can()
         mechanism as every other capability check in the app; no
         second authorization system. -->
    <div v-if="auth.can('kpi')" class="space-y-8">
        <!-- Section A: Header -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">KPI</h1>
                <p class="text-body mt-2 max-w-2xl">
                    KPI catalogue and operational telemetry readiness.
                </p>
            </div>
            <UiBadge :variant="readinessVariant.telemetry_pending" class="px-3 py-1 text-xs">
                KPI Telemetry Pending
            </UiBadge>
        </div>

        <!-- Section B: Readiness Summary KPIs -->
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

        <!-- Section B2: KPI Telemetry Readiness -->
        <DashboardSection
            title="KPI Telemetry Readiness"
            description="KPI definition and KPI telemetry are two distinct layers — a KPI can be catalogued long before its data source is connected."
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="state in readinessStates"
                    :key="state.key"
                    class="rounded-lg border border-border/60 bg-background p-3.5"
                >
                    <UiBadge :variant="readinessVariant[state.key]">{{ readinessLabel[state.key] }}</UiBadge>
                    <p class="text-caption mt-2">{{ state.desc }}</p>
                </div>
            </div>
        </DashboardSection>

        <!-- Section B3: Telemetry Integration Boundary -->
        <DashboardSection
            title="Telemetry Integration Boundary"
            description="Where OneClick Control Center currently stands in the KPI data pipeline."
        >
            <UiCard variant="glass">
                <div class="flex flex-wrap items-center gap-2">
                    <template v-for="(stage, i) in pipelineStages" :key="stage.label">
                        <div
                            class="rounded-lg border p-3 text-xs"
                            :class="stage.ready ? 'border-primary/40 bg-primary-soft' : 'border-border/60 bg-background'"
                        >
                            <p class="font-semibold" :class="stage.ready ? 'text-primary' : 'text-text-muted'">{{ stage.label }}</p>
                            <p class="text-caption mt-1">{{ stage.status }}</p>
                        </div>
                        <span v-if="i < pipelineStages.length - 1" class="text-text-muted">→</span>
                    </template>
                </div>
                <div class="mt-4 border-t border-border/60 pt-3 text-sm">
                    <p class="font-medium text-text-primary">Current Stage: KPI Definition / Catalogue Ready</p>
                    <p class="text-caption mt-1">Operational Telemetry Integration = Pending</p>
                </div>
            </UiCard>
        </DashboardSection>

        <!-- Section C: KPI Catalogue -->
        <DashboardSection
            title="KPI Catalogue"
            description="Registry of enterprise key performance indicator definitions and their readiness state — not their performance."
        >
            <UiCard padding="none">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="border-b border-border bg-neutral-soft/50 text-xs font-semibold text-text-muted uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">KPI</th>
                                <th class="px-4 py-3">Domain / Module</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Target</th>
                                <th class="px-4 py-3">Frequency</th>
                                <th class="px-4 py-3">Data Source</th>
                                <th class="px-4 py-3">Site Scope</th>
                                <th class="px-4 py-3">Owner</th>
                                <th class="px-4 py-3 text-right">Readiness</th>
                            </tr>
                        </thead>
                        <tbody v-if="catalogue.length" class="divide-y divide-border">
                            <tr v-for="kpi in catalogue" :key="kpi.key">
                                <td class="px-4 py-3 font-medium text-text-primary">{{ kpi.name }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ kpi.domain }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ kpi.unit }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ kpi.target }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ kpi.frequency }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ kpi.dataSource }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ kpi.siteScope }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ kpi.owner }}</td>
                                <td class="px-4 py-3 text-right">
                                    <UiBadge :variant="readinessVariant[kpi.readiness]">
                                        {{ readinessLabel[kpi.readiness] }}
                                    </UiBadge>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="9" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="rounded-full bg-primary/10 p-3 text-primary mb-3">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </div>
                                        <p class="font-semibold text-text-primary text-sm">
                                            No KPI telemetry definitions are connected yet.
                                        </p>
                                        <p class="text-caption mt-1 max-w-md">
                                            The KPI catalogue foundation and data contract are ready. Operational KPI
                                            performance will appear here once the corresponding telemetry source is
                                            connected — this catalogue never shows fabricated values in the meantime.
                                        </p>
                                        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                                            <UiBadge variant="info">Foundation Ready</UiBadge>
                                            <UiBadge :variant="readinessVariant.telemetry_pending">Telemetry Pending</UiBadge>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </UiCard>
        </DashboardSection>

        <!-- Section D: Data Contract Foundation -->
        <DashboardSection
            title="KPI Data Contract Specification"
            description="Required governance metadata structure for upcoming telemetry integration."
        >
            <UiCard>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="item in dataContractFields"
                        :key="item.label"
                        :class="[
                            'rounded-lg border border-border/60 bg-background p-3.5',
                            'hover:border-primary/40 transition-colors'
                        ]"
                    >
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ item.label }}</p>
                        <p class="text-body text-xs mt-1">{{ item.desc }}</p>
                    </div>
                </div>
            </UiCard>
        </DashboardSection>

        <!-- Section E: Module Domain Readiness -->
        <DashboardSection
            title="Module KPI Domains"
            description="Canonical baseline modules registered as potential performance domains."
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <UiCard v-for="mod in moduleDomains" :key="mod.key" class="flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-text-primary text-sm">{{ mod.label }}</span>
                            <UiBadge :variant="readinessVariant[mod.readiness]" class="text-[10px]">
                                {{ readinessLabel[mod.readiness] }}
                            </UiBadge>
                        </div>
                        <p class="text-caption mt-2">{{ mod.description }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-border/40 flex items-center justify-between text-xs text-text-muted">
                        <span>Domain Scope</span>
                        <span class="font-medium text-text-secondary">Ecosystem</span>
                    </div>
                </UiCard>
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
