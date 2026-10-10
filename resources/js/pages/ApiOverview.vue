<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useApiIntegration } from '../composables/useApiIntegration';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import ApiIntegrationTabs from '../components/api-integration/ApiIntegrationTabs.vue';

const auth = useAuthStore();
const { summary, statusLabel, statusVariant } = useApiIntegration();

// Executive API Summary — 4 cards, all derived from useApiIntegration()'s
// summary. Nothing here is a fabricated real-world value; every count
// reflects the current (currently empty/structural) foundation data.
const executiveApiSummary = computed(() => [
    {
        id: 'site-apis',
        title: 'Site APIs',
        value: summary.value.siteApis,
        unit: 'configured',
        status: summary.value.siteApis > 0 ? 'success' : 'neutral',
        statusLabel: summary.value.siteApis > 0 ? 'Configured' : 'Not Configured',
    },
    {
        id: 'control-center-apis',
        title: 'Control Center APIs',
        value: summary.value.controlCenterApis,
        unit: 'catalogued',
        status: 'info',
        statusLabel: 'Pending Publication',
    },
    {
        id: 'configured-integrations',
        title: 'Configured Integrations',
        value: summary.value.configuredIntegrations,
        unit: 'integrations',
        status: summary.value.configuredIntegrations > 0 ? 'success' : 'neutral',
        statusLabel: summary.value.configuredIntegrations > 0 ? 'Active Foundation' : 'None Configured',
    },
    {
        id: 'pending-configuration',
        title: 'Pending Configuration',
        value: summary.value.pendingConfiguration,
        unit: 'items',
        status: 'info',
        statusLabel: 'Awaiting Setup',
    },
]);

// Integration Architecture — a purely conceptual pipeline. "Control
// Center" is the only stage marked ready, because it is the only thing
// that genuinely exists today; every other stage is an honest "not
// built yet" placeholder, not a simulated connection.
const architectureStages = [
    { label: 'Site Application', status: 'Not Connected', ready: false },
    { label: 'Site API', status: 'Not Configured', ready: false },
    { label: 'Control Center', status: 'Foundation Ready', ready: true },
    { label: 'Control Center API', status: 'Not Published', ready: false },
    { label: 'Connected Site Applications', status: 'Not Connected', ready: false },
];

const integrationStates = [
    { key: 'not_configured', desc: 'No connection has been configured for this integration point yet.' },
    { key: 'pending', desc: 'Configuration exists; the integration has not been activated or tested yet.' },
    { key: 'configured', desc: 'The integration is configured and ready to be tested.' },
    { key: 'not_tested', desc: 'A configuration exists, but connectivity has not been verified yet.' },
];
</script>

<template>
    <!-- Page-level capability guard, same convention as every other
         gated page. API Integration reuses site_configuration_manage —
         the closest existing capability for a system/infrastructure
         configuration concern — no new capability was created. -->
    <div v-if="auth.can('site_configuration_manage')" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">API Integration</h1>
                <p class="text-body mt-2 max-w-2xl">
                    API connectivity and integration management for the OneClick ecosystem.
                </p>
            </div>
            <UiBadge variant="neutral" class="px-3 py-1 text-xs">Integration Foundation Pending</UiBadge>
        </div>

        <ApiIntegrationTabs />

        <!-- Executive API Summary -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <UiKpiCard
                v-for="kpi in executiveApiSummary"
                :key="kpi.id"
                :title="kpi.title"
                :value="kpi.value"
                :unit="kpi.unit"
                :status="kpi.status"
                :status-label="kpi.statusLabel"
            />
        </div>

        <!-- Integration Architecture -->
        <DashboardSection
            title="Integration Architecture"
            description="Conceptual relationship between site applications and the Control Center — no live communication occurs yet."
        >
            <UiCard variant="glass">
                <div class="flex flex-col items-center gap-2">
                    <template v-for="(stage, i) in architectureStages" :key="stage.label">
                        <div
                            class="w-full max-w-sm rounded-lg border p-3 text-center text-sm"
                            :class="stage.ready ? 'border-primary/40 bg-primary-soft' : 'border-border/60 bg-background'"
                        >
                            <p class="font-semibold" :class="stage.ready ? 'text-primary' : 'text-text-muted'">{{ stage.label }}</p>
                            <p class="text-caption mt-1">{{ stage.status }}</p>
                        </div>
                        <span v-if="i < architectureStages.length - 1" class="text-text-muted">↓</span>
                    </template>
                </div>
            </UiCard>
        </DashboardSection>

        <!-- Integration Status -->
        <DashboardSection
            title="Integration Status"
            description="The four states any API connection in this ecosystem can be in — definitions only, not a live status feed."
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="state in integrationStates"
                    :key="state.key"
                    class="rounded-lg border border-border/60 bg-background p-3.5"
                >
                    <UiBadge :variant="statusVariant[state.key]">{{ statusLabel[state.key] }}</UiBadge>
                    <p class="text-caption mt-2">{{ state.desc }}</p>
                </div>
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
