// Derives the Version & Deployment Overview entirely from the existing
// sources of truth — data/mock/deployments.js (version/deployment facts,
// keyed by siteId) and data/mock/sites.js (site identity). Nothing here
// duplicates site identity; every summary/distribution value is computed
// so it stays consistent with the underlying dataset rather than being a
// separately hardcoded number.
import { ref, computed } from 'vue';
import { sites } from '../data/mock/sites';
import { deployments } from '../data/mock/deployments';

const VERSION_STATUS_OPTIONS = [
    { value: 'all', label: 'All' },
    { value: 'latest', label: 'Latest' },
    { value: 'update_available', label: 'Update Available' },
    { value: 'behind', label: 'Behind' },
];

const DEPLOYMENT_STATUS_OPTIONS = [
    { value: 'all', label: 'All' },
    { value: 'healthy', label: 'Healthy' },
    { value: 'warning', label: 'Warning' },
    { value: 'failed', label: 'Failed' },
];

// versionStatus is derived here, not stored in deployments.js, so it can
// never drift out of sync with currentVersion/targetVersion.
//
// Rule (deterministic, based on the [major, minor] release line only —
// patch differences don't affect status):
// - identical string to targetVersion            -> 'latest'
// - same minor line as target, or exactly one     -> 'update_available'
//   minor release behind (e.g. target 2.5, site on 2.4.x)
// - two or more minor releases behind target      -> 'behind'
//   (e.g. target 2.5, site on 2.3.x or older)
function parseVersion(version) {
    const [major, minor, patch] = version.replace(/^v/, '').split('.').map(Number);
    return { major, minor, patch };
}

function resolveVersionStatus(currentVersion, targetVersion) {
    if (currentVersion === targetVersion) return 'latest';

    const current = parseVersion(currentVersion);
    const target = parseVersion(targetVersion);
    const minorGap = (target.major * 100 + target.minor) - (current.major * 100 + current.minor);

    return minorGap <= 1 ? 'update_available' : 'behind';
}

export function useDeploymentsOverview() {
    const search = ref('');
    const versionStatusFilter = ref('all');
    const deploymentStatusFilter = ref('all');

    // Join deployment records with their site identity, resolved from
    // sites.js by siteId — deployments.js never carries code/name/location.
    // versionStatus is computed fresh from currentVersion/targetVersion
    // here rather than trusted from stored data.
    const records = computed(() =>
        deployments
            .map((deployment) => {
                const site = sites.find((s) => s.id === deployment.siteId);
                if (!site) return null;
                return {
                    ...deployment,
                    site,
                    versionStatus: resolveVersionStatus(deployment.currentVersion, deployment.targetVersion),
                };
            })
            .filter(Boolean)
    );

    const summary = computed(() => {
        const list = records.value;
        return {
            totalSites: list.length,
            latest: list.filter((r) => r.versionStatus === 'latest').length,
            updateAvailable: list.filter((r) => r.versionStatus === 'update_available').length,
            behind: list.filter((r) => r.versionStatus === 'behind').length,
            deploymentIssues: list.filter(
                (r) => r.deploymentStatus === 'warning' || r.deploymentStatus === 'failed'
            ).length,
        };
    });

    const versionDistribution = computed(() => {
        const counts = new Map();
        for (const record of records.value) {
            counts.set(record.currentVersion, (counts.get(record.currentVersion) ?? 0) + 1);
        }
        return Array.from(counts.entries())
            .map(([version, count]) => ({ version, count }))
            .sort((a, b) => b.count - a.count || b.version.localeCompare(a.version));
    });

    const filteredRecords = computed(() => {
        const term = search.value.trim().toLowerCase();

        return records.value.filter((record) => {
            const matchesSearch =
                !term ||
                record.site.code.toLowerCase().includes(term) ||
                record.site.name.toLowerCase().includes(term);

            const matchesVersion =
                versionStatusFilter.value === 'all' || record.versionStatus === versionStatusFilter.value;

            const matchesDeployment =
                deploymentStatusFilter.value === 'all' ||
                record.deploymentStatus === deploymentStatusFilter.value;

            return matchesSearch && matchesVersion && matchesDeployment;
        });
    });

    const recentActivity = computed(() =>
        [...records.value].sort((a, b) => a.recencyRank - b.recencyRank).slice(0, 6)
    );

    return {
        search,
        versionStatusFilter,
        deploymentStatusFilter,
        versionStatusOptions: VERSION_STATUS_OPTIONS,
        deploymentStatusOptions: DEPLOYMENT_STATUS_OPTIONS,
        records,
        summary,
        versionDistribution,
        filteredRecords,
        recentActivity,
    };
}
