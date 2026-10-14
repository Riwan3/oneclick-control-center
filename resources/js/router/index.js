import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Login from '../pages/Login.vue';
import Home from '../pages/Home.vue';
import Dashboard from '../pages/Dashboard.vue';
import Sites from '../pages/Sites.vue';
import SiteDetail from '../pages/SiteDetail.vue';
import Modules from '../pages/Modules.vue';
import Kpi from '../pages/Kpi.vue';
import Deployments from '../pages/Deployments.vue';
import ModuleObservatory from '../pages/ModuleObservatory.vue';
import Baseline from '../pages/Baseline.vue';
import ChangeLog from '../pages/ChangeLog.vue';
import SiteConfiguration from '../pages/SiteConfiguration.vue';
import ApiOverview from '../pages/ApiOverview.vue';
import SiteApi from '../pages/SiteApi.vue';
import ControlCenterApi from '../pages/ControlCenterApi.vue';
import ApiConfiguration from '../pages/ApiConfiguration.vue';
import Releases from '../pages/Releases.vue';
import ReleaseDetail from '../pages/ReleaseDetail.vue';
import Monitoring from '../pages/Monitoring.vue';
import Audit from '../pages/Audit.vue';
import Users from '../pages/Users.vue';
import Notifications from '../pages/Notifications.vue';
import Profile from '../pages/Profile.vue';
import DesignSystemPreview from '../pages/DesignSystemPreview.vue';
import NavigationPreview from '../pages/NavigationPreview.vue';

const routes = [
    {
        // The only public route. Everything else requires authentication —
        // see the router.beforeEach guard below.
        path: '/login',
        name: 'login',
        component: Login,
        meta: { title: 'Login', public: true },
    },
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: { title: 'Home' },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard' },
    },
    {
        path: '/sites',
        name: 'sites',
        component: Sites,
        meta: { title: 'Sites' },
    },
    {
        path: '/sites/:id',
        name: 'site-detail',
        component: SiteDetail,
        props: true,
        meta: { title: 'Site Detail' },
    },
    {
        path: '/modules',
        name: 'modules',
        component: Modules,
        meta: { title: 'Modules' },
    },
    {
        path: '/kpi',
        name: 'kpi',
        component: Kpi,
        meta: { title: 'KPI' },
    },
    {
        path: '/deployments',
        name: 'deployments',
        component: Deployments,
        meta: { title: 'Deployments' },
    },
    {
        path: '/module-observatory',
        name: 'module-observatory',
        component: ModuleObservatory,
        meta: { title: 'Module Observatory' },
    },
    {
        path: '/site-configuration',
        name: 'site-configuration',
        component: SiteConfiguration,
        meta: { title: 'Site Configuration' },
    },
    {
        // API Integration UI/UX foundation — no real API is connected;
        // see ApiOverview.vue's header badge. Sub-pages are reached via
        // ApiIntegrationTabs.vue, not additional sidebar entries.
        path: '/api-integration',
        name: 'api-integration',
        component: ApiOverview,
        meta: { title: 'API Integration' },
    },
    {
        path: '/api-integration/site-api',
        name: 'api-integration-site-api',
        component: SiteApi,
        meta: { title: 'Site API' },
    },
    {
        path: '/api-integration/control-center-api',
        name: 'api-integration-control-center-api',
        component: ControlCenterApi,
        meta: { title: 'Control Center API' },
    },
    {
        path: '/api-integration/configuration',
        name: 'api-integration-configuration',
        component: ApiConfiguration,
        meta: { title: 'API Configuration' },
    },
    {
        path: '/monitoring',
        name: 'monitoring',
        component: Monitoring,
        meta: { title: 'Monitoring' },
    },
    {
        path: '/audit',
        name: 'audit',
        component: Audit,
        meta: { title: 'Audit' },
    },
    {
        path: '/users',
        name: 'users',
        component: Users,
        meta: { title: 'User Management' },
    },
    {
        path: '/baseline',
        name: 'baseline',
        component: Baseline,
        meta: { title: 'Master Baseline' },
    },
    {
        path: '/changelog',
        name: 'changelog',
        component: ChangeLog,
        meta: { title: 'Change Log' },
    },
    {
        // STEP 3R-2 — Release Version + Changelog Foundation.
        path: '/releases',
        name: 'releases',
        component: Releases,
        meta: { title: 'Release Management' },
    },
    {
        path: '/releases/:id',
        name: 'release-detail',
        component: ReleaseDetail,
        props: true,
        meta: { title: 'Release Detail' },
    },
    {
        // Not in the sidebar — reached via the topbar notification bell.
        // Any authenticated user may view their own notifications.
        path: '/notifications',
        name: 'notifications',
        component: Notifications,
        meta: { title: 'Notifications' },
    },
    {
        // Reached from the topbar user menu; any authenticated user.
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta: { title: 'My Profile' },
    },
    {
        // Temporary — validates the STEP 3E design system. Not in sidebar nav.
        path: '/design-system',
        name: 'design-system',
        component: DesignSystemPreview,
        meta: { title: 'Design System' },
    },
    {
        // Temporary — validates the STEP 3F navigation architecture. Not in sidebar nav.
        path: '/navigation-preview',
        name: 'navigation-preview',
        component: NavigationPreview,
        meta: { title: 'Navigation Preview' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Auth guard: resolves the current session once (fetchUser), then keeps
// unauthenticated visitors on /login and authenticated visitors off of
// it — everything else is protected by default via `meta.public`.
router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.initialized) {
        await auth.fetchUser();
    }

    if (!auth.authenticated && !to.meta.public) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (auth.authenticated && to.meta.public) {
        return { path: '/' };
    }

    return true;
});

export default router;
