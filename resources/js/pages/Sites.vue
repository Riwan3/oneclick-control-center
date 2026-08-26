<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useEffectiveAccess } from '../composables/useEffectiveAccess';
import UiButton from '../components/ui/UiButton.vue';
import SiteStatusSummary from '../components/sites/SiteStatusSummary.vue';
import SiteToolbar from '../components/sites/SiteToolbar.vue';
import SiteGrid from '../components/sites/SiteGrid.vue';
import { sites as allSites } from '../data/mock/sites';

const auth = useAuthStore();
const { canAccessSite } = useEffectiveAccess();

const search = ref('');
const statusFilter = ref('all');
const moduleFilter = ref('all');

// EFFECTIVE ACCESS: management/view only ever see the sites they were
// explicitly granted — everything downstream (summary, toolbar filters,
// grid) is derived from this, not from the raw mock catalogue.
const sites = computed(() => allSites.filter((site) => canAccessSite(site.code)));

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
                    Manage and monitor OneClick sites across PPA Group.
                </p>
            </div>

            <!-- UI-only placeholder — does not create a site or call any API. -->
            <UiButton v-if="auth.can('site_manage')">Add Site</UiButton>
        </div>

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
