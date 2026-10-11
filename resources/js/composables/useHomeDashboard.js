// Derives the Home dashboard from real data only: the access-filtered
// site catalogue (data/siteCatalogue.js, backed by /api/site-connections)
// and the audit log (/api/audit-logs) for recent activity. Everything is
// scoped to the user's effective site/module access, like every other
// page. Nothing here is invented: there is no source for system-health
// percentages or usage trends, so those are not shown.
import { ref, computed, onMounted } from 'vue';
import { sites as allSites } from '../data/siteCatalogue';
import { modules as allModuleCatalog } from '../config/modules';
import { auditService } from '../services/auditService';
import { useEffectiveAccess } from './useEffectiveAccess';

const RECENT_ACTIVITY_LIMIT = 5;

// Site status -> dashboard badge variant.
const HEALTH_VARIANT = { online: 'success', warning: 'warning', offline: 'danger', unknown: 'neutral' };

function round1(value) {
    return Math.round(value * 10) / 10;
}

function activityType(action) {
    return action.includes('login') || action.includes('logout') ? 'activity' : 'system';
}

export function useHomeDashboard() {
    const { canAccessSite, canAccessModule } = useEffectiveAccess();

    const sites = computed(() => allSites.filter((site) => canAccessSite(site.code)));
    const moduleCatalog = computed(() => allModuleCatalog.filter((mod) => canAccessModule(mod.key)));

    const siteHealth = computed(() =>
        sites.value.map((site) => ({ code: site.code, status: HEALTH_VARIANT[site.status] ?? 'neutral' }))
    );

    const connectedSites = computed(() => sites.value.filter((site) => site.connected));
    const totalUsers = computed(() => connectedSites.value.reduce((sum, site) => sum + site.users, 0));

    const moduleAdoption = computed(() =>
        moduleCatalog.value.map((mod) => {
            const enabled = connectedSites.value.filter((site) => site.modules?.[mod.key]).length;
            return { key: mod.key, label: mod.label, sitesEnabled: enabled, totalSites: sites.value.length };
        })
    );

    const moduleUtilization = computed(() =>
        moduleAdoption.value.map((m) => ({
            key: m.key,
            label: m.label,
            value: m.totalSites ? round1((m.sitesEnabled / m.totalSites) * 100) : 0,
        }))
    );

    // Average of the health scores the sites themselves report (their
    // server's busiest-resource headroom); null when no site reports one.
    const avgSystemHealth = computed(() => {
        const scores = connectedSites.value.map((site) => site.systemHealth).filter((score) => score !== null);
        return scores.length ? round1(scores.reduce((sum, score) => sum + score, 0) / scores.length) : null;
    });

    const activeUsersToday = computed(() =>
        connectedSites.value.reduce((sum, site) => sum + site.activeUsersToday, 0)
    );

    // Day-by-day unique users across the visible sites, bars relative to the peak day.
    const activeUserTrend = computed(() => {
        const totals = new Map();
        for (const site of connectedSites.value) {
            for (const day of site.dailyActivity) {
                totals.set(day.label, (totals.get(day.label) ?? 0) + day.unique_users);
            }
        }
        const peak = Math.max(1, ...totals.values());
        return Array.from(totals, ([label, count]) => ({ label, count, value: Math.round((count / peak) * 100) }));
    });

    const executiveKpis = computed(() => [
        { id: 'system-health', title: 'Average System Health', value: avgSystemHealth.value },
        { id: 'active-users-today', title: 'Active Users Today', value: activeUsersToday.value },
        { id: 'total-sites', title: 'Total Sites', value: sites.value.length, unit: '', icon: 'sites' },
        {
            id: 'connected-sites',
            title: 'Connected Sites',
            value: connectedSites.value.length,
            unit: `/ ${sites.value.length}`,
            icon: 'monitoring',
        },
        {
            id: 'total-users',
            title: 'Registered Users',
            value: totalUsers.value.toLocaleString('en-US'),
            unit: '',
            icon: 'user',
        },
    ]);

    // Recent activity comes from the audit log. Roles without access to
    // /api/audit-logs simply get an empty list.
    const recentActivity = ref([]);

    onMounted(async () => {
        const { ok, data } = await auditService.fetchLogs({ per_page: RECENT_ACTIVITY_LIMIT });
        if (!ok) return;

        recentActivity.value = (data?.data ?? []).map((log) => ({
            id: log.id,
            timestamp: new Date(log.created_at).toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }),
            site: log.user?.username ?? 'System',
            message: log.description,
            type: activityType(log.action),
        }));
    });

    return { executiveKpis, siteHealth, activeUserTrend, moduleUtilization, moduleAdoption, recentActivity };
}
