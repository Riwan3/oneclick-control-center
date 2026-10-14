// API BOUNDARY for the Module Observatory. What each site actually runs
// (version + menu manifest) comes from GET /api/module-observatory/{key},
// which relays what every connected OneClick site reports. The reference
// baseline is the Master Baseline Registry kept in the frontend
// (data/mock/baselineRegistry.js, taken from the SKS site) — it is the thing
// the sites are compared to, for every baseline module.
import { apiJson } from '../lib/http';
import { baselineModules, baselineMenus } from '../data/mock/baselineRegistry';

const BASELINES = Object.fromEntries(
    baselineModules.map((mod) => [
        mod.module_key,
        {
            moduleName: mod.label,
            menus: baselineMenus
                .filter((menu) => menu.module_key === mod.module_key)
                .map((menu) => ({
                    id: menu.menu_key,
                    label: menu.label,
                    parentId: menu.parent_key,
                    order: menu.order,
                    route: null,
                    status: menu.enabled ? 'active' : 'inactive',
                })),
        },
    ])
);

// 'one-coal' -> 'One-Coal' (the same label the backend gives an extension).
function extensionLabel(moduleKey) {
    return moduleKey
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('-');
}

function slug(label) {
    return label.toLowerCase().replace(/[^a-z0-9]+/g, '');
}

// Sites and the baseline name their menus differently (ids and route
// prefixes differ), so both are re-keyed by the menu's business identity:
// its label path, e.g. 'roster>rosterpersonal'. A menu that exists on both
// sides then matches regardless of internal id, and 'parentId' stays valid.
function rekeyByLabelPath(menus) {
    const keyById = new Map();

    return menus.map((menu) => {
        const parentKey = menu.parentId ? keyById.get(menu.parentId) : null;
        const key = parentKey ? `${parentKey}>${slug(menu.label)}` : slug(menu.label);
        keyById.set(menu.id, key);
        return { ...menu, id: key, parentId: parentKey ?? null };
    });
}

function parseVersion(version) {
    return String(version ?? '')
        .replace(/^v/, '')
        .split('.')
        .map((part) => Number(part) || 0);
}

function highestVersion(versions) {
    return versions.reduce((max, version) => {
        if (max === null) return version;
        const a = parseVersion(version);
        const b = parseVersion(max);
        const cmp = a.reduce((acc, part, i) => acc || part - (b[i] ?? 0), 0);
        return cmp > 0 ? version : max;
    }, null);
}

export const moduleObservatoryService = {
    // Returns the record for one observed module (site identity not yet
    // resolved). A baseline module is compared against the baseline. A
    // module without one is an EXTENSION — monitored when at least one
    // site reports it, with nothing to compare against — otherwise null.
    async getOverview(moduleKey) {
        const baseline = BASELINES[moduleKey] ?? null;

        const { ok, data } = await apiJson(`/api/module-observatory/${moduleKey}`);
        if (!ok) throw new Error('Unable to load module observatory data.');

        const sites = (data?.data?.sites ?? []).map((site) => ({
            siteId: site.code.toLowerCase(),
            version: site.version,
            menus: site.menus ? rekeyByLabelPath(site.menus) : null,
        }));

        if (!baseline && sites.length === 0) return null;

        return {
            moduleKey,
            moduleName: baseline ? baseline.moduleName : `${extensionLabel(moduleKey)} (extension)`,
            extension: !baseline,
            baseline: {
                // The baseline version is the newest version any site reports.
                version: highestVersion(sites.map((site) => site.version).filter(Boolean)),
                menus: baseline ? rekeyByLabelPath(baseline.menus) : [],
            },
            lastSyncedAt: data?.data?.lastSyncedAt ?? null,
            sites,
        };
    },
};
