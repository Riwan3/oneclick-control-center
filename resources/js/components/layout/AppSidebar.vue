<script setup>
import { useRoute } from 'vue-router';
import { useUiStore } from '../../stores/ui';
import { navigationItems } from '../../config/navigation';
import AppIcon from './AppIcon.vue';
import UiTooltip from '../ui/UiTooltip.vue';

const ui = useUiStore();
const route = useRoute();
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col border-r border-border bg-white/80 shadow-glass backdrop-blur-xl transition-[width] duration-200"
        :class="ui.sidebarCollapsed ? 'w-20' : 'w-64'"
    >
        <div class="flex h-16 items-center gap-3 border-b border-border px-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-maroon text-sm font-bold text-white">
                OC
            </div>
            <div v-if="!ui.sidebarCollapsed" class="min-w-0 leading-tight">
                <p class="truncate text-sm font-semibold text-text-primary">OneClick</p>
                <p class="truncate text-xs text-text-muted">Control Center</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <router-link
                v-for="item in navigationItems"
                :key="item.to"
                :to="item.to"
                class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-text-secondary transition-colors hover:bg-background hover:text-text-primary"
                :class="[
                    route.path === item.to ? 'bg-primary-soft text-primary hover:bg-primary-soft hover:text-primary' : '',
                    ui.sidebarCollapsed ? 'justify-center' : '',
                ]"
            >
                <span
                    v-if="route.path === item.to"
                    class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-maroon"
                ></span>

                <UiTooltip v-if="ui.sidebarCollapsed" :content="item.label" position="right">
                    <AppIcon :name="item.icon" class="shrink-0" />
                </UiTooltip>
                <AppIcon v-else :name="item.icon" class="shrink-0" />

                <span v-if="!ui.sidebarCollapsed" class="truncate">{{ item.label }}</span>
            </router-link>
        </nav>

        <div class="border-t border-border p-3">
            <div
                class="flex items-center gap-3 rounded-lg px-2 py-2"
                :class="ui.sidebarCollapsed ? 'justify-center' : ''"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-background text-text-muted">
                    <AppIcon name="user" />
                </div>
                <div v-if="!ui.sidebarCollapsed" class="min-w-0 leading-tight">
                    <p class="truncate text-sm font-medium text-text-secondary">Guest User</p>
                    <p class="truncate text-xs text-text-muted">No role assigned</p>
                </div>
            </div>
        </div>
    </aside>
</template>
