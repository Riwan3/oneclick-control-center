<script setup>
import { useRoute } from 'vue-router';
import { useUiStore } from '../../stores/ui';
import { navigationItems } from '../../config/navigation';
import AppIcon from './AppIcon.vue';

const ui = useUiStore();
const route = useRoute();
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col border-r border-slate-200/70 bg-white/80 backdrop-blur-xl transition-[width] duration-200"
        :class="ui.sidebarCollapsed ? 'w-20' : 'w-64'"
    >
        <div class="flex h-16 items-center gap-3 border-b border-slate-200/70 px-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-maroon-600 text-sm font-bold text-white">
                OC
            </div>
            <div v-if="!ui.sidebarCollapsed" class="min-w-0 leading-tight">
                <p class="truncate text-sm font-semibold text-slate-800">OneClick</p>
                <p class="truncate text-xs text-slate-400">Control Center</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <router-link
                v-for="item in navigationItems"
                :key="item.to"
                :to="item.to"
                class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
                :class="[
                    route.path === item.to ? 'bg-blue-50 text-blue-700 hover:bg-blue-50 hover:text-blue-700' : '',
                    ui.sidebarCollapsed ? 'justify-center' : '',
                ]"
            >
                <span
                    v-if="route.path === item.to"
                    class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-maroon-600"
                ></span>

                <AppIcon :name="item.icon" class="shrink-0" />

                <span v-if="!ui.sidebarCollapsed" class="truncate">{{ item.label }}</span>

                <span
                    v-if="ui.sidebarCollapsed"
                    class="pointer-events-none absolute left-full top-1/2 z-40 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100"
                >
                    {{ item.label }}
                </span>
            </router-link>
        </nav>

        <div class="border-t border-slate-200/70 p-3">
            <div
                class="flex items-center gap-3 rounded-lg px-2 py-2"
                :class="ui.sidebarCollapsed ? 'justify-center' : ''"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <AppIcon name="user" />
                </div>
                <div v-if="!ui.sidebarCollapsed" class="min-w-0 leading-tight">
                    <p class="truncate text-sm font-medium text-slate-700">Guest User</p>
                    <p class="truncate text-xs text-slate-400">No role assigned</p>
                </div>
            </div>
        </div>
    </aside>
</template>
