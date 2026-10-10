// KPI CATALOGUE — structural KPI DEFINITIONS and READINESS only. This is
// deliberately NOT telemetry and NOT performance data (see STEP 8 of the
// KPI Catalogue Architecture task: definition != telemetry != performance).
//
// `kpiCatalogue` is intentionally empty. No confirmed business KPI
// specification or telemetry contract exists in this repository yet —
// same reasoning baselineRegistry.js applies to LEGACY_MODULE_MAPPING
// (mark unconfirmed things unconfirmed rather than inventing them).
// Inventing KPI names/formulas/targets here would misrepresent
// readiness as if real operational metrics had already been agreed.
// Once a real KPI spec exists, populate this array using the shape
// documented below — everything else in this file (readiness states,
// per-module readiness) already works against that shape.
//
// Record shape once populated:
// {
//   key,          // stable identifier, e.g. 'one-power-utilization'
//   name,         // display name
//   domain,       // human-readable KPI domain/category
//   moduleKey,    // one of config/modules.js's canonical module keys
//   definition,   // what the KPI measures
//   formula,      // deterministic calculation logic
//   unit,         // '%', 'hours', 'count', ...
//   target,       // 'defined' | 'pending' | 'not_configured' — never a
//                 // fabricated numeric target without a confirmed source
//   frequency,    // 'real_time' | 'daily' | 'monthly' | ...
//   dataSource,   // operational telemetry / API endpoint feed
//   siteScope,    // 'all' | 'selected' | site code
//   owner,        // domain governance lead
//   readiness,    // one of KPI_READINESS_STATES below
// }
export const kpiCatalogue = [];

// Explicit readiness vocabulary — never substitute "0 performance" for
// unavailable telemetry (STEP 3).
export const KPI_READINESS_STATES = {
    CONNECTED: 'connected',
    TELEMETRY_PENDING: 'telemetry_pending',
    NOT_CONFIGURED: 'not_configured',
    UNAVAILABLE: 'unavailable',
};

export const KPI_READINESS_LABEL = {
    connected: 'Connected',
    telemetry_pending: 'Telemetry Pending',
    not_configured: 'Not Configured',
    unavailable: 'Unavailable',
};

export const KPI_READINESS_VARIANT = {
    connected: 'success',
    telemetry_pending: 'neutral',
    not_configured: 'warning',
    unavailable: 'danger',
};

// Aggregate readiness for one module's KPI domain. A module with zero
// catalogue records is reported as TELEMETRY_PENDING rather than
// NOT_CONFIGURED — STEP 4: "if a domain is not defined, represent it
// as Telemetry Pending rather than inventing KPI definitions." A
// domain is only ever CONNECTED once every one of its KPI records is.
export function getModuleKpiReadiness(moduleKey) {
    const domainKpis = kpiCatalogue.filter((kpi) => kpi.moduleKey === moduleKey);

    if (!domainKpis.length) {
        return KPI_READINESS_STATES.TELEMETRY_PENDING;
    }
    if (domainKpis.every((kpi) => kpi.readiness === KPI_READINESS_STATES.CONNECTED)) {
        return KPI_READINESS_STATES.CONNECTED;
    }
    if (domainKpis.some((kpi) => kpi.readiness === KPI_READINESS_STATES.UNAVAILABLE)) {
        return KPI_READINESS_STATES.UNAVAILABLE;
    }
    return KPI_READINESS_STATES.TELEMETRY_PENDING;
}
