// Mock data for the Version & Deployment Overview (Deployments.vue).
//
// This dataset owns ONLY deployment/version-specific information. Site
// identity (code, name, location) is never duplicated here — it is
// always resolved from data/mock/sites.js by `siteId` in
// composables/useDeploymentsOverview.js.
//
// VERSION SOURCE OF TRUTH (STEP 3L correction): "version" means the
// OneClick application version deployed at a site, and there is only one
// lineage for it — data/mock/sites.js `version`. `currentVersion` below
// is therefore always the exact same string as the corresponding site's
// `sites.js` `version` field (e.g. 'v2.4.1'), not a second, independently
// authored number. `versionStatus` is NOT stored here — it is derived in
// useDeploymentsOverview.js from currentVersion vs targetVersion, so it
// can never drift out of sync with the source data.
//
// deploymentStatus: 'healthy' | 'warning' | 'failed'
//
// All values are deterministic constants — no Math.random(), no timers,
// no generated ids. `recencyRank` is a plain sort key (1 = most recent)
// standing in for real timestamps, so "recent activity" can be ordered
// without parsing the display strings in `lastDeployedAt`.
//
// TARGET_VERSION is the next OneClick platform release beyond the
// highest version currently deployed anywhere in sites.js (v2.4.1), i.e.
// a plausible upcoming rollout target — not one already running on any
// site today. See the STEP 3L correction report for how this was chosen.
const TARGET_VERSION = 'v2.5.0';

export const deployments = [
    {
        siteId: 'bib',
        currentVersion: 'v2.4.1',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 09:12',
        deploymentDuration: '4m 12s',
        recencyRank: 2,
    },
    {
        siteId: 'mhu',
        currentVersion: 'v2.4.1',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 08:47',
        deploymentDuration: '3m 48s',
        recencyRank: 5,
    },
    {
        siteId: 'sks',
        currentVersion: 'v2.3.8',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'warning',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 07:55',
        deploymentDuration: '5m 02s',
        recencyRank: 8,
    },
    {
        siteId: 'wara',
        currentVersion: 'v2.4.1',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 08:30',
        deploymentDuration: '4m 05s',
        recencyRank: 6,
    },
    {
        siteId: 'mip',
        currentVersion: 'v2.4.0',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '23 Aug 2026, 17:05',
        deploymentDuration: '3m 55s',
        recencyRank: 13,
    },
    {
        siteId: 'ba',
        currentVersion: 'v2.2.5',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'failed',
        environment: 'production',
        lastDeployedAt: '20 Aug 2026, 14:12',
        deploymentDuration: '2m 10s',
        recencyRank: 14,
    },
    {
        siteId: 'mifa',
        currentVersion: 'v2.4.1',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 09:00',
        deploymentDuration: '4m 20s',
        recencyRank: 3,
    },
    {
        siteId: 'bcp',
        currentVersion: 'v2.3.5',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'warning',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 06:40',
        deploymentDuration: '5m 30s',
        recencyRank: 10,
    },
    {
        siteId: 'ami',
        currentVersion: 'v2.4.1',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 09:15',
        deploymentDuration: '3m 40s',
        recencyRank: 1,
    },
    {
        siteId: 'mlp',
        currentVersion: 'v2.4.0',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 08:10',
        deploymentDuration: '4m 00s',
        recencyRank: 7,
    },
    {
        siteId: 'pik',
        currentVersion: 'v2.4.1',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '23 Aug 2026, 19:30',
        deploymentDuration: '3m 50s',
        recencyRank: 12,
    },
    {
        siteId: 'bge',
        currentVersion: 'v2.3.9',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 07:20',
        deploymentDuration: '4m 45s',
        recencyRank: 9,
    },
    {
        siteId: 'sbs',
        currentVersion: 'v2.3.5',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'warning',
        environment: 'production',
        lastDeployedAt: '23 Aug 2026, 22:05',
        deploymentDuration: '5m 15s',
        recencyRank: 11,
    },
    {
        siteId: 'kes',
        currentVersion: 'v2.4.1',
        targetVersion: TARGET_VERSION,
        deploymentStatus: 'healthy',
        environment: 'production',
        lastDeployedAt: '24 Aug 2026, 08:55',
        deploymentDuration: '3m 58s',
        recencyRank: 4,
    },
];
