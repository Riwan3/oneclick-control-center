// Derives the Module Ecosystem Overview entirely from the existing
// sources of truth — config/modules.js (module catalogue) and
// data/mock/sites.js (per-site module flags). Nothing here duplicates
// or hardcodes adoption numbers; every count is computed so it stays
// consistent with the Home dashboard, Sites, and Site Detail pages,
// which derive their own module figures from the same sites.js records.
import { ref, computed } from 'vue';
import { modules as moduleCatalog } from '../config/modules';
import { sites } from '../data/mock/sites';

function round1(value) {
    return Math.round(value * 10) / 10;
}

export function useModulesOverview() {
    const search = ref('');
    const selectedModule = ref('all');

    const totalSites = sites.length;

    const modulesWithAdoption = computed(() =>
        moduleCatalog.map((mod) => {
            const activeSites = sites.filter((site) => Boolean(site.modules?.[mod.key])).length;
            const inactiveSites = totalSites - activeSites;
            const adoption = totalSites ? round1((activeSites / totalSites) * 100) : 0;

            return {
                ...mod,
                activeSites,
                inactiveSites,
                totalSites,
                adoption,
            };
        })
    );

    const summary = computed(() => {
        const activeDeployments = modulesWithAdoption.value.reduce(
            (sum, mod) => sum + mod.activeSites,
            0
        );
        const totalPossible = moduleCatalog.length * totalSites;

        return {
            totalModules: moduleCatalog.length,
            totalSites,
            activeDeployments,
            overallAdoption: totalPossible ? round1((activeDeployments / totalPossible) * 100) : 0,
        };
    });

    const filteredSites = computed(() => {
        const term = search.value.trim().toLowerCase();

        return sites.filter((site) => {
            if (!term) return true;

            return (
                site.code.toLowerCase().includes(term) ||
                site.name.toLowerCase().includes(term) ||
                site.location.toLowerCase().includes(term)
            );
        });
    });

    const visibleModules = computed(() =>
        selectedModule.value === 'all'
            ? modulesWithAdoption.value
            : modulesWithAdoption.value.filter((mod) => mod.key === selectedModule.value)
    );

    return {
        search,
        selectedModule,
        moduleCatalog,
        modules: modulesWithAdoption,
        visibleModules,
        summary,
        filteredSites,
    };
}
