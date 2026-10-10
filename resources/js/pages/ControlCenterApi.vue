<script setup>
import { useAuthStore } from '../stores/auth';
import { useApiIntegration } from '../composables/useApiIntegration';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiButton from '../components/ui/UiButton.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';
import ApiIntegrationTabs from '../components/api-integration/ApiIntegrationTabs.vue';

const auth = useAuthStore();
const { services, statusLabel, statusVariant } = useApiIntegration();
</script>

<template>
    <div v-if="auth.can('site_configuration_manage')" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Control Center API</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Control Center services prepared for site application integration.
                </p>
            </div>
            <UiBadge variant="neutral" class="px-3 py-1 text-xs">No Endpoints Published</UiBadge>
        </div>

        <ApiIntegrationTabs />

        <DashboardSection
            title="API Service Catalogue"
            description="Control Center domains prepared for future external access — none is published as a live endpoint yet."
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <UiCard v-for="service in services" :key="service.key" class="flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-semibold text-text-primary text-sm">{{ service.name }}</span>
                            <UiBadge :variant="statusVariant[service.status]">{{ statusLabel[service.status] }}</UiBadge>
                        </div>
                    </div>
                    <div class="mt-4 space-y-1.5 border-t border-border/40 pt-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Version</span>
                            <span class="font-medium text-text-secondary">{{ service.version }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Endpoint</span>
                            <span class="font-medium text-text-secondary">{{ service.endpoint }}</span>
                        </div>
                    </div>
                </UiCard>
            </div>
        </DashboardSection>
    </div>

    <UiCard v-else>
        <h2 class="text-page-title">Access Restricted</h2>
        <p class="text-body mt-2 max-w-2xl">
            You don't have permission to view this page. Contact your administrator if you believe
            this is a mistake.
        </p>
        <router-link to="/" class="mt-4 inline-block">
            <UiButton variant="secondary">← Back to Home</UiButton>
        </router-link>
    </UiCard>
</template>
