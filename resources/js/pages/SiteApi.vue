<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useApiIntegration } from '../composables/useApiIntegration';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiButton from '../components/ui/UiButton.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import ApiIntegrationTabs from '../components/api-integration/ApiIntegrationTabs.vue';

const auth = useAuthStore();
const { accessibleSites, siteConnections, statusLabel, statusVariant } = useApiIntegration();

const fieldClasses =
    'w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

const authMethods = ['None', 'API Key', 'Bearer Token', 'Basic Auth'];

const form = ref({
    siteCode: '',
    apiName: '',
    baseUrl: '',
    authenticationMethod: 'None',
    credential: '',
    timeout: '',
    notes: '',
});

// UI-only placeholder interaction — this phase explicitly forbids real
// API calls or fabricated connection results. Both actions only surface
// an honest inline notice; neither performs a network request nor
// mutates apiIntegration.js's mock data.
const actionNotice = ref(null);

function handleTestConnection() {
    actionNotice.value = 'Connection testing is not available yet — API Integration is in the foundation stage.';
}

function handleSaveConfiguration() {
    actionNotice.value = 'This is a UI preview only. Site API configuration is not yet persisted to a backend service.';
}
</script>

<template>
    <div v-if="auth.can('site_configuration_manage')" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Site API</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Configure API connectivity from monitored OneClick sites.
                </p>
            </div>
            <UiBadge variant="neutral" class="px-3 py-1 text-xs">No Site APIs Configured</UiBadge>
        </div>

        <ApiIntegrationTabs />

        <!-- Site API Registry -->
        <DashboardSection
            title="Site API Registry"
            description="API connections configured from individual sites into the Control Center."
        >
            <UiCard padding="none">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="border-b border-border bg-neutral-soft/50 text-xs font-semibold text-text-muted uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Site</th>
                                <th class="px-4 py-3">API Name</th>
                                <th class="px-4 py-3">Base URL</th>
                                <th class="px-4 py-3">Authentication</th>
                                <th class="px-4 py-3">Configuration Status</th>
                                <th class="px-4 py-3">Last Tested</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody v-if="siteConnections.length" class="divide-y divide-border">
                            <tr v-for="conn in siteConnections" :key="conn.id">
                                <td class="px-4 py-3 font-medium text-text-primary">{{ conn.siteCode }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ conn.apiName }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ conn.baseUrl }}</td>
                                <td class="px-4 py-3 text-text-secondary">{{ conn.authenticationMethod }}</td>
                                <td class="px-4 py-3">
                                    <UiBadge :variant="statusVariant[conn.configurationStatus]">{{ statusLabel[conn.configurationStatus] }}</UiBadge>
                                </td>
                                <td class="px-4 py-3 text-caption">{{ conn.lastTested ?? 'Not Tested' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <UiButton variant="ghost" size="sm">Edit</UiButton>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="7" class="py-10 text-center">
                                    <p class="font-semibold text-text-primary text-sm">No site API connections configured yet.</p>
                                    <p class="text-caption mt-1 max-w-md mx-auto">
                                        Use the form below to prepare a site connection. No connection is active until a real
                                        integration is built and tested.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </UiCard>
        </DashboardSection>

        <!-- Configure Site API -->
        <DashboardSection
            title="Configure Site API"
            description="Prepare connection details for a site's API — nothing here performs a real network call in this phase."
        >
            <UiCard>
                <form class="space-y-4" @submit.prevent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-label" for="site-api-site">Site</label>
                            <select id="site-api-site" v-model="form.siteCode" class="mt-1.5" :class="fieldClasses">
                                <option value="" disabled>Select a site…</option>
                                <option v-for="site in accessibleSites" :key="site.code" :value="site.code">
                                    {{ site.code }} — {{ site.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="text-label" for="site-api-name">API Name</label>
                            <input id="site-api-name" v-model="form.apiName" type="text" placeholder="e.g. Site Telemetry Feed" class="mt-1.5" :class="fieldClasses" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-label" for="site-api-url">Base URL</label>
                            <input id="site-api-url" v-model="form.baseUrl" type="text" placeholder="https://site.example.com/api" class="mt-1.5" :class="fieldClasses" />
                        </div>
                        <div>
                            <label class="text-label" for="site-api-auth">Authentication Method</label>
                            <select id="site-api-auth" v-model="form.authenticationMethod" class="mt-1.5" :class="fieldClasses">
                                <option v-for="method in authMethods" :key="method" :value="method">{{ method }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-label" for="site-api-credential">API Key / Credential</label>
                            <input id="site-api-credential" v-model="form.credential" type="password" placeholder="Not stored in this preview" class="mt-1.5" :class="fieldClasses" />
                        </div>
                        <div>
                            <label class="text-label" for="site-api-timeout">Timeout (seconds)</label>
                            <input id="site-api-timeout" v-model="form.timeout" type="number" min="0" placeholder="30" class="mt-1.5" :class="fieldClasses" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-label" for="site-api-notes">Notes</label>
                            <textarea id="site-api-notes" v-model="form.notes" rows="3" :class="fieldClasses" class="mt-1.5"></textarea>
                        </div>
                    </div>

                    <p v-if="actionNotice" class="text-sm font-medium text-text-secondary">{{ actionNotice }}</p>

                    <div class="flex flex-wrap items-center gap-3">
                        <UiButton type="button" variant="secondary" @click="handleTestConnection">Test Connection</UiButton>
                        <UiButton type="button" variant="primary" @click="handleSaveConfiguration">Save Configuration</UiButton>
                    </div>
                </form>
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
