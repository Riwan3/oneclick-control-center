<script setup>
import { useAuthStore } from '../stores/auth';
import { useBaselineRegistry } from '../composables/useBaselineRegistry';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import BaselineHeader from '../components/baseline/BaselineHeader.vue';
import BaselineSummary from '../components/baseline/BaselineSummary.vue';
import BaselineModuleTable from '../components/baseline/BaselineModuleTable.vue';
import BaselineRegistration from '../components/baseline/BaselineRegistration.vue';
import LegacyCatalogueMapping from '../components/baseline/LegacyCatalogueMapping.vue';
import SidebarManifest from '../components/module-observatory/SidebarManifest.vue';

const auth = useAuthStore();

// Visibility reuses change_log_view (true for super_admin and management, false
// for view) — the closest existing governance-reference capability. Writes
// (register / unregister a module) are gated by the more restrictive
// baseline_manage, true only for super_admin; see canManage below.
const {
    loading,
    error,
    baselineVersion,
    modules,
    summary,
    selectedModule,
    selectedModuleMenus,
    selectModule,
    legacyModuleMapping,
    candidates,
    acting,
    actionError,
    registerModule,
    unregisterModule,
} = useBaselineRegistry();

// Registering / unregistering changes the baseline for every site, so it is
// Super Admin only (baseline_manage); other roles see the registry read-only.
const canManage = auth.can('baseline_manage');

function handleUnregister(mod) {
    const message = `Unregister ${mod.label} from the baseline? It becomes an extension again on the sites that report it; per-site settings are kept.`;
    if (window.confirm(message)) unregisterModule(mod.module_key);
}
</script>

<template>
    <!-- Page-level capability guard, same convention as Modules.vue / Deployments.vue / ModuleObservatory.vue. -->
    <div v-if="auth.can('change_log_view')" class="space-y-8">
        <p v-if="loading" class="text-caption">Loading baseline registry…</p>
        <p v-else-if="error" class="text-sm font-medium text-danger">{{ error }}</p>

        <template v-else>
            <BaselineHeader :baseline-version="baselineVersion" />

            <DashboardSection title="Summary" description="Ecosystem-wide totals, derived from the registry data.">
                <BaselineSummary :summary="summary" />
            </DashboardSection>

            <LegacyCatalogueMapping :entries="legacyModuleMapping" />

            <DashboardSection title="Modules" description="Every module in the baseline. Select a row to view its sidebar structure.">
                <p v-if="actionError" class="mb-3 text-sm font-medium text-danger">{{ actionError }}</p>
                <BaselineModuleTable
                    :modules="modules"
                    :selected-module-key="selectedModule?.module_key ?? null"
                    :can-manage="canManage"
                    :busy="acting"
                    @select="selectModule"
                    @unregister="handleUnregister"
                />
            </DashboardSection>

            <DashboardSection
                title="Register Module"
                description="Modules that sites report but the baseline does not have yet. Registering one adds it, with its sidebar, to the baseline."
            >
                <BaselineRegistration
                    :candidates="candidates"
                    :can-manage="canManage"
                    :busy="acting"
                    @register="registerModule"
                />
            </DashboardSection>

            <DashboardSection
                title="Sidebar Structure"
                :description="selectedModule ? `${selectedModule.label} baseline hierarchy.` : ''"
            >
                <SidebarManifest
                    v-if="selectedModule"
                    :title="`${selectedModule.label} — Baseline`"
                    :menus="selectedModuleMenus"
                />
                <UiCard v-else>
                    <p class="text-caption">No module selected.</p>
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
