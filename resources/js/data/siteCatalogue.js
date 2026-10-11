import { reactive, ref } from 'vue';
import { apiJson } from '../lib/http';
import { modules as moduleCatalog } from '../config/modules';

// Site catalogue backed by GET /api/site-connections (the `sites` table
// plus the status last pulled from each connected OneClick site). The
// array is reactive and replaced in place, so every consumer updates
// when a refresh lands without changing how it reads `sites`.
//
// Honesty rules: a site that has no connection yet has status
// 'unknown'; fields a site did not report are null/'—', never invented.
// `systemHealth` is the site's own server-health score (100 minus its
// busiest resource), null while unreachable or unreadable.
export const sites = reactive([]);
export const sitesLoaded = ref(false);

// Module keys the OneClick site reports -> the Master Baseline Registry
// key the Control Center uses for the same module. Anything not listed
// maps to itself; site modules with no baseline counterpart (e.g.
// one-roster) are simply not part of the catalogue.
const SITE_TO_BASELINE_MODULE = {
    'one-core': 'one-core-value',
    'one-procedure': 'one-procedur',
};

function formatTimestamp(iso) {
    if (!iso) return '—';
    return new Date(iso).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function toSite(row) {
    const reported = new Set((row.modules ?? []).map((key) => SITE_TO_BASELINE_MODULE[key] ?? key));

    return {
        id: row.code.toLowerCase(),
        dbId: row.id,
        code: row.code,
        name: row.name,
        location: row.location ?? '—',
        connected: row.connected,
        status: row.connected ? row.status : 'unknown',
        version: row.version ?? '—',
        users: row.users ?? 0,
        modules: Object.fromEntries(moduleCatalog.map((mod) => [mod.key, reported.has(mod.key)])),
        systemHealth: row.health_score,
        environment: row.environment ?? '—',
        deployedAt: formatTimestamp(row.deployed_at),
        activeUsersToday: row.active_users_today ?? 0,
        dailyActivity: row.daily_activity ?? [],
        lastActivity: formatTimestamp(row.last_checked_at),
        lastCheckedAt: row.last_checked_at,
        lastError: row.last_error,
    };
}

export async function loadSites() {
    const { ok, data } = await apiJson('/api/site-connections');
    if (ok && Array.isArray(data?.data)) sites.splice(0, sites.length, ...data.data.map(toSite));

    // Marked loaded even on failure so the layout never hangs on a spinner;
    // a failed load just leaves the catalogue empty (or at its last data).
    sitesLoaded.value = true;
    return ok;
}
