<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useIntegrationSettings } from '../composables/useIntegrationSettings';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import ApiIntegrationTabs from '../components/api-integration/ApiIntegrationTabs.vue';

const auth = useAuthStore();
const { settings, sites, needsAttention, lastReport, loading, error } = useIntegrationSettings();

// Read-only: these are the rules the Control Center applies, not form fields.
const cards = computed(() => {
    const s = settings.value;
    if (!s) return [];

    return [
        {
            title: 'Control Center endpoint',
            hint: 'Where a OneClick site sends its report.',
            rows: [
                ['Address', s.endpoint.url],
                ['Report endpoint', `${s.endpoint.method} ${s.endpoint.report_path}`],
                ['Rate limit', `${s.endpoint.rate_limit_per_minute} reports per minute, per source address`],
                ['Maximum report size', `${s.endpoint.max_payload_kb} KB`],
            ],
        },
        {
            title: 'Authentication',
            hint: 'Issue or replace a site\'s token under Site API.',
            rows: [
                ['Method', s.authentication.method],
                ['Site identified by', `${s.authentication.site_header} header`],
                ['Token storage', s.authentication.token_storage],
            ],
        },
        {
            title: 'Timing',
            hint: 'Set in the server configuration, not from this page.',
            rows: [
                ['Pull timeout', `${s.timing.pull_timeout_seconds} seconds`],
                ['Marked Offline after', `${s.timing.offline_after_minutes} minutes without a report`],
            ],
        },
    ];
});

const statusTiles = computed(() => {
    const s = sites.value;
    if (!s) return [];

    return [
        { id: 'registered', title: 'Registered Sites', value: s.registered, unit: 'sites', status: 'neutral', statusLabel: 'In the registry' },
        {
            id: 'connected',
            title: 'Connected',
            value: s.connected,
            unit: `of ${s.registered}`,
            status: s.connected > 0 ? 'success' : 'neutral',
            statusLabel: `${s.push} push · ${s.pull} pull`,
        },
        { id: 'online', title: 'Online', value: s.online, unit: 'sites', status: s.online > 0 ? 'success' : 'neutral', statusLabel: 'Reporting normally' },
        {
            id: 'attention',
            title: 'Need Attention',
            value: needsAttention.value,
            unit: 'sites',
            status: needsAttention.value > 0 ? 'warning' : 'neutral',
            statusLabel: needsAttention.value > 0 ? 'Warning or offline' : 'None',
        },
        {
            id: 'awaiting',
            title: 'Awaiting First Report',
            value: s.awaiting_first_report,
            unit: 'sites',
            status: 'info',
            statusLabel: 'Token issued, no report yet',
        },
    ];
});
</script>

<template>
    <div v-if="auth.can('site_configuration_manage')" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">API Configuration</h1>
                <p class="text-body mt-2 max-w-2xl">
                    How sites connect to this Control Center. This page only shows the rules in effect; a site's
                    own connection is set up under Site API.
                </p>
            </div>
            <UiBadge variant="neutral" class="px-3 py-1 text-xs">Read-only</UiBadge>
        </div>

        <ApiIntegrationTabs />

        <p v-if="loading" class="text-caption">Loading integration settings…</p>
        <p v-else-if="error" class="text-sm font-medium text-danger">{{ error }}</p>

        <template v-else-if="settings">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <UiCard v-for="card in cards" :key="card.title">
                    <h3 class="text-card-title">{{ card.title }}</h3>
                    <p class="text-caption mt-1">{{ card.hint }}</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div v-for="[label, value] in card.rows" :key="label">
                            <dt class="text-caption">{{ label }}</dt>
                            <dd class="mt-0.5 break-words font-medium text-text-primary">{{ value }}</dd>
                        </div>
                    </dl>
                </UiCard>
            </div>

            <DashboardSection
                title="Integration Status"
                description="The live state of the site connections, taken from what the sites last reported."
            >
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <UiKpiCard
                        v-for="tile in statusTiles"
                        :key="tile.id"
                        :title="tile.title"
                        :value="tile.value"
                        :unit="tile.unit"
                        :status="tile.status"
                        :status-label="tile.statusLabel"
                    />
                </div>
                <p class="text-caption mt-3">
                    Last report received: <span class="font-medium text-text-secondary">{{ lastReport ?? 'None yet' }}</span>
                </p>
            </DashboardSection>
        </template>
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
