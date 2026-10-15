<script setup>
import { onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useUiStore } from '../stores/ui';
import AppSidebar from '../components/layout/AppSidebar.vue';
import AppTopbar from '../components/layout/AppTopbar.vue';
import AppContent from '../components/layout/AppContent.vue';
import { useLiveSites } from '../composables/useLiveSites';
import { sitesLoaded } from '../data/siteCatalogue';

const ui = useUiStore();
const route = useRoute();
useLiveSites();

onMounted(() => ui.watchViewport());
// Close the drawer after navigating on phones/tablets.
watch(() => route.fullPath, () => ui.closeMobileNav());
</script>

<template>
    <div class="min-h-dvh bg-cover bg-center bg-no-repeat lg:bg-fixed" style="background-image: url('/app-bg.png');">
        <AppSidebar />

        <div
            class="flex min-h-dvh flex-col transition-[margin-left] duration-200"
            :class="ui.sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'"
        >
            <AppTopbar />
            <AppContent v-if="sitesLoaded" />
            <main v-else class="flex-1 p-4 sm:p-8 text-sm text-text-muted">Loading sites…</main>
        </div>
    </div>
</template>
