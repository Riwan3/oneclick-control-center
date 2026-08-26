<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useModuleObservatory } from '../composables/useModuleObservatory';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import ObservatoryHeader from '../components/module-observatory/ObservatoryHeader.vue';
import ObservatorySummary from '../components/module-observatory/ObservatorySummary.vue';
import ModuleVersionStatus from '../components/module-observatory/ModuleVersionStatus.vue';
import SidebarManifest from '../components/module-observatory/SidebarManifest.vue';
import ConfigurationDiff from '../components/module-observatory/ConfigurationDiff.vue';
import SiteModuleComparison from '../components/module-observatory/SiteModuleComparison.vue';

const auth = useAuthStore();
const route = useRoute();

const {
    loading,
    error,
    moduleName,
    baseline,
    monitoredSiteIds,
    sites,
    summary,
    versionDistribution,
    latestVersion,
    overallStatus,
    lastSyncedAt,
    selectedSite,
    selectSite,
} = useModuleObservatory('one-power');

// Direct site selection (?site=CODE) — respects effective site access
// exactly like SiteDetail.vue: an id that isn't in the already-filtered
// `sites` list is simply never selectable (selectSite() no-ops), so a
// requested-but-unauthorized site never exposes its manifest. When the
// requested site DOES exist for this module but isn't accessible, the
// page shows the same access-restricted convention used elsewhere
// instead of silently substituting a different site.
const requestedSiteId = computed(() => (typeof route.query.site === 'string' ? route.query.site.toLowerCase() : null));
const requestedSiteBlocked = computed(() => {
    if (!requestedSiteId.value) return false;
    const isMonitored = monitoredSiteIds.value.includes(requestedSiteId.value);
    const isAccessible = sites.value.some((site) => site.siteId === requestedSiteId.value);
    return isMonitored && !isAccessible;
});

onMounted(() => {
    if (requestedSiteId.value) selectSite(requestedSiteId.value);
});
</script>

<template>
    <!-- Page-level capability guard, same convention as Modules.vue / Deployments.vue. -->
    <div v-if="auth.can('module_view')" class="space-y-8">
        <div>
            <h1 class="text-page-title">Module Observatory</h1>
            <p class="text-body mt-2 max-w-2xl">
                Configuration structure, version, and sidebar manifest for monitored OneClick
                applications, compared against a governance baseline.
            </p>
        </div>

        <UiCard v-if="requestedSiteBlocked">
            <h2 class="text-page-title">Access Restricted</h2>
            <p class="text-body mt-2 max-w-2xl">
                You don't have permission to view this site's configuration. Contact your
                administrator if you believe this is a mistake.
            </p>
        </UiCard>

        <template v-else>
            <p v-if="loading" class="text-caption">Loading observatory data…</p>
            <p v-else-if="error" class="text-sm font-medium text-danger">{{ error }}</p>

            <template v-else-if="moduleName">
                <ObservatoryHeader
                    :module-name="moduleName"
                    :baseline-version="baseline.version"
                    :latest-version="latestVersion"
                    :overall-status="overallStatus"
                    :last-synced-at="lastSyncedAt"
                />

                <DashboardSection title="Summary" description="Governance overview across the sites you have access to.">
                    <ObservatorySummary :summary="summary" />
                </DashboardSection>

                <DashboardSection title="Version" description="Baseline vs. observed version distribution.">
                    <ModuleVersionStatus
                        :baseline-version="baseline.version"
                        :latest-version="latestVersion"
                        :version-distribution="versionDistribution"
                    />
                </DashboardSection>

                <DashboardSection
                    title="Sidebar Structure"
                    description="Baseline manifest vs. the selected site's current manifest."
                >
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <SidebarManifest title="Baseline (Governance Reference)" :menus="baseline.menus" />
                        <SidebarManifest
                            v-if="selectedSite"
                            :title="`${selectedSite.siteCode} — Current`"
                            :menus="selectedSite.menus ?? []"
                            :diff="selectedSite.menus ? selectedSite.diff : null"
                        />
                        <UiCard v-else>
                            <p class="text-caption">No accessible site to display.</p>
                        </UiCard>
                    </div>
                </DashboardSection>

                <DashboardSection title="Configuration Diff" description="Selected site vs. baseline.">
                    <ConfigurationDiff
                        v-if="selectedSite && selectedSite.menus"
                        :module-name="moduleName"
                        :baseline-count="baseline.menus.length"
                        :current-count="selectedSite.menus.length"
                        :diff="selectedSite.diff"
                    />
                    <UiCard v-else>
                        <p class="text-caption">
                            {{
                                selectedSite
                                    ? `${selectedSite.siteCode} has not reported a configuration manifest yet.`
                                    : 'No accessible site to display.'
                            }}
                        </p>
                    </UiCard>
                </DashboardSection>

                <DashboardSection title="Site Comparison" description="All sites you have access to for this module.">
                    <SiteModuleComparison
                        :sites="sites"
                        :selected-site-id="selectedSite?.siteId ?? null"
                        @select="selectSite"
                    />
                </DashboardSection>
            </template>

            <UiCard v-else>
                <h2 class="text-page-title">Not Available</h2>
                <p class="text-body mt-2 max-w-2xl">This module is not yet monitored by the Observatory.</p>
            </UiCard>
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
