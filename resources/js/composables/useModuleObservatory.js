// Module Observatory business logic — Page -> Composable -> Service
// (see moduleObservatoryService.js). No API calls, no business logic in
// ModuleObservatory.vue itself.
import { ref, computed, onMounted } from 'vue';
import { sites as allSites } from '../data/mock/sites';
import { moduleObservatoryService } from '../services/moduleObservatoryService';
import { useEffectiveAccess } from './useEffectiveAccess';

/**
 * Deterministic comparison between a baseline menu manifest and a
 * site's current manifest, matched by the stable `id` field (not
 * label/route — a change to either of those, on the same id, is
 * exactly what "modified" needs to detect). No timestamps, no
 * Math.random(); a given pair of manifests always diffs the same way.
 *
 * @return {{added: array, removed: array, modified: array<{baseline, current}>}}
 */
export function diffMenus(baselineMenus, currentMenus) {
    if (!currentMenus) {
        return { added: [], removed: [], modified: [] };
    }

    const baselineById = new Map(baselineMenus.map((menu) => [menu.id, menu]));
    const currentById = new Map(currentMenus.map((menu) => [menu.id, menu]));

    const added = currentMenus.filter((menu) => !baselineById.has(menu.id));
    const removed = baselineMenus.filter((menu) => !currentById.has(menu.id));

    const modified = [];
    for (const baselineMenu of baselineMenus) {
        const currentMenu = currentById.get(baselineMenu.id);
        if (!currentMenu) continue;

        const changed =
            baselineMenu.label !== currentMenu.label ||
            baselineMenu.route !== currentMenu.route ||
            baselineMenu.parentId !== currentMenu.parentId ||
            baselineMenu.order !== currentMenu.order;

        if (changed) {
            modified.push({ baseline: baselineMenu, current: currentMenu });
        }
    }

    return { added, removed, modified };
}

/**
 * synced / updated / changed / drifted / not_synced — deterministic:
 * - no manifest received at all              -> not_synced
 * - any removed menu                         -> drifted (losing a menu
 *   is treated as the most significant structural regression)
 * - any added/modified menu (no removals)    -> changed
 * - version differs from baseline, manifest otherwise identical -> updated
 * - otherwise                                -> synced
 */
export function resolveSyncStatus({ hasManifest, version, baselineVersion, diff }) {
    if (!hasManifest) return 'not_synced';
    if (diff.removed.length > 0) return 'drifted';
    if (diff.added.length > 0 || diff.modified.length > 0) return 'changed';
    if (version !== baselineVersion) return 'updated';
    return 'synced';
}

const STATUS_PRIORITY = ['drifted', 'changed', 'not_synced', 'updated', 'synced'];

export function useModuleObservatory(moduleKey = 'one-power') {
    const { canAccessSite } = useEffectiveAccess();

    const raw = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const selectedSiteId = ref(null);

    async function load() {
        loading.value = true;
        error.value = null;
        try {
            raw.value = await moduleObservatoryService.getOverview(moduleKey);
        } catch {
            error.value = 'Unable to load module observatory data.';
        } finally {
            loading.value = false;
        }
    }

    const baseline = computed(() => raw.value?.baseline ?? null);

    // Every site id this module is monitored at, BEFORE effective-access
    // filtering — used only to distinguish "exists but you can't see
    // it" from "not monitored at all" for the page's direct-site-request
    // guard. Never exposes any site's manifest/version data itself.
    const monitoredSiteIds = computed(() => raw.value?.sites.map((entry) => entry.siteId) ?? []);

    // EFFECTIVE ACCESS: a site the user isn't allowed to see is dropped
    // here — it never reaches the sites list, the summary counts, the
    // version distribution, or the site-comparison table, and it can
    // never be selected via selectSite() either (see below). Reuses
    // useEffectiveAccess() — no separate access rule.
    const sites = computed(() => {
        if (!raw.value) return [];

        return raw.value.sites
            .map((entry) => ({ entry, identity: allSites.find((site) => site.id === entry.siteId) }))
            // canAccessSite() compares against the effective-access
            // siteCodes (uppercase, e.g. 'BIB') — NOT the mock siteId
            // ('bib'). A site with no matching sites.js identity can
            // never be resolved to a code, so it fails safe (excluded).
            .filter(({ identity }) => identity && canAccessSite(identity.code))
            .map(({ entry, identity }) => {
                const hasManifest = Boolean(entry.menus);
                const diff = hasManifest
                    ? diffMenus(baseline.value.menus, entry.menus)
                    : { added: [], removed: [], modified: [] };
                const syncStatus = resolveSyncStatus({
                    hasManifest,
                    version: entry.version,
                    baselineVersion: baseline.value.version,
                    diff,
                });

                return {
                    siteId: entry.siteId,
                    siteCode: identity.code,
                    siteName: identity.name,
                    version: entry.version,
                    menus: entry.menus,
                    diff,
                    syncStatus,
                };
            })
            .sort((a, b) => a.siteName.localeCompare(b.siteName));
    });

    const summary = computed(() => {
        const list = sites.value;
        const totalConfigurationChanges = list.reduce(
            (sum, site) => sum + site.diff.added.length + site.diff.removed.length + site.diff.modified.length,
            0
        );

        return {
            sitesMonitored: list.length,
            synced: list.filter((site) => site.syncStatus === 'synced').length,
            updated: list.filter((site) => site.syncStatus === 'updated').length,
            changed: list.filter((site) => site.syncStatus === 'changed').length,
            drifted: list.filter((site) => site.syncStatus === 'drifted').length,
            notSynced: list.filter((site) => site.syncStatus === 'not_synced').length,
            totalConfigurationChanges,
        };
    });

    const versionDistribution = computed(() => {
        const counts = new Map();
        for (const site of sites.value) {
            if (!site.version) continue;
            counts.set(site.version, (counts.get(site.version) ?? 0) + 1);
        }
        return Array.from(counts.entries())
            .map(([version, count]) => ({ version, count }))
            .sort((a, b) => b.count - a.count || b.version.localeCompare(a.version));
    });

    const latestVersion = computed(() => {
        const versions = sites.value.map((site) => site.version).filter(Boolean);
        if (!versions.length) return baseline.value?.version ?? null;
        return [...versions].sort().at(-1);
    });

    const overallStatus = computed(() => {
        const list = sites.value;
        if (!list.length) return 'not_synced';
        const present = new Set(list.map((site) => site.syncStatus));
        return STATUS_PRIORITY.find((status) => present.has(status)) ?? 'synced';
    });

    // The first accessible site is selected by default. Selecting an id
    // that isn't in the (already access-filtered) `sites` list — e.g. a
    // hand-edited query param — is simply ignored, so selectedSite can
    // never resolve to an unauthorized site's manifest.
    const selectedSite = computed(() => {
        if (!sites.value.length) return null;
        return sites.value.find((site) => site.siteId === selectedSiteId.value) ?? sites.value[0];
    });

    function selectSite(siteId) {
        if (sites.value.some((site) => site.siteId === siteId)) {
            selectedSiteId.value = siteId;
        }
    }

    onMounted(load);

    return {
        loading,
        error,
        moduleKey: computed(() => raw.value?.moduleKey ?? moduleKey),
        moduleName: computed(() => raw.value?.moduleName ?? null),
        baseline,
        monitoredSiteIds,
        sites,
        summary,
        versionDistribution,
        latestVersion,
        overallStatus,
        lastSyncedAt: computed(() => raw.value?.lastSyncedAt ?? null),
        selectedSite,
        selectedSiteId,
        selectSite,
    };
}
