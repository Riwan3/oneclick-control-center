<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useUiStore } from '../../stores/ui';
import AppIcon from './AppIcon.vue';

const ui = useUiStore();
const route = useRoute();

const pageTitle = computed(() => route.meta.title ?? 'OneClick Control Center');
</script>

<template>
    <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-border bg-white/80 px-6 shadow-glass backdrop-blur-xl">
        <div class="flex items-center gap-4">
            <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-text-secondary transition-colors hover:bg-background hover:text-text-primary"
                @click="ui.toggleSidebar()"
            >
                <AppIcon :name="ui.sidebarCollapsed ? 'menu' : 'chevron-left'" />
            </button>

            <h1 class="text-section-title">{{ pageTitle }}</h1>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                class="flex items-center gap-2 rounded-lg border border-border bg-surface px-3 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-background"
            >
                <span class="h-2 w-2 rounded-full bg-primary"></span>
                All Sites
                <AppIcon name="chevron-down" class="h-4 w-4 text-text-muted" />
            </button>

            <button
                type="button"
                class="relative flex h-9 w-9 items-center justify-center rounded-lg text-text-secondary transition-colors hover:bg-background hover:text-text-primary"
            >
                <AppIcon name="bell" />
                <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-maroon"></span>
            </button>

            <button
                type="button"
                class="flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors hover:bg-background"
            >
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-soft text-primary">
                    <AppIcon name="user" class="h-4 w-4" />
                </div>
                <span class="text-sm font-medium text-text-secondary">Guest</span>
                <AppIcon name="chevron-down" class="h-4 w-4 text-text-muted" />
            </button>
        </div>
    </header>
</template>
