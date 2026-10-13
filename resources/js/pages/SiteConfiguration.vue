<script setup>
import { computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useSiteConfiguration } from '../composables/useSiteConfiguration';
import { useConfigurationSnapshots } from '../composables/useConfigurationSnapshots';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import SiteConfigCard from '../components/site-configuration/SiteConfigCard.vue';
import SiteModuleConfigTable from '../components/site-configuration/SiteModuleConfigTable.vue';
import SiteMenuConfigTree from '../components/site-configuration/SiteMenuConfigTree.vue';
import SnapshotList from '../components/site-configuration/SnapshotList.vue';
import SnapshotDetail from '../components/site-configuration/SnapshotDetail.vue';

const auth = useAuthStore();

// Reuses the existing capability architecture — one new capability,
// site_configuration_manage, added to config/roles.js (true only for
// super_admin), matching how user_manage already gates the whole Users
// page. No new authorization mechanism, no hardcoded role checks.
const canManage = auth.can('site_configuration_manage');

const {
    loading,
    error,
    saving,
    sites,
    selectedSite,
    selectedSiteModules,
    selectedModule,
    selectedModuleMenus,
    selectSite,
    selectModule,
    toggleModule,
    toggleMenu,
    totalModules,
    totalMenus,
} = useSiteConfiguration();

// "Manage Modules" on a site's detail page lands here as
// /site-configuration?site=CODE: select that site once, as soon as the
// configuration has loaded (later reloads must not reset the user's choice).
const route = useRoute();
let siteFromQueryApplied = false;
watch(
    sites,
    (list) => {
        const code = typeof route.query.site === 'string' ? route.query.site.toUpperCase() : null;
        if (siteFromQueryApplied || !code || !list.length) return;
        siteFromQueryApplied = true;
        selectSite(code);
    },
    { immediate: true }
);

// GOVERNANCE SUMMARY — ecosystem-wide aggregates built only from the
// per-site `summary` (modulesEnabled/modulesTotal) useSiteConfiguration()
// already exposes for every site, not a second governance computation.
// Deliberately does NOT include an "Active Overrides" tile: that would
// require each site's full per-module baseline/override detail, which
// the composable only computes for the currently selected site — so an
// ecosystem-wide override count can't be derived reliably without a
// second resolver, and is omitted rather than approximated.
const totalEnabledModules = computed(() => sites.value.reduce((sum, s) => sum + s.summary.modulesEnabled, 0));
const totalDisabledModules = computed(
    () => sites.value.reduce((sum, s) => sum + (s.summary.modulesTotal - s.summary.modulesEnabled), 0)
);

const governanceSummary = computed(() => [
    { id: 'total-sites', title: 'Total Sites', value: sites.value.length, unit: 'sites', status: 'info', statusLabel: 'Governed Scope' },
    { id: 'canonical-modules', title: 'Canonical Modules', value: totalModules, unit: 'modules', status: 'info', statusLabel: 'Baseline Registry' },
    { id: 'enabled-modules', title: 'Enabled Modules', value: totalEnabledModules.value, unit: 'site-modules', status: 'success', statusLabel: 'Ecosystem-wide' },
    { id: 'disabled-modules', title: 'Disabled Modules', value: totalDisabledModules.value, unit: 'site-modules', status: 'neutral', statusLabel: 'Ecosystem-wide' },
]);

// EFFECTIVE CONFIGURATION SUMMARY (selected site only) — a module has
// an override iff its effective state differs from baseline, the same
// rule SiteModuleConfigTable.vue uses (see its own comment): the
// backend's sparse override model only persists a row when the state
// deviates from baseline. This is reliable for the selected site
// because useSiteConfiguration() already resolves each module's
// baselineEnabled/siteEnabled there (selectedSiteModules).
const selectedSiteOverrideCount = computed(
    () => selectedSiteModules.value.filter((mod) => mod.siteEnabled !== mod.baselineEnabled).length
);

const configurationExplanation = [
    { label: 'Baseline', desc: 'The canonical default module policy defined by the Master Baseline Registry.' },
    { label: 'Override', desc: 'A site-specific governance decision stored against this site, deviating from baseline.' },
    { label: 'Effective Configuration', desc: 'The final policy — baseline unless overridden — that the rest of OneClick consumes.' },
];

const {
    snapshots,
    loadingList: loadingSnapshots,
    loadSnapshots,
    selectedSnapshot,
    selectedSnapshotModules,
    selectedSnapshotMenusForModule,
    loadingDetail: loadingSnapshotDetail,
    loadSnapshotDetail,
    clearSelectedSnapshot,
    creating: creatingSnapshot,
    createSnapshot,
} = useConfigurationSnapshots();

watch(
    () => selectedSite.value?.siteId,
    (siteId) => {
        clearSelectedSnapshot();
        if (siteId) loadSnapshots(siteId);
    },
    { immediate: true }
);

function handleCreateSnapshot() {
    if (selectedSite.value) createSnapshot(selectedSite.value.siteId);
}

function handleOpenSnapshot(snapshotId) {
    if (selectedSite.value) loadSnapshotDetail(selectedSite.value.siteId, snapshotId);
}
</script>

<template>
    <div v-if="canManage" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Site Configuration</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Site-level module governance and configuration — which baseline modules and sidebar menu
                    items each site is authorized to use.
                </p>
            </div>
            <UiBadge variant="info" class="px-3 py-1 text-xs">Governance Configuration</UiBadge>
        </div>

        <p v-if="loading" class="text-caption">Loading site configuration…</p>
        <p v-else-if="error" class="text-sm font-medium text-danger">{{ error }}</p>

        <template v-else>
            <!-- Governance Summary -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <UiKpiCard
                    v-for="kpi in governanceSummary"
                    :key="kpi.id"
                    :title="kpi.title"
                    :value="kpi.value"
                    :unit="kpi.unit"
                    :status="kpi.status"
                    :status-label="kpi.statusLabel"
                />
            </div>

            <DashboardSection
                title="Sites"
                :description="`Baseline: ${totalModules} modules / ${totalMenus} menus. Select a site to inspect its module governance.`"
            >
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <SiteConfigCard
                        v-for="site in sites"
                        :key="site.siteCode"
                        :site="site"
                        :selected="site.siteCode === selectedSite?.siteCode"
                        @select="selectSite"
                    />
                </div>
                <p v-if="!sites.length" class="text-caption">No sites available.</p>
            </DashboardSection>

            <template v-if="selectedSite">
                <!-- Site Governance Header -->
                <UiCard variant="glass">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-label">Selected Site</p>
                            <p class="text-kpi-value mt-1">{{ selectedSite.siteCode }}</p>
                            <p class="text-caption mt-1">{{ selectedSite.identity.name }}</p>
                        </div>
                        <div class="flex flex-wrap gap-6 text-sm">
                            <div>
                                <p class="text-caption">Enabled Modules</p>
                                <p class="mt-1 font-semibold text-success">{{ selectedSite.summary.modulesEnabled }} / {{ selectedSite.summary.modulesTotal }}</p>
                            </div>
                            <div>
                                <p class="text-caption">Disabled Modules</p>
                                <p class="mt-1 font-semibold text-text-secondary">{{ selectedSite.summary.modulesTotal - selectedSite.summary.modulesEnabled }} / {{ selectedSite.summary.modulesTotal }}</p>
                            </div>
                            <div>
                                <p class="text-caption">Site Overrides</p>
                                <p class="mt-1 font-semibold text-primary">{{ selectedSiteOverrideCount }}</p>
                            </div>
                        </div>
                    </div>
                </UiCard>

                <DashboardSection
                    :title="`Module Governance — ${selectedSite.siteCode}`"
                    description="Baseline policy, site override, and effective configuration for every canonical module. Select a module to configure its sidebar."
                >
                    <SiteModuleConfigTable
                        :modules="selectedSiteModules"
                        :selected-module-key="selectedModule?.module_key ?? null"
                        :can-manage="canManage"
                        :saving="saving"
                        @select="selectModule"
                        @toggle="toggleModule"
                    />

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border/60 bg-background p-3.5 text-sm">
                        <p class="text-caption">
                            Effective Configuration — {{ selectedSite.summary.modulesEnabled }} modules enabled,
                            {{ selectedSite.summary.modulesTotal - selectedSite.summary.modulesEnabled }} disabled,
                            {{ selectedSiteOverrideCount }} site-specific override{{ selectedSiteOverrideCount === 1 ? '' : 's' }}.
                        </p>
                        <p class="text-caption">This is the policy downstream deployment observability (Modules, Monitoring) consumes.</p>
                    </div>
                </DashboardSection>

                <DashboardSection
                    v-if="selectedModule"
                    :title="`Sidebar Configuration — ${selectedModule.label}`"
                    description=""
                >
                    <!-- BASELINE vs SITE CONFIGURATION shown as two distinct
                         numbers — the baseline itself never changes here. -->
                    <div class="mb-4 flex flex-wrap gap-6 text-sm">
                        <div>
                            <p class="text-caption">Baseline</p>
                            <p class="text-kpi-value mt-1">{{ selectedModuleMenus.length }} <span class="text-base font-medium text-text-secondary">menus</span></p>
                        </div>
                        <div>
                            <p class="text-caption">Site Configuration</p>
                            <p class="text-kpi-value mt-1">
                                {{ selectedModuleMenus.filter((m) => m.effectiveEnabled).length }}
                                <span class="text-base font-medium text-text-secondary">enabled</span>
                            </p>
                        </div>
                    </div>

                    <SiteMenuConfigTree
                        :menus="selectedModuleMenus"
                        :can-manage="canManage"
                        :saving="saving"
                        @toggle="toggleMenu"
                    />
                </DashboardSection>

                <DashboardSection
                    :title="`Configuration Snapshots — ${selectedSite.siteCode}`"
                    description="Immutable historical records of this site's effective configuration. Creating a snapshot never changes Site Configuration, and later Site Configuration changes never alter an existing snapshot."
                >
                    <SnapshotDetail
                        v-if="selectedSnapshot || loadingSnapshotDetail"
                        :snapshot="selectedSnapshot"
                        :modules="selectedSnapshotModules"
                        :menus-for-module="selectedSnapshotMenusForModule"
                        :loading="loadingSnapshotDetail"
                        @close="clearSelectedSnapshot"
                    />
                    <SnapshotList
                        v-else
                        :snapshots="snapshots"
                        :loading="loadingSnapshots"
                        :creating="creatingSnapshot"
                        :can-manage="canManage"
                        @create="handleCreateSnapshot"
                        @open="handleOpenSnapshot"
                    />
                </DashboardSection>
            </template>

            <!-- Configuration Explanation -->
            <DashboardSection
                title="Configuration Semantics"
                description="How baseline policy and site overrides combine into effective configuration."
            >
                <UiCard>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 text-xs">
                        <div v-for="item in configurationExplanation" :key="item.label" class="rounded-lg border border-border/60 bg-background p-3.5">
                            <p class="font-semibold uppercase tracking-wider text-primary text-[11px]">{{ item.label }}</p>
                            <p class="text-body text-xs mt-1">{{ item.desc }}</p>
                        </div>
                    </div>
                </UiCard>
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
