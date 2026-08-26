<script setup>
import { useAuthStore } from '../stores/auth';
import { useBaselineRegistry } from '../composables/useBaselineRegistry';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import BaselineHeader from '../components/baseline/BaselineHeader.vue';
import BaselineSummary from '../components/baseline/BaselineSummary.vue';
import BaselineModuleTable from '../components/baseline/BaselineModuleTable.vue';
import LegacyCatalogueMapping from '../components/baseline/LegacyCatalogueMapping.vue';
import SidebarManifest from '../components/module-observatory/SidebarManifest.vue';

const auth = useAuthStore();

// Reuses the existing capability architecture — no new capability was
// created. change_log_view was chosen because it is the closest
// existing governance-reference capability (true for super_admin and
// management, false for view) and because this page is, conceptually,
// exactly what that capability already describes: visibility into the
// ecosystem's governance/change reference. This step implements no
// write/mutation actions at all, so "management/view must not receive
// write authority automatically" has nothing to grant yet — when write
// actions are added later, they must be gated by a more restrictive
// capability (e.g. settings_manage), not this read capability.
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
} = useBaselineRegistry();
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
                <BaselineModuleTable
                    :modules="modules"
                    :selected-module-key="selectedModule?.module_key ?? null"
                    @select="selectModule"
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
