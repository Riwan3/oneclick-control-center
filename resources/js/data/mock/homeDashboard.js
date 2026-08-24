// Mock data for the Home / PPA Group Digital Command Center dashboard.
//
// Shaped the way a future API response is expected to look, so the
// dashboard components can be pointed at a real endpoint later without
// redesigning them — only this file (or its replacement) changes.
//
// All values are static/deterministic mock data. No API, no randomness.

export const executiveKpis = [
    {
        id: 'total-sites',
        title: 'Total Sites',
        value: 14,
        unit: '',
        icon: 'sites',
        trend: { direction: 'up', label: '+2 vs last quarter' },
    },
    {
        id: 'active-users',
        title: 'Active Users Today',
        value: '1,284',
        unit: '',
        icon: 'user',
        trend: { direction: 'up', label: '+6.4% vs yesterday' },
    },
    {
        id: 'active-modules',
        title: 'Active Modules',
        value: 42,
        unit: '',
        icon: 'dashboard',
        trend: { direction: 'flat', label: 'No change vs last week' },
    },
    {
        id: 'system-health',
        title: 'System Health',
        value: 99.8,
        unit: '%',
        icon: 'monitoring',
        status: 'success',
        statusLabel: 'All systems normal',
    },
];

// Deterministic mock status per site. 'success' = Online, 'warning' =
// Warning, 'danger' = Offline.
export const siteHealth = [
    { code: 'BIB', status: 'success' },
    { code: 'MHU', status: 'success' },
    { code: 'SKS', status: 'warning' },
    { code: 'WARA', status: 'success' },
    { code: 'MIP', status: 'success' },
    { code: 'BA', status: 'danger' },
    { code: 'MIFA', status: 'success' },
    { code: 'BCP', status: 'warning' },
    { code: 'AMI', status: 'success' },
    { code: 'MLP', status: 'success' },
    { code: 'PIK', status: 'success' },
    { code: 'BGE', status: 'success' },
    { code: 'SBS', status: 'warning' },
    { code: 'KES', status: 'success' },
];

// Bar-chart points, 0-100, relative to peak capacity.
export const activeUserTrend = [
    { label: 'Mon', value: 62 },
    { label: 'Tue', value: 68 },
    { label: 'Wed', value: 71 },
    { label: 'Thu', value: 66 },
    { label: 'Fri', value: 74 },
    { label: 'Sat', value: 40 },
    { label: 'Sun', value: 35 },
];

// Utilization percentage per module, across active sites.
export const moduleUtilization = [
    { key: 'one-manpower', label: 'One-Manpower', value: 82 },
    { key: 'one-fleet', label: 'One-Fleet', value: 65 },
    { key: 'one-dms', label: 'One-DMS', value: 54 },
    { key: 'one-qcc', label: 'One-QCC', value: 38 },
];

export const userActivity = {
    activeToday: 1284,
    activeThisWeek: 3120,
    inactive: 412,
    loginTrend: [
        { label: 'Mon', value: 58 },
        { label: 'Tue', value: 64 },
        { label: 'Wed', value: 69 },
        { label: 'Thu', value: 61 },
        { label: 'Fri', value: 72 },
        { label: 'Sat', value: 33 },
        { label: 'Sun', value: 29 },
    ],
};

// Module adoption across the 14 connected sites.
export const moduleAdoption = [
    { key: 'one-manpower', label: 'One-Manpower', sitesEnabled: 12, totalSites: 14 },
    { key: 'one-fleet', label: 'One-Fleet', sitesEnabled: 10, totalSites: 14 },
    { key: 'one-dms', label: 'One-DMS', sitesEnabled: 8, totalSites: 14 },
    { key: 'one-qcc', label: 'One-QCC', sitesEnabled: 6, totalSites: 14 },
];

// type: 'module' | 'version' | 'activity' | 'system' — used by
// RecentActivity.vue to pick an icon/badge variant.
export const recentActivity = [
    { id: 1, timestamp: '24 Aug 2026, 09:12', site: 'BIB', message: 'One-Manpower module updated', type: 'module' },
    { id: 2, timestamp: '24 Aug 2026, 08:47', site: 'MHU', message: 'Application version updated', type: 'version' },
    { id: 3, timestamp: '24 Aug 2026, 08:30', site: 'WARA', message: 'User activity increased', type: 'activity' },
    { id: 4, timestamp: '23 Aug 2026, 17:05', site: 'MIP', message: 'One-Fleet module enabled', type: 'module' },
    { id: 5, timestamp: '23 Aug 2026, 15:00', site: 'System', message: 'System health check completed', type: 'system' },
];
