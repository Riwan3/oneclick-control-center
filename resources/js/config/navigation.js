// Central navigation configuration — CONFIGURATION layer only.
//
// This file does not know about the current user, site, or module state.
// Filtering/grouping for the active session lives in
// composables/useNavigation.js (NAVIGATION STATE); AppSidebar.vue only
// renders whatever that composable hands it (PRESENTATION). Keeping this
// split means new nav items are added here, not inside AppSidebar.vue.
//
// Field reference:
// - id          unique key
// - label       display label
// - route       Vue Router path, or null if the page does not exist yet
// - icon        AppIcon name
// - section     group heading shown in the sidebar, or null for top-level items
// - module      key into config/modules.js, or null if not module-gated
// - enabled     whether the page/feature actually exists yet (independent of
//               role/site — an unbuilt page is unavailable to everyone)
// - capability  key into the role capability matrix (config/roles.js) that
//               a role needs to see this item, or null if every role sees it
// - sites       'all' (always visible) or 'selected' (only once a specific
//               site is chosen — for site- or module-scoped items)
// - order       sort position, grouped in contiguous blocks per section
export const navigationItems = [
    {
        id: 'home',
        label: 'Home',
        route: '/',
        icon: 'home',
        section: null,
        module: null,
        enabled: true,
        capability: 'overview',
        sites: 'all',
        order: 1,
    },
    {
        // Grouped under a "Dashboard" section heading, matching the
        // Performance/Governance/System sections below — Home (above,
        // order 1) stays its own unlabeled top item, unchanged.
        id: 'dashboard',
        label: 'Dashboard',
        route: '/dashboard',
        icon: 'dashboard',
        section: 'Dashboard',
        module: null,
        enabled: true,
        capability: 'overview',
        sites: 'all',
        order: 2,
    },
    {
        id: 'sites',
        label: 'Sites',
        route: '/sites',
        icon: 'sites',
        section: 'Dashboard',
        module: null,
        enabled: true,
        capability: 'site_view',
        sites: 'all',
        order: 3,
    },
    {
        id: 'modules',
        label: 'Modules',
        route: '/modules',
        icon: 'layers',
        section: 'Dashboard',
        module: null,
        enabled: true,
        capability: 'module_view',
        sites: 'all',
        order: 4,
    },
    {
        id: 'kpi',
        label: 'KPI',
        route: '/kpi',
        icon: 'monitoring',
        section: 'Performance',
        module: null,
        enabled: true,
        capability: 'kpi',
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
        capability: 'site_view',
        sites: 'selected',
        order: 11,
    },
    {
        id: 'one-manpower',
        label: 'One-Manpower',
        route: null,
        icon: 'briefcase',
        section: 'Operations',
        module: 'one-power',
        enabled: false,
        capability: 'module_view',
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
        capability: 'module_view',
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
        capability: 'module_view',
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
        capability: 'audit_view',
        sites: 'all',
        order: 30,
    },
    {
        id: 'change-log',
        label: 'Change Log',
        route: '/changelog',
        icon: 'history',
        section: 'Governance',
        module: null,
        enabled: true,
        capability: 'change_log_view',
        sites: 'all',
        order: 31,
    },
    {
        // STEP 3R-2 — Release Version + Changelog Foundation. This was
        // an inert placeholder (route: null, enabled: false) reserved
        // for exactly this feature since STEP 3F; repurposed here
        // rather than adding a second, competing nav item. capability
        // stays version_view (unchanged) — read access for
        // super_admin+management, matching the Deployments page.
        id: 'version',
        label: 'Releases',
        route: '/releases',
        icon: 'tag',
        section: 'Governance',
        module: null,
        enabled: true,
        capability: 'version_view',
        sites: 'all',
        order: 32,
    },
    {
        id: 'users',
        label: 'User Management',
        route: '/users',
        icon: 'user',
        section: 'Governance',
        module: null,
        enabled: true,
        capability: 'user_manage',
        sites: 'all',
        order: 33,
    },
    {
        // Master Baseline Registry — reuses change_log_view, the
        // closest existing governance-reference capability. No new
        // capability was created; the existing "Change Log" placeholder
        // item above (route: null, enabled: false) is untouched.
        id: 'baseline',
        label: 'Baseline',
        route: '/baseline',
        icon: 'tag',
        section: 'Governance',
        module: null,
        enabled: true,
        capability: 'change_log_view',
        sites: 'all',
        order: 34,
    },
    {
        id: 'monitoring',
        label: 'Monitoring',
        route: '/monitoring',
        icon: 'monitoring',
        section: 'System',
        module: null,
        enabled: true,
        capability: 'monitoring',
        sites: 'all',
        order: 40,
    },
    {
        id: 'deployments',
        label: 'Deployments',
        route: '/deployments',
        icon: 'package',
        section: 'System',
        module: null,
        enabled: true,
        capability: 'version_view',
        sites: 'all',
        order: 41,
    },
    {
        // STEP 3R: Module Observatory — configuration/version/sidebar
        // governance for monitored applications (starting with
        // One-Power). Reuses module_view, the same capability Modules
        // already uses — no new capability was created for this.
        id: 'module-observatory',
        label: 'Module Observatory',
        route: '/module-observatory',
        icon: 'monitoring',
        section: 'System',
        module: null,
        enabled: true,
        capability: 'module_view',
        sites: 'all',
        order: 42,
    },
    {
        // Site Configuration Foundation — new capability
        // site_configuration_manage (true only for super_admin in
        // config/roles.js), gating the whole page the same way
        // user_manage already gates Users.vue. No new authorization
        // mechanism; reuses the 'layers' icon already used for Modules.
        id: 'site-configuration',
        label: 'Site Configuration',
        route: '/site-configuration',
        icon: 'layers',
        section: 'System',
        module: null,
        enabled: true,
        capability: 'site_configuration_manage',
        sites: 'all',
        order: 43,
    },
    {
        // API Integration UI/UX foundation — reuses site_configuration_manage,
        // the closest existing capability for a system/infrastructure
        // configuration concern (matching its System-section sibling,
        // Site Configuration). No new capability was created. The three
        // conceptual sub-pages (Site API, Control Center API, API
        // Configuration) are reached via in-page tabs on this page, not
        // additional sidebar entries — see ApiIntegrationTabs.vue.
        id: 'api-integration',
        label: 'API Integration',
        route: '/api-integration',
        icon: 'package',
        section: 'System',
        module: null,
        enabled: true,
        capability: 'site_configuration_manage',
        sites: 'all',
        order: 44,
    },
];
