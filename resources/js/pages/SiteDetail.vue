<script setup>
import { computed, ref, watch } from 'vue';
import { apiJson } from '../lib/http';
import { useAuthStore } from '../stores/auth';
import { useEffectiveAccess } from '../composables/useEffectiveAccess';
import { sites } from '../data/siteCatalogue';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiStatus from '../components/ui/UiStatus.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import SiteExecutiveKpi from '../components/site-detail/SiteExecutiveKpi.vue';
import SiteInformation from '../components/site-detail/SiteInformation.vue';
import SiteModuleStatus from '../components/site-detail/SiteModuleStatus.vue';
import SitePerformanceOverview from '../components/site-detail/SitePerformanceOverview.vue';
import SiteRecentActivity from '../components/site-detail/SiteRecentActivity.vue';

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
});

const auth = useAuthStore();
const { canAccessSite } = useEffectiveAccess();

// EFFECTIVE ACCESS: a management/view user navigating directly to
// /sites/{unauthorized-site-id} — by typing the URL, editing history
// state, or any other client-side manipulation — must not see that
// site's data. Rather than expose a distinct "forbidden" branch (which
// would itself confirm the site exists), an unauthorized site resolves
// to the same null/"not found" state as a genuinely unknown id.
const site = computed(() => {
    const match = sites.find((s) => s.id === props.id) ?? null;
    if (!match) return null;
    return canAccessSite(match.code) ? match : null;
});

// Server-health breakdown and recent activity come from the detail
// endpoint (the catalogue only carries the light list fields).
const detail = ref(null);

watch(
    () => (site.value?.connected ? site.value.dbId : null),
    async (dbId) => {
        detail.value = null;
        if (!dbId) return;
        const { ok, data } = await apiJson(`/api/site-connections/${dbId}`);
        if (ok) detail.value = data?.data ?? null;
    },
    { immediate: true }
);

const statusVariant = { online: 'success', warning: 'warning', offline: 'danger', unknown: 'neutral' };
const statusLabel = { online: 'Online', warning: 'Warning', offline: 'Offline', unknown: 'Not Connected' };
</script>

<template>
    <div v-if="site" class="space-y-8">
        <div>
            <router-link to="/sites" class="text-sm font-medium text-primary hover:text-primary-hover">
                ← Back to Sites
            </router-link>

            <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-page-title">{{ site.code }}</p>
                    <p class="text-body mt-1">{{ site.name }}</p>
                    <p class="text-caption mt-1">{{ site.location }}</p>
                    <div class="mt-3 flex items-center gap-3">
                        <UiStatus :variant="statusVariant[site.status]" :label="statusLabel[site.status]" />
                        <span class="text-caption">OneClick {{ site.version }}</span>
                    </div>
                </div>

                <!-- Super Admin only. Both open the page that already does the work, with this site preselected. -->
                <div v-if="auth.can('site_manage') || auth.can('module_manage')" class="flex gap-3">
                    <router-link v-if="auth.can('site_manage')" :to="{ path: '/sites', query: { manage: site.code } }">
                        <UiButton variant="secondary">Configure Site</UiButton>
                    </router-link>
                    <router-link
                        v-if="auth.can('module_manage')"
                        :to="{ path: '/site-configuration', query: { site: site.code } }"
                    >
                        <UiButton variant="secondary">Manage Modules</UiButton>
                    </router-link>
                </div>
            </div>
        </div>

        <SiteExecutiveKpi :site="site" />

        <SiteInformation :site="site" />

        <DashboardSection
            v-if="auth.can('module_view')"
            title="Module Status"
            description="OneClick module availability for this site."
        >
            <SiteModuleStatus :modules="site.modules" />
        </DashboardSection>

        <DashboardSection
            v-if="auth.can('monitoring') && site.connected"
            title="Performance Overview"
            description="Login activity and server condition reported by this site."
        >
            <SitePerformanceOverview :daily="site.dailyActivity" :health="detail?.health ?? null" />
        </DashboardSection>

        <DashboardSection
            v-if="auth.can('monitoring') && site.connected"
            title="Recent Activity"
            description="Latest logins on this site."
        >
            <SiteRecentActivity :activities="detail?.recent_activity ?? []" />
        </DashboardSection>

    </div>

    <UiCard v-else>
        <h2 class="text-page-title">Site not found</h2>
        <p class="text-body mt-2 max-w-2xl">
            We couldn't find a site matching &quot;{{ id }}&quot;. It may have been removed or the
            link is incorrect.
        </p>
        <router-link to="/sites" class="mt-4 inline-block">
            <UiButton variant="secondary">← Back to Sites</UiButton>
        </router-link>
    </UiCard>
</template>
