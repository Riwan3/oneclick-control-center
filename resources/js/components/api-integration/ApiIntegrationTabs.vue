<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const tabs = [
    { name: 'api-integration', label: 'API Overview', to: '/api-integration' },
    { name: 'api-integration-site-api', label: 'Site API', to: '/api-integration/site-api' },
    { name: 'api-integration-control-center-api', label: 'Control Center API', to: '/api-integration/control-center-api' },
    { name: 'api-integration-configuration', label: 'API Configuration', to: '/api-integration/configuration' },
];
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <router-link
            v-for="tab in tabs"
            :key="tab.name"
            :to="tab.to"
            class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
            :class="
                route.name === tab.name
                    ? 'bg-primary text-white'
                    : 'border border-border bg-surface text-text-secondary hover:bg-background'
            "
        >
            {{ tab.label }}
        </router-link>
    </div>
</template>
