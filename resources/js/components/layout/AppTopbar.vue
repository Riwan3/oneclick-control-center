<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useUiStore } from '../../stores/ui';
import { useAuthStore } from '../../stores/auth';
import { useNotificationStore } from '../../stores/notification';
import { useSiteContextStore } from '../../stores/siteContext';
import { useEffectiveAccess } from '../../composables/useEffectiveAccess';
import { sites as allSites } from '../../data/siteCatalogue';
import { notificationIcon, notificationSeverityClass } from '../../config/notificationTypes';
import AppIcon from './AppIcon.vue';
import UserAvatar from './UserAvatar.vue';

const ui = useUiStore();
const auth = useAuthStore();
const notifications = useNotificationStore();
const siteContext = useSiteContextStore();
const { hasSiteAccess } = useEffectiveAccess();
const route = useRoute();
const router = useRouter();

const pageTitle = computed(() => route.meta.title ?? 'OneClick Control Center');
const menuOpen = ref(false);
const notifOpen = ref(false);
const siteMenuOpen = ref(false);

// Reuses the existing, real site catalogue (data/mock/sites.js) and
// the existing access-scope rule (useEffectiveAccess) — the same
// source and filter every other page already uses. No new site data,
// no API call.
const accessibleSites = computed(() => allSites.filter((site) => hasSiteAccess(site.code)));

function toggleSiteMenu() {
    menuOpen.value = false;
    notifOpen.value = false;
    siteMenuOpen.value = !siteMenuOpen.value;
}

function handleSelectSite(site) {
    siteContext.selectSite(site ? { key: site.code, name: site.name } : null);
    siteMenuOpen.value = false;
}

async function toggleNotifications() {
    menuOpen.value = false;
    siteMenuOpen.value = false;
    notifOpen.value = !notifOpen.value;
    if (notifOpen.value) {
        await notifications.fetchNotifications({ perPage: 8 });
    }
}

function formatTime(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}

function handleNotificationClick(item) {
    if (!item.read_at) notifications.markAsRead(item.id);
    notifOpen.value = false;
    if (item.url) router.push(item.url);
}

async function handleLogout() {
    menuOpen.value = false;
    siteMenuOpen.value = false;
    await auth.logout();
    router.push('/login');
}

onMounted(() => {
    notifications.fetchUnreadCount();
});
</script>

<template>
    <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-border bg-white px-6 shadow-card">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-maroon to-maroon-hover"></div>
        <div class="flex items-center gap-4">
            <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-lg text-text-secondary transition-colors hover:bg-background hover:text-text-primary"
                @click="ui.toggleSidebar()"
            >
                <AppIcon :name="ui.sidebarCollapsed ? 'menu' : 'chevron-left'" />
            </button>

            <p class="text-section-title">{{ pageTitle }}</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="relative">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg border border-border bg-surface px-3 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-background"
                    @click="toggleSiteMenu"
                >
                    <span class="h-2 w-2 rounded-full bg-primary"></span>
                    {{ siteContext.selected.name }}
                    <AppIcon name="chevron-down" class="h-4 w-4 text-text-muted" />
                </button>

                <div v-if="siteMenuOpen" class="fixed inset-0 z-20" @click="siteMenuOpen = false"></div>

                <div
                    v-if="siteMenuOpen"
                    class="absolute left-0 top-full z-30 mt-2 w-64 rounded-lg border border-border bg-surface shadow-elevated"
                >
                    <div class="border-b border-border px-4 py-3">
                        <p class="text-sm font-semibold text-text-primary">Site Context</p>
                        <p class="text-caption">Scope the Control Center to one site</p>
                    </div>

                    <div class="max-h-96 overflow-y-auto py-1">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm transition-colors hover:bg-background"
                            :class="siteContext.selected.key === 'all' ? 'font-semibold text-primary' : 'text-text-secondary'"
                            @click="handleSelectSite(null)"
                        >
                            <span class="h-2 w-2 rounded-full bg-primary"></span>
                            All Sites
                        </button>

                        <button
                            v-for="site in accessibleSites"
                            :key="site.code"
                            type="button"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm transition-colors hover:bg-background"
                            :class="siteContext.selected.key === site.code ? 'font-semibold text-primary' : 'text-text-secondary'"
                            @click="handleSelectSite(site)"
                        >
                            <span class="h-2 w-2 shrink-0 rounded-full border border-border bg-neutral-soft"></span>
                            <span class="min-w-0 truncate">{{ site.code }} — {{ site.name }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative">
                <button
                    type="button"
                    class="relative flex h-9 w-9 items-center justify-center rounded-lg text-text-secondary transition-colors hover:bg-background hover:text-text-primary"
                    @click="toggleNotifications"
                >
                    <AppIcon name="bell" />
                    <span
                        v-if="notifications.unreadCount > 0"
                        class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-maroon px-1 text-[10px] font-semibold leading-none text-white"
                    >
                        {{ notifications.unreadCount > 9 ? '9+' : notifications.unreadCount }}
                    </span>
                </button>

                <div v-if="notifOpen" class="fixed inset-0 z-20" @click="notifOpen = false"></div>

                <div
                    v-if="notifOpen"
                    class="absolute right-0 top-full z-30 mt-2 w-96 rounded-lg border border-border bg-surface shadow-elevated"
                >
                    <div class="flex items-center justify-between border-b border-border px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-text-primary">Notification Center</p>
                            <p class="text-caption">{{ notifications.unreadCount }} unread</p>
                        </div>
                        <button
                            v-if="notifications.unreadCount > 0"
                            type="button"
                            class="text-xs font-medium text-primary hover:text-primary-hover"
                            @click="notifications.markAllAsRead()"
                        >
                            Mark all as read
                        </button>
                    </div>

                    <div class="max-h-96 overflow-y-auto">
                        <div v-if="notifications.loading" class="px-4 py-6 text-center text-body">Loading…</div>
                        <div
                            v-else-if="notifications.notifications.length === 0"
                            class="px-4 py-6 text-center text-body"
                        >
                            No notifications yet.
                        </div>
                        <button
                            v-for="item in notifications.notifications"
                            :key="item.id"
                            type="button"
                            class="flex w-full items-start gap-3 border-b border-border px-4 py-3 text-left transition-colors last:border-0 hover:bg-background"
                            :class="!item.read_at ? 'bg-primary-soft/40' : ''"
                            @click="handleNotificationClick(item)"
                        >
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                :class="notificationSeverityClass(item.severity)"
                            >
                                <AppIcon :name="notificationIcon(item.type)" class="h-4 w-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-text-primary">{{ item.title }}</p>
                                <p class="truncate text-caption">{{ item.message }}</p>
                                <p class="mt-0.5 text-caption">{{ formatTime(item.created_at) }}</p>
                            </div>
                            <span v-if="!item.read_at" class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary"></span>
                        </button>
                    </div>

                    <div class="border-t border-border px-4 py-2 text-center">
                        <router-link
                            to="/notifications"
                            class="text-xs font-medium text-primary hover:text-primary-hover"
                            @click="notifOpen = false"
                        >
                            View all notifications
                        </router-link>
                    </div>
                </div>
            </div>

            <div class="relative">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors hover:bg-background"
                    @click="siteMenuOpen = false; menuOpen = !menuOpen"
                >
                    <UserAvatar :src="auth.user?.avatarUrl" />
                    <span class="text-sm font-medium text-text-secondary">{{ auth.user?.name ?? 'Guest' }}</span>
                    <AppIcon name="chevron-down" class="h-4 w-4 text-text-muted" />
                </button>

                <div v-if="menuOpen" class="fixed inset-0 z-20" @click="menuOpen = false"></div>

                <div
                    v-if="menuOpen"
                    class="absolute right-0 top-full z-30 mt-2 w-52 rounded-lg border border-border bg-surface py-1 shadow-elevated"
                >
                    <div class="border-b border-border px-3 py-2">
                        <p class="truncate text-sm font-medium text-text-primary">{{ auth.user?.name }}</p>
                        <p class="truncate text-caption">{{ auth.roleDefinition?.label }}</p>
                    </div>
                    <router-link
                        to="/profile"
                        class="block w-full px-3 py-2 text-left text-sm text-text-secondary transition-colors hover:bg-background hover:text-text-primary"
                        @click="menuOpen = false"
                    >
                        My Profile
                    </router-link>
                    <button
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm text-text-secondary transition-colors hover:bg-background hover:text-danger"
                        @click="handleLogout"
                    >
                        Sign Out
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>
