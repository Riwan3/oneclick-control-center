// Central navigation configuration — CONFIGURATION layer only.
//
// This file does not know about the current user, site, or module state.
// Filtering/grouping for the active session lives in
// composables/useNavigation.js (NAVIGATION STATE); AppSidebar.vue only
// renders whatever that composable hands it (PRESENTATION). Keeping this
// split means new nav items are added here, not inside AppSidebar.vue.
//
// Field reference:
// - id       unique key
// - label    display label
// - route    Vue Router path, or null if the page does not exist yet
// - icon     AppIcon name
// - section  group heading shown in the sidebar, or null for top-level items
// - module   key into config/modules.js, or null if not module-gated
// - enabled  whether the page/feature actually exists yet (independent of
//            role/site — an unbuilt page is unavailable to everyone)
// - roles    Control Center roles (see stores/auth.js) that may see this item
// - sites    'all' (always visible) or 'selected' (only once a specific
//            site is chosen — for site- or module-scoped items)
// - order    sort position, grouped in contiguous blocks per section
export const navigationItems = [
    {
        id: 'home',
        label: 'Home',
        route: '/',
        icon: 'home',
        section: null,
        module: null,
        enabled: true,
        roles: ['super_admin', 'management', 'view'],
        sites: 'all',
        order: 1,
    },
    {
        id: 'dashboard',
        label: 'Dashboard',
        route: '/dashboard',
        icon: 'dashboard',
        section: null,
        module: null,
        enabled: true,
        roles: ['super_admin', 'management', 'view'],
        sites: 'all',
        order: 2,
    },
    {
        id: 'sites',
        label: 'Sites',
        route: '/sites',
        icon: 'sites',
        section: null,
        module: null,
        enabled: true,
        roles: ['super_admin', 'management', 'view'],
        sites: 'all',
        order: 3,
    },
    {
        id: 'kpi',
        label: 'KPI',
        route: null,
        icon: 'monitoring',
        section: 'Performance',
        module: null,
        enabled: false,
        roles: ['super_admin', 'management'],
        sites: 'all',
        order: 10,
    },
    {
        id: 'site-performance',
        label: 'Site Performance',
        route: null,
        icon: 'monitoring',
        section: 'Performance',
        module: null,
        enabled: false,
        roles: ['super_admin', 'management', 'view'],
        sites: 'selected',
        order: 11,
    },
    {
        id: 'one-manpower',
        label: 'One-Manpower',
        route: null,
        icon: 'briefcase',
        section: 'Operations',
        module: 'one-manpower',
        enabled: false,
        roles: ['super_admin', 'management'],
        sites: 'selected',
        order: 20,
    },
    {
        id: 'one-fleet',
        label: 'One-Fleet',
        route: null,
        icon: 'truck',
        section: 'Operations',
        module: 'one-fleet',
        enabled: false,
        roles: ['super_admin', 'management'],
        sites: 'selected',
        order: 21,
    },
    {
        id: 'one-dms',
        label: 'One-DMS',
        route: null,
        icon: 'folder',
        section: 'Operations',
        module: 'one-dms',
        enabled: false,
        roles: ['super_admin', 'management', 'view'],
        sites: 'selected',
        order: 22,
    },
    {
        id: 'audit-trail',
        label: 'Audit Trail',
        route: '/audit',
        icon: 'audit',
        section: 'Governance',
        module: null,
        enabled: true,
        roles: ['super_admin'],
        sites: 'all',
        order: 30,
    },
    {
        id: 'change-log',
        label: 'Change Log',
        route: null,
        icon: 'history',
        section: 'Governance',
        module: null,
        enabled: false,
        roles: ['super_admin', 'management'],
        sites: 'all',
        order: 31,
    },
    {
        id: 'version',
        label: 'Version',
        route: null,
        icon: 'tag',
        section: 'Governance',
        module: null,
        enabled: false,
        roles: ['super_admin', 'management', 'view'],
        sites: 'all',
        order: 32,
    },
    {
        id: 'monitoring',
        label: 'Monitoring',
        route: '/monitoring',
        icon: 'monitoring',
        section: 'System',
        module: null,
        enabled: true,
        roles: ['super_admin', 'management'],
        sites: 'all',
        order: 40,
    },
];
