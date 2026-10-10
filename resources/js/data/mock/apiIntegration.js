// API INTEGRATION — structural UI/UX foundation only. No real API
// connection exists anywhere in this repository yet (see "PHASE — API
// INTEGRATION UI/UX FOUNDATION ONLY"). Mirrors the same pattern already
// established by kpiCatalogue.js: explicit, honest state vocabulary,
// zero fabricated telemetry, and a documented shape for future
// population once a real Site API / Control Center API is built.
//
// Never add: HTTP response codes, latency, uptime, synchronization
// timestamps, request counts, error rates, or "successful connection"
// results. Every field below is either empty, structurally defined
// (not telemetry), or explicitly 'pending'/'not_configured'/'not_tested'.

export const API_INTEGRATION_STATES = {
    NOT_CONFIGURED: 'not_configured',
    PENDING: 'pending',
    CONFIGURED: 'configured',
    NOT_TESTED: 'not_tested',
};

export const API_INTEGRATION_LABEL = {
    not_configured: 'Not Configured',
    pending: 'Pending',
    configured: 'Configured',
    not_tested: 'Not Tested',
};

// success = actual positive/ready state only; info = pending/informational;
// neutral = not configured / not tested / unavailable — never amber/red
// for an ordinary "not configured yet" foundation state.
export const API_INTEGRATION_VARIANT = {
    not_configured: 'neutral',
    pending: 'info',
    configured: 'success',
    not_tested: 'neutral',
};

// Site -> Control Center API connections (APIs coming FROM individual
// sites). Empty: no site has been configured to expose an API to the
// Control Center yet.
//
// Record shape once populated:
// { id, siteCode, apiName, baseUrl, authenticationMethod,
//   configurationStatus, lastTested }
export const siteApiConnections = [];

// Control Center -> Site API service catalogue (APIs exposed BY the
// Control Center). These 4 services are structural definitions — they
// mirror domains that already exist inside this Control Center — but
// none is published as an external endpoint yet. This is not telemetry;
// it is a fixed catalogue, the same kind of structural record
// kpiCatalogue.js documents for KPIs.
export const controlCenterApiServices = [
    { key: 'site-configuration', name: 'Site Configuration', status: 'pending', version: 'v1', endpoint: 'Not Published' },
    { key: 'module-configuration', name: 'Module Configuration', status: 'pending', version: 'v1', endpoint: 'Not Published' },
    { key: 'user-access', name: 'User & Access', status: 'pending', version: 'v1', endpoint: 'Not Published' },
    { key: 'kpi-catalogue', name: 'KPI Catalogue', status: 'pending', version: 'v1', endpoint: 'Not Published' },
];

// Global API configuration record — represents the current
// not-yet-configured connection/authentication/integration state.
// Every field is null or an explicit pending/not-tested/not-available
// state; nothing here is a real credential, date, or connection result.
export const apiConfigurationState = {
    environment: null,
    baseUrl: null,
    timeout: null,
    authenticationMethod: null,
    configurationStatus: 'not_configured',
    connectionStatus: 'not_tested',
    lastTest: null,
    lastSynchronization: null,
};
