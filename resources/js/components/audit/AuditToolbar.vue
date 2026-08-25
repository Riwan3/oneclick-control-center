<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['filter']);

const filters = ref({
    search: '',
    action: 'all',
    date: 'all',
    user_id: 'all',
});

const fieldClasses =
    'rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';

let timeout;
watch(
    filters,
    (newVal) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            emit('filter', { ...newVal });
        }, 300);
    },
    { deep: true }
);
</script>

<template>
    <div class="flex flex-wrap items-center gap-3">
        <div class="w-full sm:w-auto flex-1 min-w-[200px]">
            <input
                v-model="filters.search"
                type="text"
                placeholder="Search audit trail..."
                :class="fieldClasses + ' w-full'"
            />
        </div>

        <div class="w-full sm:w-auto">
            <select v-model="filters.action" :class="fieldClasses + ' w-full sm:w-auto'">
                <option value="all">All Actions</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
                <option value="login_failed">Login Failed</option>
                <option value="user_created">User Created</option>
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <select v-model="filters.date" :class="fieldClasses + ' w-full sm:w-auto'">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="last_7_days">Last 7 Days</option>
                <option value="last_30_days">Last 30 Days</option>
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <select v-model="filters.user_id" :class="fieldClasses + ' w-full sm:w-auto'">
                <option value="all">All Users</option>
                <option v-for="u in users" :key="u.id" :value="u.id">
                    {{ u.name }} ({{ u.username }})
                </option>
            </select>
        </div>
    </div>
</template>

