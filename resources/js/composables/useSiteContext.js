import { ref, computed } from 'vue';
import { sites as allSites } from '../data/siteCatalogue';

const ALL_SITES_OPTION = { key: 'all', name: 'All Sites', code: 'ALL' };

// Reactive state declared at module scope to persist selection across navigation in current session
const selectedSiteKey = ref('all');

export function useSiteContext() {
    const availableSites = computed(() => [
        ALL_SITES_OPTION,
        ...allSites.map((site) => ({
            key: site.code,
            name: site.name,
            code: site.code,
            location: site.location,
            status: site.status,
        })),
    ]);

    const selectedSite = computed(() => {
        if (selectedSiteKey.value === 'all') {
            return ALL_SITES_OPTION;
        }
        return availableSites.value.find((s) => s.key === selectedSiteKey.value) ?? ALL_SITES_OPTION;
    });

    function selectSite(siteKey) {
        selectedSiteKey.value = siteKey;
    }

    function resetSite() {
        selectedSiteKey.value = 'all';
    }

    return {
        availableSites,
        selectedSite,
        selectedSiteKey,
        selectSite,
        resetSite,
    };
}
