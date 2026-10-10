<script setup>
import { useAuthStore } from '../../stores/auth';
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';
import UiStatus from '../ui/UiStatus.vue';

const auth = useAuthStore();

defineProps({
    module: {
        type: Object,
        required: true,
        // shape: { key, label, description, activeSites, inactiveSites, totalSites, adoption }
    },
    highlighted: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <UiCard :class="highlighted ? 'ring-2 ring-primary' : ''">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-card-title">{{ module.label }}</h3>
                <p class="text-caption mt-1">{{ module.description }}</p>
            </div>
            <UiBadge variant="info">{{ module.adoption }}%</UiBadge>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 rounded-lg bg-background p-2.5 border border-border/60 text-center">
            <div>
                <p class="text-[10px] uppercase tracking-wider font-semibold text-text-muted">Configured</p>
                <p class="text-xs font-semibold text-text-primary mt-0.5">{{ module.configuredSites }}/{{ module.totalSites }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider font-semibold text-text-muted">Raw Active</p>
                <p class="text-xs font-semibold text-text-primary mt-0.5">{{ module.rawActiveSites }}/{{ module.totalSites }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider font-semibold text-text-muted">Effective</p>
                <p class="text-xs font-semibold text-primary mt-0.5">{{ module.effectiveActiveSites }}/{{ module.totalSites }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider font-semibold text-text-muted">Pending</p>
                <p class="text-xs font-semibold text-text-primary mt-0.5">{{ module.pendingDeploymentSites }}/{{ module.totalSites }}</p>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <UiStatus :variant="module.effectiveActiveSites > 0 ? 'success' : 'neutral'" :label="`Effective Active ${module.effectiveActiveSites}`" />
            <UiBadge v-if="module.policyViolationSites > 0" variant="warning">
                {{ module.policyViolationSites }} Policy Violation
            </UiBadge>
        </div>

        <div
            v-if="auth.can('module_view') || auth.can('change_log_view')"
            class="mt-4 flex items-center gap-3 border-t border-border pt-3"
        >
            <router-link
                v-if="auth.can('module_view')"
                :to="`/module-observatory?module=${module.key}`"
                class="text-xs font-medium text-primary hover:text-primary-hover hover:underline"
            >
                Observe
            </router-link>
            <router-link
                v-if="auth.can('change_log_view')"
                :to="`/baseline?module=${module.key}`"
                class="text-xs font-medium text-primary hover:text-primary-hover hover:underline"
            >
                Baseline
            </router-link>
        </div>
    </UiCard>
</template>
