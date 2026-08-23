import { createRouter, createWebHistory } from 'vue-router';
import Home from '../pages/Home.vue';
import Dashboard from '../pages/Dashboard.vue';
import Sites from '../pages/Sites.vue';
import Monitoring from '../pages/Monitoring.vue';
import Audit from '../pages/Audit.vue';

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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
