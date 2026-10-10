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
const { configuration, statusLabel, statusVariant } = useApiIntegration();

const fieldClasses =
    'w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

const environments = ['Sandbox', 'Staging', 'Production'];
const authMethods = ['None', 'API Key', 'Bearer Token', 'Basic Auth'];

const form = ref({
    environment: '',
    baseUrl: '',
    timeout: '',
    authenticationMethod: 'None',
    credential: '',
});

// Same UI-only placeholder pattern as Site API — no real persistence,
// no fabricated success result.
const actionNotice = ref(null);
function handleSaveConfiguration() {
    actionNotice.value = 'This is a UI preview only. API Configuration is not yet persisted to a backend service.';
}
</script>

<template>
    <div v-if="auth.can('site_configuration_manage')" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">API Configuration</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Centralized connection, authentication, and integration status configuration.
                </p>
            </div>
            <UiBadge variant="neutral" class="px-3 py-1 text-xs">Configuration Pending</UiBadge>
        </div>

        <ApiIntegrationTabs />

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <UiCard>
                <h3 class="text-card-title">Connection Configuration</h3>
                <form class="mt-4 space-y-4" @submit.prevent>
                    <div>
                        <label class="text-label" for="api-config-env">Environment</label>
                        <select id="api-config-env" v-model="form.environment" class="mt-1.5" :class="fieldClasses">
                            <option value="" disabled>Select an environment…</option>
                            <option v-for="env in environments" :key="env" :value="env">{{ env }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-label" for="api-config-url">Base URL</label>
                        <input id="api-config-url" v-model="form.baseUrl" type="text" placeholder="https://api.example.com" class="mt-1.5" :class="fieldClasses" />
                    </div>
                    <div>
                        <label class="text-label" for="api-config-timeout">Timeout (seconds)</label>
                        <input id="api-config-timeout" v-model="form.timeout" type="number" min="0" placeholder="30" class="mt-1.5" :class="fieldClasses" />
                    </div>
                </form>
            </UiCard>

            <UiCard>
                <h3 class="text-card-title">Authentication</h3>
                <form class="mt-4 space-y-4" @submit.prevent>
                    <div>
                        <label class="text-label" for="api-config-auth">Authentication Method</label>
                        <select id="api-config-auth" v-model="form.authenticationMethod" class="mt-1.5" :class="fieldClasses">
                            <option v-for="method in authMethods" :key="method" :value="method">{{ method }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-label" for="api-config-credential">Credential / Token</label>
                        <input id="api-config-credential" v-model="form.credential" type="password" placeholder="Not stored in this preview" class="mt-1.5" :class="fieldClasses" />
                    </div>
                </form>

                <p v-if="actionNotice" class="mt-4 text-sm font-medium text-text-secondary">{{ actionNotice }}</p>
                <div class="mt-4">
                    <UiButton type="button" variant="primary" @click="handleSaveConfiguration">Save Configuration</UiButton>
                </div>
            </UiCard>
        </div>

        <DashboardSection
            title="Integration Status"
            description="Current state of the global API configuration — no connection has been established yet."
        >
            <UiCard>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                    <div>
                        <p class="text-caption">Configuration Status</p>
                        <UiBadge class="mt-1.5" :variant="statusVariant[configuration.configurationStatus]">
                            {{ statusLabel[configuration.configurationStatus] }}
                        </UiBadge>
                    </div>
                    <div>
                        <p class="text-caption">Connection Status</p>
                        <UiBadge class="mt-1.5" :variant="statusVariant[configuration.connectionStatus]">
                            {{ statusLabel[configuration.connectionStatus] }}
                        </UiBadge>
                    </div>
                    <div>
                        <p class="text-caption">Last Test</p>
                        <p class="mt-1.5 font-medium text-text-secondary">{{ configuration.lastTest ?? 'Not Available' }}</p>
                    </div>
                    <div>
                        <p class="text-caption">Last Synchronization</p>
                        <p class="mt-1.5 font-medium text-text-secondary">{{ configuration.lastSynchronization ?? 'Not Available' }}</p>
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
