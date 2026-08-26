// Derives the Module Ecosystem Overview entirely from the existing
// sources of truth — config/modules.js (module catalogue) and
// data/mock/sites.js (per-site module flags). Nothing here duplicates
// or hardcodes adoption numbers; every count is computed so it stays
// consistent with the Home dashboard, Sites, and Site Detail pages,
// which derive their own module figures from the same sites.js records.
import { ref, computed } from 'vue';
import { modules as allModules } from '../config/modules';
import { sites as allSites } from '../data/mock/sites';
import { useEffectiveAccess } from './useEffectiveAccess';

function round1(value) {
    return Math.round(value * 10) / 10;
}

export function useModulesOverview() {
    const { canAccessSite, canAccessModule } = useEffectiveAccess();

    const search = ref('');
    const selectedModule = ref('all');

    // EFFECTIVE ACCESS: management/view only ever see the sites and
    // modules they were explicitly granted — adoption figures, the
    // module catalogue buttons, and the site matrix are all derived
    // from these two filtered lists, so no unauthorized site/module
    // data can leak through a derived count.
    const sites = computed(() => allSites.filter((site) => canAccessSite(site.code)));
    const moduleCatalog = computed(() => allModules.filter((mod) => canAccessModule(mod.key)));

    const totalSites = computed(() => sites.value.length);

    const modulesWithAdoption = computed(() =>
        moduleCatalog.value.map((mod) => {
            const activeSites = sites.value.filter((site) => Boolean(site.modules?.[mod.key])).length;
            const inactiveSites = totalSites.value - activeSites;
            const adoption = totalSites.value ? round1((activeSites / totalSites.value) * 100) : 0;

            return {
                ...mod,
                activeSites,
                inactiveSites,
                totalSites: totalSites.value,
                adoption,
            };
        })
    );

    const summary = computed(() => {
        const activeDeployments = modulesWithAdoption.value.reduce(
            (sum, mod) => sum + mod.activeSites,
            0
        );
        const totalPossible = moduleCatalog.value.length * totalSites.value;

        return {
            totalModules: moduleCatalog.value.length,
            totalSites: totalSites.value,
            activeDeployments,
            overallAdoption: totalPossible ? round1((activeDeployments / totalPossible) * 100) : 0,
        };
    });

    const filteredSites = computed(() => {
        const term = search.value.trim().toLowerCase();

        return sites.value.filter((site) => {
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
