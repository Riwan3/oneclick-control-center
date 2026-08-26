// EFFECTIVE ACCESS: derives the Home dashboard's site- and module-scoped
// numbers (KPIs, module utilization/adoption, user activity, site
// health) from the same access-filtered site list used by Sites.vue /
// Modules.vue / Deployments.vue, instead of the flat ecosystem-wide
// mock constants in data/mock/homeDashboard.js. A management/view user
// who can only see e.g. site WARA sees "Active Users Today" scoped to
// WARA's own user count, not the full 14-site total — same rule as
// every other page, applied consistently here too.
//
// activeUserTrend and userActivity.loginTrend are left untouched: they
// are percentage-of-peak-capacity rates, not absolute counts, so
// narrowing the site set does not change what they represent.
import { computed } from 'vue';
import { sites as allSites } from '../data/mock/sites';
import {
    executiveKpis as baseExecutiveKpis,
    siteHealth as allSiteHealth,
    activeUserTrend,
    userActivity as baseUserActivity,
    recentActivity,
} from '../data/mock/homeDashboard';
import { modules as allModuleCatalog } from '../config/modules';
import { useEffectiveAccess } from './useEffectiveAccess';

function round1(value) {
    return Math.round(value * 10) / 10;
}

function activeModuleKeys(site, moduleCatalog) {
    return moduleCatalog.filter((mod) => Boolean(site.modules?.[mod.key]));
}

export function useHomeDashboard() {
    const { canAccessSite, canAccessModule } = useEffectiveAccess();

    const sites = computed(() => allSites.filter((site) => canAccessSite(site.code)));
    const siteHealth = computed(() => allSiteHealth.filter((entry) => canAccessSite(entry.code)));
    const moduleCatalog = computed(() => allModuleCatalog.filter((mod) => canAccessModule(mod.key)));

    const totalUsersAllSites = allSites.reduce((sum, site) => sum + site.users, 0);
    const scopedUsers = computed(() => sites.value.reduce((sum, site) => sum + site.users, 0));

    const scopedActiveModulePairs = computed(() =>
        sites.value.reduce((sum, site) => sum + activeModuleKeys(site, moduleCatalog.value).length, 0)
    );

    const scopedAvgSystemHealth = computed(() => {
        if (!sites.value.length) return 0;
        const total = sites.value.reduce((sum, site) => sum + site.systemHealth, 0);
        return round1(total / sites.value.length);
    });

    const executiveKpis = computed(() =>
        baseExecutiveKpis.map((kpi) => {
            switch (kpi.id) {
                case 'total-sites':
                    return { ...kpi, value: sites.value.length };
                case 'active-users':
                    return { ...kpi, value: scopedUsers.value.toLocaleString('en-US') };
                case 'active-modules':
                    return { ...kpi, value: scopedActiveModulePairs.value };
                case 'system-health':
                    return { ...kpi, value: scopedAvgSystemHealth.value };
                default:
                    return kpi;
            }
        })
    );

    const moduleUtilization = computed(() =>
        moduleCatalog.value.map((mod) => {
            const total = sites.value.length;
            const active = sites.value.filter((site) => Boolean(site.modules?.[mod.key])).length;
            return { key: mod.key, label: mod.label, value: total ? round1((active / total) * 100) : 0 };
        })
    );

    const moduleAdoption = computed(() =>
        moduleCatalog.value.map((mod) => ({
            key: mod.key,
            label: mod.label,
            sitesEnabled: sites.value.filter((site) => Boolean(site.modules?.[mod.key])).length,
            totalSites: sites.value.length,
        }))
    );

    // No per-site breakdown exists for the weekly activeThisWeek/inactive
    // splits, so they scale proportionally to the same today's-users
    // share the visible sites represent — keeping the three numbers
    // internally consistent as the site scope narrows.
    const userActivity = computed(() => {
        const ratio = totalUsersAllSites ? scopedUsers.value / totalUsersAllSites : 0;
        return {
            activeToday: scopedUsers.value,
            activeThisWeek: Math.round(baseUserActivity.activeThisWeek * ratio),
            inactive: Math.round(baseUserActivity.inactive * ratio),
            loginTrend: baseUserActivity.loginTrend,
        };
    });

    return {
        executiveKpis,
        siteHealth,
        activeUserTrend,
        moduleUtilization,
        userActivity,
        moduleAdoption,
        recentActivity,
    };
}
