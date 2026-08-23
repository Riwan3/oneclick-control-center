<script setup>
import { useUiStore } from '../stores/ui';
import AppSidebar from '../components/layout/AppSidebar.vue';
import AppTopbar from '../components/layout/AppTopbar.vue';
import AppContent from '../components/layout/AppContent.vue';

const ui = useUiStore();
</script>

<template>
    <div class="min-h-screen bg-background">
        <AppSidebar />

        <div
            class="flex min-h-screen flex-col transition-[margin-left] duration-200"
            :class="ui.sidebarCollapsed ? 'ml-20' : 'ml-64'"
        >
            <AppTopbar />
            <AppContent />
        </div>
    </div>
</template>
