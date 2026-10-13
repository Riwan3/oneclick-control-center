<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useModulesOverview } from '../composables/useModulesOverview';
import UiButton from '../components/ui/UiButton.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import SiteStatusSummary from '../components/sites/SiteStatusSummary.vue';
import SiteToolbar from '../components/sites/SiteToolbar.vue';
import SiteGrid from '../components/sites/SiteGrid.vue';
import SiteManagement from '../components/sites/SiteManagement.vue';

const auth = useAuthStore();
const route = useRoute();

// "Configure Site" on a site's detail page lands here as /sites?manage=CODE:
// the management panel opens with that site's edit form ready.
const manageCode = typeof route.query.manage === 'string' ? route.query.manage : null;
const showManagement = ref(Boolean(manageCode) && auth.can('site_manage'));

// Reuses the same resolver chain Modules.vue/Monitoring.vue/Dashboard.vue
// already consume — useModulesOverview()'s own filteredSites is every
// access-filtered site (its internal `search` stays empty here) already
// carrying a real effectiveModules map per site, resolved from
// governance (useSiteConfiguration) + runtime (data/mock/sites.js), not
// a second computation. Sites.vue never re-derives module status itself.
const { filteredSites: sitesWithModuleStatus } = useModulesOverview();

const search = ref('');
const statusFilter = ref('all');
const moduleFilter = ref('all');

// EFFECTIVE ACCESS: management/view only ever see the sites they were
// explicitly granted — everything downstream (summary, toolbar filters,
// grid) is derived from this, not from the raw mock catalogue.
const sites = computed(() => sitesWithModuleStatus.value);

const filteredSites = computed(() => {
    const term = search.value.trim().toLowerCase();

    return sites.value.filter((site) => {
        const matchesSearch =
            !term ||
            site.code.toLowerCase().includes(term) ||
            site.name.toLowerCase().includes(term) ||
            site.location.toLowerCase().includes(term);

        const matchesStatus = statusFilter.value === 'all' || site.status === statusFilter.value;

        const matchesModule =
            moduleFilter.value === 'all' || Boolean(site.modules?.[moduleFilter.value]);

        return matchesSearch && matchesStatus && matchesModule;
    });
});
</script>

<template>
    <div class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Sites</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Enterprise site registry and operational identity overview.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <UiBadge variant="neutral" class="px-3 py-1 text-xs">{{ sites.length }} Sites Registered</UiBadge>
                <UiButton v-if="auth.can('site_manage')" @click="showManagement = !showManagement">
                    {{ showManagement ? 'Close' : 'Manage Sites' }}
                </UiButton>
            </div>
        </div>

        <SiteManagement v-if="showManagement && auth.can('site_manage')" :edit-code="manageCode" />

        <!-- Summary always reflects all sites, independent of the toolbar filters below. -->
        <SiteStatusSummary :sites="sites" />

        <SiteToolbar
            v-model:search="search"
            v-model:status="statusFilter"
            v-model:module="moduleFilter"
        />

        <SiteGrid :sites="filteredSites" />
    </div>
</template>
