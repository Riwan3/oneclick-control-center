<script setup>
import { useAuthStore } from '../stores/auth';
import { useSiteApi } from '../composables/useSiteApi';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiStatus from '../components/ui/UiStatus.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import ApiIntegrationTabs from '../components/api-integration/ApiIntegrationTabs.vue';

const auth = useAuthStore();
const {
    connections,
    summary,
    busyId,
    actionError,
    notice,
    issued,
    copied,
    envSnippet,
    issueToken,
    testConnection,
    copySnippet,
    modeMeta,
    statusVariant,
    statusLabel,
} = useSiteApi();

// What a site says about applying this Control Center's configuration.
function configSyncText(sync) {
    if (!sync) return '—';
    if (!sync.apply) return 'Received, not applied';
    const hidden = `${sync.disabled_modules} module${sync.disabled_modules === 1 ? '' : 's'}, ${sync.disabled_menus} menu${sync.disabled_menus === 1 ? '' : 's'} hidden`;
    return sync.unmapped_menus ? `Applied — ${hidden} (${sync.unmapped_menus} unmapped)` : `Applied — ${hidden}`;
}
</script>

<template>
    <div v-if="auth.can('site_configuration_manage')" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Site API</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Connect each OneClick site to this Control Center with an API token, and check that it reports.
                </p>
            </div>
            <UiBadge :variant="summary.connected ? 'success' : 'neutral'" class="px-3 py-1 text-xs">
                {{ summary.connected }} of {{ summary.total }} sites connected
            </UiBadge>
        </div>

        <ApiIntegrationTabs />

        <DashboardSection
            title="Site API Registry"
            description="Every registered site and the state of its connection. Add or edit sites under Sites → Manage Sites."
        >
            <p v-if="actionError" class="text-sm font-medium text-danger">{{ actionError }}</p>
            <p v-if="notice" class="text-sm font-medium text-text-secondary">{{ notice }}</p>

            <div v-if="issued" class="space-y-3 rounded-lg border border-warning/50 bg-warning-soft p-4">
                <p class="text-sm font-semibold text-text-primary">Token for {{ issued.code }} — shown only once</p>
                <p class="text-caption">
                    Put these lines in the site's <span class="font-medium">.env</span> (add
                    <span class="font-medium">CONTROL_CENTER_APPLY_CONFIG=true</span> to let this Control Center hide
                    modules and menus on the site), restart it, then run
                    <span class="font-medium">php artisan cc:push</span> on the site. Copy it now: it cannot be shown
                    again, only replaced by a new token.
                </p>
                <pre class="overflow-x-auto rounded-md bg-surface p-3 text-xs text-text-primary">{{ envSnippet }}</pre>
                <p class="text-caption">
                    Both on one machine with a self-signed certificate? Use <span class="font-medium">http://</span>
                    in <span class="font-medium">CONTROL_CENTER_URL</span> — the site cannot verify a local HTTPS certificate.
                </p>
                <div class="flex gap-2">
                    <UiButton size="sm" @click="copySnippet">{{ copied ? 'Copied' : 'Copy' }}</UiButton>
                    <UiButton size="sm" variant="ghost" @click="issued = null">I have saved it</UiButton>
                </div>
            </div>

            <UiCard padding="none">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="border-b border-border bg-neutral-soft/50 text-xs font-semibold text-text-muted uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Site</th>
                                <th class="px-4 py-3">Mode</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Last report / check</th>
                                <th class="px-4 py-3">Configuration on site</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody v-if="connections.length" class="divide-y divide-border">
                            <tr v-for="site in connections" :key="site.dbId">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-text-primary">{{ site.code }}</p>
                                    <p class="text-caption">{{ site.name }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <UiBadge :variant="modeMeta[site.mode]?.variant">{{ modeMeta[site.mode]?.label }}</UiBadge>
                                    <p v-if="site.mode === 'pull'" class="text-caption mt-1">{{ site.baseUrl }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <UiStatus :variant="statusVariant[site.status]" :label="statusLabel[site.status]" />
                                    <p v-if="site.connected && site.status !== 'unknown'" class="text-caption mt-1">
                                        OneClick {{ site.version }}
                                    </p>
                                    <p v-if="site.lastError" class="text-caption mt-1 text-danger">{{ site.lastError }}</p>
                                </td>
                                <td class="px-4 py-3 text-caption">
                                    {{ site.connected ? site.lastActivity : 'Never' }}
                                </td>
                                <td class="px-4 py-3 text-caption">{{ configSyncText(site.configSync) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <UiButton
                                            variant="secondary"
                                            size="sm"
                                            :disabled="busyId === site.dbId"
                                            @click="issueToken(site)"
                                        >
                                            {{ site.mode === 'none' ? 'Connect' : 'New token' }}
                                        </UiButton>
                                        <UiButton
                                            v-if="site.mode === 'pull'"
                                            variant="secondary"
                                            size="sm"
                                            :disabled="busyId === site.dbId"
                                            @click="testConnection(site)"
                                        >
                                            Test connection
                                        </UiButton>
                                        <router-link :to="{ path: '/sites', query: { manage: site.code } }">
                                            <UiButton variant="ghost" size="sm">Edit</UiButton>
                                        </router-link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="py-10 text-center">
                                    <p class="font-semibold text-text-primary text-sm">No sites registered yet.</p>
                                    <p class="text-caption mt-1 max-w-md mx-auto">
                                        Register a site under
                                        <router-link to="/sites" class="font-medium text-primary hover:text-primary-hover">Sites</router-link>
                                        → Manage Sites, then come back here to connect it.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </UiCard>

            <UiCard>
                <h3 class="text-card-title">How a site connects</h3>
                <ol class="text-body mt-2 list-decimal space-y-1 pl-5 text-sm">
                    <li><span class="font-medium">Connect</span> issues a token and shows the three lines for the site's <span class="font-medium">.env</span>.</li>
                    <li>
                        <span class="font-medium">Push</span> (default, no Site URL): the site runs <span class="font-medium">php artisan cc:push</span>
                        (every 5 minutes when its scheduler runs) and this Control Center answers with the site's configuration.
                    </li>
                    <li>
                        <span class="font-medium">Pull</span> (set a Site URL under Edit): this Control Center calls the site; use
                        <span class="font-medium">Test connection</span>. Pull only reads telemetry — configuration reaches a site through push.
                    </li>
                </ol>
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
