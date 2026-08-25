<script setup>
import { onMounted, ref, watch } from 'vue';
import { useNotificationStore } from '../stores/notification';
import { NOTIFICATION_TYPES, notificationIcon, notificationSeverityClass } from '../config/notificationTypes';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiStatus from '../components/ui/UiStatus.vue';
import UiButton from '../components/ui/UiButton.vue';
import AppIcon from '../components/layout/AppIcon.vue';

const notifications = useNotificationStore();

const STATUS_OPTIONS = ['all', 'unread', 'read'];
const statusFilter = ref('all');
const typeFilter = ref('all');

function formatTime(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}

function load(page = 1) {
    notifications.fetchNotifications({
        page,
        perPage: notifications.pagination?.perPage ?? 20,
        status: statusFilter.value,
        type: typeFilter.value,
    });
}

watch([statusFilter, typeFilter], () => load(1));

onMounted(() => load(1));
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-page-title">Notification Center</h1>
            <p class="text-body mt-2 max-w-2xl">
                {{ notifications.unreadCount }} unread notification{{ notifications.unreadCount === 1 ? '' : 's' }}.
            </p>
        </div>

        <UiCard padding="sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-for="option in STATUS_OPTIONS"
                        :key="option"
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium capitalize transition-colors"
                        :class="
                            statusFilter === option
                                ? 'bg-primary text-white'
                                : 'border border-border bg-surface text-text-secondary hover:bg-background'
                        "
                        @click="statusFilter = option"
                    >
                        {{ option }}
                    </button>

                    <select
                        v-model="typeFilter"
                        class="rounded-md border border-border bg-surface px-3 py-1.5 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40"
                    >
                        <option value="all">All types</option>
                        <option v-for="opt in NOTIFICATION_TYPES" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                </div>

                <UiButton
                    v-if="notifications.unreadCount > 0"
                    variant="secondary"
                    size="sm"
                    @click="notifications.markAllAsRead()"
                >
                    Mark all as read
                </UiButton>
            </div>
        </UiCard>

        <UiCard padding="sm">
            <div v-if="notifications.loading" class="py-8 text-center text-body">Loading notifications…</div>
            <div v-else-if="notifications.error" class="py-8 text-center text-sm font-medium text-danger">
                {{ notifications.error }}
            </div>
            <div v-else-if="notifications.notifications.length === 0" class="py-8 text-center text-body">
                No notifications match the current filters.
            </div>
            <div v-else class="divide-y divide-border">
                <div
                    v-for="item in notifications.notifications"
                    :key="item.id"
                    class="flex items-start gap-3 px-2 py-3"
                    :class="!item.read_at ? 'bg-primary-soft/30' : ''"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                        :class="notificationSeverityClass(item.severity)"
                    >
                        <AppIcon :name="notificationIcon(item.type)" class="h-4 w-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-medium text-text-primary">{{ item.title }}</p>
                            <UiBadge variant="neutral">{{ item.type }}</UiBadge>
                            <UiStatus
                                :variant="item.read_at ? 'neutral' : 'info'"
                                :label="item.read_at ? 'Read' : 'Unread'"
                            />
                        </div>
                        <p class="text-body mt-0.5">{{ item.message }}</p>
                        <p class="mt-1 text-caption">{{ formatTime(item.created_at) }}</p>
                    </div>
                    <UiButton v-if="!item.read_at" variant="ghost" size="sm" @click="notifications.markAsRead(item.id)">
                        Mark as read
                    </UiButton>
                </div>
            </div>

            <div
                v-if="notifications.pagination && notifications.pagination.lastPage > 1"
                class="mt-4 flex items-center justify-between border-t border-border pt-4"
            >
                <p class="text-caption">
                    Page {{ notifications.pagination.currentPage }} of {{ notifications.pagination.lastPage }}
                </p>
                <div class="flex gap-2">
                    <UiButton
                        variant="secondary"
                        size="sm"
                        :disabled="notifications.pagination.currentPage <= 1"
                        @click="load(notifications.pagination.currentPage - 1)"
                    >
                        Previous
                    </UiButton>
                    <UiButton
                        variant="secondary"
                        size="sm"
                        :disabled="notifications.pagination.currentPage >= notifications.pagination.lastPage"
                        @click="load(notifications.pagination.currentPage + 1)"
                    >
                        Next
                    </UiButton>
                </div>
            </div>
        </UiCard>
    </div>
</template>
