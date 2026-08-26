import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Login from '../pages/Login.vue';
import Home from '../pages/Home.vue';
import Dashboard from '../pages/Dashboard.vue';
import Sites from '../pages/Sites.vue';
import SiteDetail from '../pages/SiteDetail.vue';
import Modules from '../pages/Modules.vue';
import Deployments from '../pages/Deployments.vue';
import ModuleObservatory from '../pages/ModuleObservatory.vue';
import Baseline from '../pages/Baseline.vue';
import Monitoring from '../pages/Monitoring.vue';
import Audit from '../pages/Audit.vue';
import Users from '../pages/Users.vue';
import Notifications from '../pages/Notifications.vue';
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
        path: '/baseline',
        name: 'baseline',
        component: Baseline,
        meta: { title: 'Master Baseline' },
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
        // Not in the sidebar — reached via the topbar notification bell.
        // Any authenticated user may view their own notifications.
        path: '/notifications',
        name: 'notifications',
        component: Notifications,
        meta: { title: 'Notifications' },
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
