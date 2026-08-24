// Mock data for the Site Detail page (SiteDetail.vue).
//
// Site IDENTITY (code, name, location, status, version, users, modules,
// systemHealth, lastActivity) already lives in data/mock/sites.js — this
// file only adds detail-specific information: performance visualizations
// and a recent-activity feed, keyed by the same site `id`.
//
// Values are derived deterministically from each site's own record in
// sites.js (its systemHealth, users, status, modules) using fixed
// formulas below — no Math.random(), no timers, no polling. This keeps
// the detail page consistent with the site's summary data and means
// every site in sites.js automatically gets detail data without hand
// duplicating 14 blocks.
import { sites } from './sites';
import { modules as moduleCatalog } from '../../config/modules';

const WEEKDAY_LABELS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

// Relative shape of a normal week, 0-100. Same shape for every site;
// scaled down for sites that are degraded/offline (see statusMultiplier).
const ACTIVE_USER_SHAPE = [88, 94, 100, 90, 97, 55, 48];

// Day-over-day offset applied to the site's current systemHealth, so the
// trend recovers up to today's actual value (the last point always
// equals site.systemHealth exactly).
const HEALTH_TREND_OFFSET = [-2.4, -1.8, -1.2, -0.9, -0.5, -0.3, 0];

const MODULE_BASE_UTILIZATION = {
    'one-manpower': 82,
    'one-fleet': 65,
    'one-dms': 54,
    'one-qcc': 38,
};

const ACTIVITY_TIMESTAMPS = [
    '24 Aug 2026, 09:00',
    '23 Aug 2026, 18:20',
    '23 Aug 2026, 10:05',
    '22 Aug 2026, 14:40',
];

function statusMultiplier(status) {
    if (status === 'offline') return 0.05;
    if (status === 'warning') return 0.7;
    return 1;
}

function clamp(value, min = 0, max = 100) {
    return Math.max(min, Math.min(max, value));
}

function buildPerformance(site) {
    const multiplier = statusMultiplier(site.status);

    return {
        activeUserTrend: WEEKDAY_LABELS.map((label, i) => ({
            label,
            value: clamp(Math.round(ACTIVE_USER_SHAPE[i] * multiplier)),
        })),
        moduleUtilization: moduleCatalog.map((mod) => ({
            key: mod.key,
            label: mod.label,
            value: site.modules?.[mod.key] ? MODULE_BASE_UTILIZATION[mod.key] : 0,
        })),
        systemHealthTrend: WEEKDAY_LABELS.map((label, i) => ({
            label,
            value: clamp(Math.round(site.systemHealth + HEALTH_TREND_OFFSET[i])),
        })),
    };
}

function buildRecentActivity(site) {
    const enabledModule = moduleCatalog.find((mod) => site.modules?.[mod.key]);

    return [
        {
            timestamp: site.lastActivity,
            type: 'system',
            title: 'System health check completed',
            description: `Automated health check completed for ${site.code}.`,
            status: site.status === 'offline' ? 'danger' : 'success',
        },
        {
            timestamp: ACTIVITY_TIMESTAMPS[0],
            type: 'module',
            title: enabledModule ? `${enabledModule.label} module verified` : 'No active modules to verify',
            description: enabledModule
                ? `${enabledModule.label} responded normally on the latest check.`
                : `${site.code} has no OneClick modules enabled yet.`,
            status: enabledModule ? 'success' : 'neutral',
        },
        {
            timestamp: ACTIVITY_TIMESTAMPS[1],
            type: 'version',
            title: 'Version confirmed',
            description: `${site.code} is running OneClick ${site.version}.`,
            status: 'info',
        },
        {
            timestamp: ACTIVITY_TIMESTAMPS[2],
            type: 'activity',
            title: 'User activity recorded',
            description: `${site.users.toLocaleString()} registered users on ${site.code}.`,
            status: 'neutral',
        },
    ];
}

export const siteDetails = Object.fromEntries(
    sites.map((site) => [
        site.id,
        {
            performance: buildPerformance(site),
            recentActivity: buildRecentActivity(site),
        },
    ])
);

export function getSiteDetail(siteId) {
    return siteDetails[siteId] ?? null;
}
