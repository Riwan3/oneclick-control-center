import { createRouter, createWebHistory } from 'vue-router';
import Home from '../pages/Home.vue';
import Dashboard from '../pages/Dashboard.vue';
import Sites from '../pages/Sites.vue';
import SiteDetail from '../pages/SiteDetail.vue';
import Modules from '../pages/Modules.vue';
import Deployments from '../pages/Deployments.vue';
import Monitoring from '../pages/Monitoring.vue';
import Audit from '../pages/Audit.vue';
import DesignSystemPreview from '../pages/DesignSystemPreview.vue';
import NavigationPreview from '../pages/NavigationPreview.vue';

const routes = [
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

export default router;
