<script setup>
import { useUiStore } from '../stores/ui';
import AppSidebar from '../components/layout/AppSidebar.vue';
import AppTopbar from '../components/layout/AppTopbar.vue';
import AppContent from '../components/layout/AppContent.vue';
import { useLiveSites } from '../composables/useLiveSites';
import { sitesLoaded } from '../data/siteCatalogue';

const ui = useUiStore();
useLiveSites();
</script>

<template>
    <div class="min-h-screen bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: url('/app-bg.png');">
        <AppSidebar />

        <div
            class="flex min-h-screen flex-col transition-[margin-left] duration-200"
            :class="ui.sidebarCollapsed ? 'ml-20' : 'ml-64'"
        >
            <AppTopbar />
            <AppContent v-if="sitesLoaded" />
            <main v-else class="flex-1 p-8 text-sm text-text-muted">Loading sites…</main>
        </div>
    </div>
</template>
