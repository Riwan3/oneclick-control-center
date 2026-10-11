// API BOUNDARY for the Module Observatory. What each site actually runs
// (version + menu manifest) comes from GET /api/module-observatory/{key},
// which relays what every connected OneClick site reports. The reference
// baseline is the official business baseline kept in the frontend
// (data/onePowerBaseline.js) — it is the thing the sites are compared to.
import { apiJson } from '../lib/http';
import { ONE_POWER_BASELINE_MENUS } from '../data/onePowerBaseline';

const BASELINES = {
    'one-power': { moduleName: 'One-Power', menus: ONE_POWER_BASELINE_MENUS },
};

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
    // resolved), or null if it has no baseline and so isn't monitored.
    async getOverview(moduleKey) {
        const baseline = BASELINES[moduleKey];
        if (!baseline) return null;

        const { ok, data } = await apiJson(`/api/module-observatory/${moduleKey}`);
        if (!ok) throw new Error('Unable to load module observatory data.');

        const sites = (data?.data?.sites ?? []).map((site) => ({
            siteId: site.code.toLowerCase(),
            version: site.version,
            menus: site.menus ? rekeyByLabelPath(site.menus) : null,
        }));

        return {
            moduleKey,
            moduleName: baseline.moduleName,
            baseline: {
                // The baseline version is the newest version any site reports.
                version: highestVersion(sites.map((site) => site.version).filter(Boolean)),
                menus: rekeyByLabelPath(baseline.menus),
            },
            lastSyncedAt: data?.data?.lastSyncedAt ?? null,
            sites,
        };
    },
};
