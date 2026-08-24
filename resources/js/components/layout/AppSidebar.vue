<script setup>
import { useRoute } from 'vue-router';
import { useUiStore } from '../../stores/ui';
import { useNavigation } from '../../composables/useNavigation';
import AppIcon from './AppIcon.vue';
import UiTooltip from '../ui/UiTooltip.vue';
import UiBadge from '../ui/UiBadge.vue';
import logoUrl from '../../../../Logo-Oneclick.png';

const ui = useUiStore();
const route = useRoute();
const { groups } = useNavigation();
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col border-r border-border bg-white/80 shadow-glass backdrop-blur-xl transition-[width] duration-200"
        :class="ui.sidebarCollapsed ? 'w-20' : 'w-64'"
    >
        <div class="flex h-16 items-center gap-3 border-b border-border px-4">
            <img :src="logoUrl" alt="OneClick Logo" class="h-10 w-10 shrink-0 object-contain" />
            <div v-if="!ui.sidebarCollapsed" class="min-w-0 leading-tight">
                <p class="truncate text-sm font-semibold text-text-primary">OneClick</p>
                <p class="truncate text-xs text-text-muted">Control Center</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <template v-for="group in groups" :key="group.section ?? 'top'">
                <p v-if="group.section && !ui.sidebarCollapsed" class="text-label px-3 pb-1 pt-4">
                    {{ group.section }}
                </p>
                <div v-else-if="group.section" class="mx-3 my-2 border-t border-border/70"></div>

                <template v-for="item in group.items" :key="item.id">
                    <router-link
                        v-if="item.available"
                        :to="item.route"
                        class="group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-text-secondary transition-colors hover:bg-background hover:text-text-primary"
                        :class="[
                            route.path === item.route ? 'bg-primary-soft text-primary hover:bg-primary-soft hover:text-primary' : '',
                            ui.sidebarCollapsed ? 'justify-center' : '',
                        ]"
                    >
                        <span
                            v-if="route.path === item.route"
                            class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-maroon"
                        ></span>

                        <UiTooltip v-if="ui.sidebarCollapsed" :content="item.label" position="right">
                            <AppIcon :name="item.icon" class="shrink-0" />
                        </UiTooltip>
                        <AppIcon v-else :name="item.icon" class="shrink-0" />

                        <span v-if="!ui.sidebarCollapsed" class="truncate">{{ item.label }}</span>
                    </router-link>

                    <div
                        v-else
                        class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-text-muted"
                        :class="ui.sidebarCollapsed ? 'justify-center' : ''"
                    >
                        <UiTooltip v-if="ui.sidebarCollapsed" :content="`${item.label} · Soon`" position="right">
                            <AppIcon :name="item.icon" class="shrink-0 opacity-50" />
                        </UiTooltip>
                        <AppIcon v-else :name="item.icon" class="shrink-0 opacity-50" />

                        <span v-if="!ui.sidebarCollapsed" class="min-w-0 flex-1 truncate">{{ item.label }}</span>

                        <UiBadge v-if="!ui.sidebarCollapsed" variant="neutral" class="shrink-0">
                            Soon
                        </UiBadge>
                    </div>
                </template>
            </template>
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
