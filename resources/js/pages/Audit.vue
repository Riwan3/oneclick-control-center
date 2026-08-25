<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useAuditLogs } from '../composables/useAuditLogs';
import UiCard from '../components/ui/UiCard.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import AuditToolbar from '../components/audit/AuditToolbar.vue';
import AuditTable from '../components/audit/AuditTable.vue';
import AuditDetail from '../components/audit/AuditDetail.vue';

const auth = useAuthStore();
const { logs, summary, users, loading, error, meta, fetchLogs } = useAuditLogs();

const selectedLog = ref(null);
const currentFilters = ref({
    page: 1,
    per_page: 25,
    search: '',
    action: 'all',
    date: 'all',
    user_id: 'all',
});

function handleFilter(newFilters) {
    currentFilters.value = {
        ...currentFilters.value,
        ...newFilters,
        page: 1, // Reset to page 1 on filter change
    };
    fetchLogs(currentFilters.value);
}

function handlePageChange(newPage) {
    currentFilters.value.page = newPage;
    fetchLogs(currentFilters.value);
}

function handleSelectLog(log) {
    selectedLog.value = log;
}

onMounted(() => {
    if (auth.can('audit_view')) {
        fetchLogs(currentFilters.value);
    }
});
</script>

<template>
    <div class="space-y-8">
        <!-- Access Denied Guard -->
        <UiCard v-if="!auth.can('audit_view')" class="text-center py-12">
            <h2 class="text-card-title text-danger mb-2">Access Denied</h2>
            <p class="text-body max-w-md mx-auto">
                You do not have permission to view the Audit Trail governance records.
            </p>
        </UiCard>

        <!-- Audit Trail Page Content -->
        <template v-else>
            <!-- Page Header -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-page-title">Audit Trail</h1>
                    <p class="text-body mt-2 max-w-2xl">
                        Enterprise governance log — tracking administrative, user management, and security authentication actions.
                    </p>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <UiKpiCard
                    title="Total Events"
                    :value="summary.total"
                    description="All recorded audit events"
                />
                <UiKpiCard
                    title="Today"
                    :value="summary.today"
                    description="Events recorded today"
                />
                <UiKpiCard
                    title="Successful Logins"
                    :value="summary.successful_logins"
                    description="Total authentication logins"
                />
                <UiKpiCard
                    title="Failed Logins"
                    :value="summary.failed_logins"
                    description="Failed login attempts"
                />
            </div>

            <!-- Audit Trail Main Card -->
            <UiCard padding="sm" class="space-y-4">
                <!-- Toolbar Filters -->
                <div class="p-3 bg-background/50 rounded-lg border border-border">
                    <AuditToolbar
                        :users="users"
                        :loading="loading"
                        @filter="handleFilter"
                    />
                </div>

                <!-- Error Alert -->
                <div v-if="error" class="p-4 rounded-md bg-danger/10 text-danger text-sm font-medium">
                    {{ error }}
                </div>

                <!-- Audit Table -->
                <AuditTable
                    :logs="logs"
                    :loading="loading"
                    :meta="meta"
                    @select-log="handleSelectLog"
                    @change-page="handlePageChange"
                />
            </UiCard>

            <!-- Audit Detail Modal -->
            <AuditDetail
                :log="selectedLog"
                @close="selectedLog = null"
            />
        </template>
    </div>
</template>
