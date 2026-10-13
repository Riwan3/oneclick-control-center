// API Integration business logic — Page -> Composable -> mock data,
// the same layering useKpiCatalogue.js already uses (no backend exists
// for this domain yet, same as KPI). Returns FOUNDATION/READINESS
// data only — never a real connection result or fabricated telemetry.
import { computed } from 'vue';
import { sites as allSites } from '../data/siteCatalogue';
import { useEffectiveAccess } from './useEffectiveAccess';
import {
    controlCenterApiServices,
    apiConfigurationState,
    API_INTEGRATION_LABEL,
    API_INTEGRATION_VARIANT,
} from '../data/mock/apiIntegration';

export function useApiIntegration() {
    const { canAccessSite } = useEffectiveAccess();

    // Reuses the existing access-scope rule everywhere else already
    // does — no separate site enumeration logic.
    const accessibleSites = computed(() => allSites.filter((site) => canAccessSite(site.code)));

    // Real connections: every accessible site that has a token (push) or a
    // Site URL (pull), derived from the site catalogue — never invented.
    const siteConnections = computed(() =>
        accessibleSites.value
            .filter((site) => site.connected)
            .map((site) => ({
                id: site.dbId,
                siteCode: site.code,
                apiName: site.mode === 'pull' ? 'OneClick Site API (pull)' : 'OneClick Site Report (push)',
                baseUrl: site.baseUrl || null,
                authenticationMethod: 'Bearer Token',
                configurationStatus: 'configured',
                lastTested: site.lastCheckedAt,
            }))
    );
    const services = computed(() => controlCenterApiServices);
    const configuration = computed(() => apiConfigurationState);

    const configuredSiteConnections = computed(
        () => siteConnections.value.filter((c) => c.configurationStatus === 'configured').length
    );
    const configuredServices = computed(() => services.value.filter((s) => s.status === 'configured').length);

    // Executive API Summary — 4 real, derived counts (never fabricated
    // values): how many site connections exist, how many Control
    // Center services are catalogued, how many of either are actually
    // configured, and how much configuration work remains.
    const summary = computed(() => ({
        siteApis: siteConnections.value.length,
        controlCenterApis: services.value.length,
        configuredIntegrations: configuredSiteConnections.value + configuredServices.value,
        pendingConfiguration:
            accessibleSites.value.length - configuredSiteConnections.value + (services.value.length - configuredServices.value),
    }));

    return {
        accessibleSites,
        siteConnections,
        services,
        configuration,
        summary,
        statusLabel: API_INTEGRATION_LABEL,
        statusVariant: API_INTEGRATION_VARIANT,
    };
}
