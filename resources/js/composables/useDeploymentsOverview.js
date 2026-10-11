// Derives the Version & Deployment Overview from real data: each
// connected site's reported OneClick version (data/siteCatalogue.js) and
// the Release catalogue (GET /api/releases) for the target version.
// Sites with no connection have no version and are not listed.
// Environment and last-deployed time are reported by the site itself
// (APP_ENV and the time its current build was produced); there is no
// per-deployment history or duration source.
import { ref, computed, onMounted } from 'vue';
import { sites as allSites } from '../data/siteCatalogue';
import { releaseService } from '../services/releaseService';
import { useEffectiveAccess } from './useEffectiveAccess';

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
    const [major = 0, minor = 0, patch = 0] = version.replace(/^v/, '').split('.').map((n) => Number(n) || 0);
    return { major, minor, patch };
}

function compareVersions(a, b) {
    const x = parseVersion(a);
    const y = parseVersion(b);
    return x.major - y.major || x.minor - y.minor || x.patch - y.patch;
}

function highestVersion(versions) {
    return versions.reduce((max, v) => (max === null || compareVersions(v, max) > 0 ? v : max), null);
}

// Site status -> deployment health: a site we cannot reach counts as failed.
const DEPLOYMENT_STATUS_BY_SITE_STATUS = { online: 'healthy', warning: 'warning', offline: 'failed' };

// Latest released version, shared across callers. Roles without access to
// /api/releases simply leave this null and fall back to the highest
// version seen on any site.
const releasedVersion = ref(null);

async function loadReleasedVersion() {
    const { ok, data } = await releaseService.fetchReleases();
    if (!ok) return;
    releasedVersion.value = highestVersion(
        (data?.data ?? []).filter((r) => r.status === 'released').map((r) => r.version)
    );
}

function resolveVersionStatus(currentVersion, targetVersion) {
    if (currentVersion === targetVersion) return 'latest';

    const current = parseVersion(currentVersion);
    const target = parseVersion(targetVersion);
    const minorGap = (target.major * 100 + target.minor) - (current.major * 100 + current.minor);

    return minorGap <= 1 ? 'update_available' : 'behind';
}

export function useDeploymentsOverview() {
    const { canAccessSite } = useEffectiveAccess();

    const search = ref('');
    const versionStatusFilter = ref('all');
    const deploymentStatusFilter = ref('all');

    // EFFECTIVE ACCESS: management/view only see deployment records for
    // sites they were explicitly granted access to.
    const sites = computed(() => allSites.filter((site) => canAccessSite(site.code)));

    onMounted(loadReleasedVersion);

    const targetVersion = computed(
        () => releasedVersion.value ?? highestVersion(sites.value.filter((s) => s.connected).map((s) => s.version))
    );

    // One record per connected site that has reported a version.
    const records = computed(() =>
        sites.value
            .filter((site) => site.connected && site.version !== '—' && targetVersion.value)
            .map((site) => ({
                siteId: site.id,
                site,
                currentVersion: site.version,
                targetVersion: targetVersion.value,
                versionStatus: resolveVersionStatus(site.version, targetVersion.value),
                deploymentStatus: DEPLOYMENT_STATUS_BY_SITE_STATUS[site.status] ?? 'warning',
                environment: site.environment,
                lastDeployedAt: site.deployedAt,
                lastCheckedAt: site.lastCheckedAt,
            }))
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
        [...records.value].sort((a, b) => (b.lastCheckedAt ?? '').localeCompare(a.lastCheckedAt ?? '')).slice(0, 6)
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
