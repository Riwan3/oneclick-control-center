// KPI Catalogue business logic — Page -> Composable -> mock data, the
// same layering useModulesOverview.js/useHomeDashboard.js already use
// (no backend exists for this feature yet, same as those two).
//
// Returns CATALOGUE + READINESS only — never telemetry or performance
// values (see kpiCatalogue.js's header for why the catalogue is
// currently empty).
import { computed } from 'vue';
import { modules as canonicalModules } from '../config/modules';
import { sites as allSites } from '../data/siteCatalogue';
import {
    kpiCatalogue,
    KPI_READINESS_STATES,
    KPI_READINESS_LABEL,
    KPI_READINESS_VARIANT,
    getModuleKpiReadiness,
} from '../data/mock/kpiCatalogue';
import { useEffectiveAccess } from './useEffectiveAccess';

export function useKpiCatalogue() {
    const { canAccessSite } = useEffectiveAccess();

    const accessibleSites = computed(() => allSites.filter((site) => canAccessSite(site.code)));

    const catalogue = computed(() => kpiCatalogue);

    const connectedKpis = computed(() =>
        catalogue.value.filter((kpi) => kpi.readiness === KPI_READINESS_STATES.CONNECTED)
    );
    const telemetryPendingKpis = computed(() =>
        catalogue.value.filter((kpi) => kpi.readiness === KPI_READINESS_STATES.TELEMETRY_PENDING)
    );

    // Every canonical module is represented, even though every domain
    // currently resolves to telemetry_pending — STEP 4: all 8 modules
    // stay visible regardless of catalogue population.
    const moduleDomains = computed(() =>
        canonicalModules.map((mod) => ({
            ...mod,
            readiness: getModuleKpiReadiness(mod.key),
        }))
    );

    const summary = computed(() => ({
        catalogueTotal: catalogue.value.length,
        connected: connectedKpis.value.length,
        telemetryPending: telemetryPendingKpis.value.length,
        configuredSites: accessibleSites.value.length,
        canonicalModules: canonicalModules.length,
    }));

    return {
        catalogue,
        moduleDomains,
        summary,
        readinessLabel: KPI_READINESS_LABEL,
        readinessVariant: KPI_READINESS_VARIANT,
    };
}
