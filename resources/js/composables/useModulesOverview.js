// Derives the Module Ecosystem Overview entirely from the existing
// sources of truth — config/modules.js (module catalogue),
// useSiteConfiguration.js (governance policy), and data/mock/sites.js
// (per-site operational telemetry).
import { ref, computed } from 'vue';
import { modules as allModules } from '../config/modules';
import { sites as allSites } from '../data/mock/sites';
import { useEffectiveAccess } from './useEffectiveAccess';
import { useSiteConfiguration } from './useSiteConfiguration';
import { resolveEffectiveModuleStatus } from '../helpers/effectiveModuleStatus';

function round1(value) {
    return Math.round(value * 10) / 10;
}

export function useModulesOverview() {
    const { canAccessSite, canAccessModule } = useEffectiveAccess();
    const { sites: configSites } = useSiteConfiguration();

    const search = ref('');
    const selectedModule = ref('all');

    const sites = computed(() => allSites.filter((site) => canAccessSite(site.code)));
    const moduleCatalog = computed(() => allModules.filter((mod) => canAccessModule(mod.key)));

    const totalSites = computed(() => sites.value.length);

    // Map site configuration overrides by site code
    function getSiteModuleEnabled(siteCode, moduleKey) {
        const foundSite = configSites.value.find((s) => s.siteCode === siteCode);
        if (!foundSite?.summary) return true; // Async fallback to baseline default (enabled)
        // If site is present in configSites, check summary or selected site modules if available
        // Note: configSites provides summary and siteId; find raw module configuration:
        const modSummary = foundSite.summary;
        // In useSiteConfiguration, effectiveSiteModules resolves siteEnabled per module
        // We can inspect whether the site has moduleOverrides for this moduleKey
        const overrides = new Map((foundSite.moduleOverrides ?? []).map((o) => [o.moduleKey, o.enabled]));
        return overrides.has(moduleKey) ? overrides.get(moduleKey) : true;
    }

    const sitesWithEffectiveModules = computed(() =>
        sites.value.map((site) => {
            const effectiveModules = {};
            for (const mod of moduleCatalog.value) {
                const siteEnabled = getSiteModuleEnabled(site.code, mod.key);
                const deployed = Boolean(site.modules?.[mod.key]);
                const effectiveStatus = resolveEffectiveModuleStatus({ siteEnabled, deployed });

                effectiveModules[mod.key] = {
                    siteEnabled,
                    deployed,
                    effectiveStatus,
                };
            }
            return {
                ...site,
                effectiveModules,
            };
        })
    );

    const modulesWithAdoption = computed(() =>
        moduleCatalog.value.map((mod) => {
            const siteStates = sitesWithEffectiveModules.value.map((s) => s.effectiveModules[mod.key]);

            const configuredSites = siteStates.filter((s) => s?.siteEnabled).length;
            const rawActiveSites = siteStates.filter((s) => s?.deployed).length;
            const effectiveActiveSites = siteStates.filter((s) => s?.effectiveStatus === 'active').length;
            const pendingDeploymentSites = siteStates.filter((s) => s?.effectiveStatus === 'pending').length;
            const policyViolationSites = siteStates.filter((s) => s?.effectiveStatus === 'policy_violation').length;

            const inactiveSites = totalSites.value - rawActiveSites;
            const adoption = totalSites.value ? round1((effectiveActiveSites / totalSites.value) * 100) : 0;

            return {
                ...mod,
                configuredSites,
                rawActiveSites,
                effectiveActiveSites,
                pendingDeploymentSites,
                policyViolationSites,
                activeSites: rawActiveSites, // Backward compatibility
                inactiveSites,
                totalSites: totalSites.value,
                adoption,
            };
        })
    );

    const summary = computed(() => {
        const rawActiveDeployments = modulesWithAdoption.value.reduce(
            (sum, mod) => sum + mod.rawActiveSites,
            0
        );
        const effectiveActiveDeployments = modulesWithAdoption.value.reduce(
            (sum, mod) => sum + mod.effectiveActiveSites,
            0
        );
        const policyViolations = modulesWithAdoption.value.reduce(
            (sum, mod) => sum + mod.policyViolationSites,
            0
        );
        const totalPossible = moduleCatalog.value.length * totalSites.value;

        return {
            totalModules: moduleCatalog.value.length,
            totalSites: totalSites.value,
            activeDeployments: rawActiveDeployments, // Backward compatibility
            rawActiveDeployments,
            effectiveActiveDeployments,
            policyViolations,
            overallAdoption: totalPossible ? round1((effectiveActiveDeployments / totalPossible) * 100) : 0,
        };
    });

    const filteredSites = computed(() => {
        const term = search.value.trim().toLowerCase();

        return sitesWithEffectiveModules.value.filter((site) => {
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
