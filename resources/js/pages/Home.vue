<script setup>
import { useAuthStore } from '../stores/auth';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import ExecutiveKpiGrid from '../components/dashboard/ExecutiveKpiGrid.vue';
import SiteHealthOverview from '../components/dashboard/SiteHealthOverview.vue';
import PerformanceOverview from '../components/dashboard/PerformanceOverview.vue';
import UserActivityCard from '../components/dashboard/UserActivityCard.vue';
import ModuleOverview from '../components/dashboard/ModuleOverview.vue';
import RecentActivity from '../components/dashboard/RecentActivity.vue';
import {
    executiveKpis,
    siteHealth,
    activeUserTrend,
    moduleUtilization,
    userActivity,
    moduleAdoption,
    recentActivity,
} from '../data/mock/homeDashboard';

const auth = useAuthStore();

// Static at load — no polling/interval, mock only per STEP 3H.
const today = new Date().toLocaleDateString('en-GB', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});
</script>

<template>
    <div class="space-y-8">
        <div>
            <p class="text-label">{{ today }}</p>
            <h1 class="text-page-title mt-1">PPA Group Digital Command Center</h1>
            <p class="text-body mt-2 max-w-2xl">
                A high-level overview of the OneClick ecosystem across all connected PPA sites.
            </p>
        </div>

        <DashboardSection
            title="Executive Overview"
            description="Ecosystem-wide KPIs across all connected sites."
        >
            <ExecutiveKpiGrid :kpis="executiveKpis" />
        </DashboardSection>

        <DashboardSection title="Site Health" description="Status across all 14 PPA sites.">
            <SiteHealthOverview :sites="siteHealth" />
        </DashboardSection>

        <DashboardSection
            v-if="auth.can('monitoring')"
            title="Performance Overview"
            description="Active user trend and module utilization across the ecosystem."
        >
            <PerformanceOverview :user-trend="activeUserTrend" :module-utilization="moduleUtilization" />
        </DashboardSection>

        <DashboardSection
            v-if="auth.can('monitoring')"
            title="User Activity"
            description="Login and engagement activity across all sites."
        >
            <UserActivityCard :activity="userActivity" />
        </DashboardSection>

        <DashboardSection
            v-if="auth.can('module_view')"
            title="Module Overview"
            description="Adoption of OneClick modules across sites."
        >
            <ModuleOverview :modules="moduleAdoption" />
        </DashboardSection>

        <DashboardSection
            v-if="auth.hasRole('super_admin')"
            title="Recent System Activity"
            description="Latest events across the OneClick ecosystem."
        >
            <RecentActivity :activities="recentActivity" />
        </DashboardSection>
    </div>
</template>
