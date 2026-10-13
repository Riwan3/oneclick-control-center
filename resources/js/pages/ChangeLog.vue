<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useChangeLog } from '../composables/useChangeLog';
import UiCard from '../components/ui/UiCard.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import DashboardSection from '../components/dashboard/DashboardSection.vue';

const auth = useAuthStore();
const { typeFilter, filteredChangeLog, summary, typeLabel, typeVariant } = useChangeLog();

const filters = [
    { value: 'all', label: 'All' },
    { value: 'feature', label: 'Features' },
    { value: 'improvement', label: 'Improvements' },
    { value: 'bug_fix', label: 'Bug Fixes' },
    { value: 'governance', label: 'Governance' },
];

const executiveSummary = computed(() => [
    { id: 'total', title: 'Total Changes', value: summary.value.total, unit: 'entries', status: 'neutral', statusLabel: 'Recorded' },
    { id: 'features', title: 'Features', value: summary.value.features, unit: 'entries', status: 'info', statusLabel: 'New Capability' },
    { id: 'improvements', title: 'Improvements', value: summary.value.improvements, unit: 'entries', status: 'neutral', statusLabel: 'Refinement' },
    { id: 'bug-fixes', title: 'Bug Fixes', value: summary.value.bugFixes, unit: 'entries', status: 'neutral', statusLabel: 'Corrected' },
]);
</script>

<template>
    <!-- Page-level capability guard — reuses change_log_view, the same
         capability already reserved for this page in config/roles.js
         (true for super_admin + management). No new capability. -->
    <div v-if="auth.can('change_log_view')" class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-page-title">Change Log</h1>
                <p class="text-body mt-2 max-w-2xl">
                    Product changes, improvements, fixes, and governance updates.
                </p>
            </div>
            <UiBadge variant="neutral" class="px-3 py-1 text-xs">Product History</UiBadge>
        </div>

        <!-- Executive Summary -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <UiKpiCard
                v-for="kpi in executiveSummary"
                :key="kpi.id"
                :title="kpi.title"
                :value="kpi.value"
                :unit="kpi.unit"
                :status="kpi.status"
                :status-label="kpi.statusLabel"
            />
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2">
            <button
                v-for="filter in filters"
                :key="filter.value"
                type="button"
                class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                :class="
                    typeFilter === filter.value
                        ? 'bg-primary text-white'
                        : 'border border-border bg-surface text-text-secondary hover:bg-background'
                "
                @click="typeFilter = filter.value"
            >
                {{ filter.label }}
            </button>
        </div>

        <!-- Change Timeline -->
        <DashboardSection
            title="Product Change Timeline"
            description="Changes to the OneClick Control Center product itself — not user activity, release versions, or site deployments."
        >
            <ul v-if="filteredChangeLog.length" class="space-y-3">
                <li v-for="entry in filteredChangeLog" :key="entry.id">
                    <UiCard padding="sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <UiBadge variant="neutral">{{ entry.version }}</UiBadge>
                            <UiBadge :variant="typeVariant[entry.type]">{{ typeLabel[entry.type] }}</UiBadge>
                            <UiBadge variant="success">{{ entry.status }}</UiBadge>
                            <span class="ml-auto text-caption">{{ entry.date }}</span>
                        </div>

                        <h3 class="text-card-title mt-2">{{ entry.title }}</h3>
                        <p class="text-body text-sm mt-1">{{ entry.description }}</p>

                        <p class="text-caption mt-2">
                            <span class="font-semibold text-text-secondary">Impact:</span> {{ entry.impact }}
                        </p>
                    </UiCard>
                </li>
            </ul>

            <UiCard v-else>
                <h3 class="text-card-title">No changes found</h3>
                <p class="text-body mt-1">
                    {{
                        summary.total
                            ? 'No change log entries match the selected filter.'
                            : 'No change log entries have been recorded yet.'
                    }}
                </p>
            </UiCard>
        </DashboardSection>
    </div>

    <UiCard v-else>
        <h2 class="text-page-title">Access Restricted</h2>
        <p class="text-body mt-2 max-w-2xl">
            You don't have permission to view this page. Contact your administrator if you believe
            this is a mistake.
        </p>
        <router-link to="/" class="mt-4 inline-block">
            <UiButton variant="secondary">← Back to Home</UiButton>
        </router-link>
    </UiCard>
</template>
