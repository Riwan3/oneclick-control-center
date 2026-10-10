<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';

defineProps({
    modules: {
        type: Array,
        required: true,
        // shape: [{ key, label, configuredSites, rawActiveSites, effectiveActiveSites, policyViolationSites, totalSites, adoption }]
    },
});
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">Module Deployment & Adoption Overview</h3>
        <p class="text-caption mt-1">Multi-dimensional governance policy vs operational telemetry across baseline modules</p>

        <div class="mt-6 space-y-4">
            <div v-for="mod in modules" :key="mod.key" class="rounded-lg border border-border/50 bg-background/50 p-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-text-primary text-sm">{{ mod.label }}</span>
                        <UiBadge v-if="mod.policyViolationSites > 0" variant="warning">
                            Policy Violation: {{ mod.policyViolationSites }}
                        </UiBadge>
                    </div>
                    <span class="text-xs font-semibold text-primary">{{ mod.adoption }}% Adoption</span>
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-text-muted sm:grid-cols-4">
                    <div>
                        <span>Configured: </span>
                        <span class="font-medium text-text-secondary">{{ mod.configuredSites }} / {{ mod.totalSites }} sites</span>
                    </div>
                    <div>
                        <span>Raw Active: </span>
                        <span class="font-medium text-text-secondary">{{ mod.rawActiveSites }} / {{ mod.totalSites }} sites</span>
                    </div>
                    <div>
                        <span>Effective Active: </span>
                        <span class="font-semibold text-primary">{{ mod.effectiveActiveSites }} / {{ mod.totalSites }} sites</span>
                    </div>
                    <div>
                        <span>Pending: </span>
                        <span class="font-medium text-text-secondary">{{ mod.pendingDeploymentSites }} / {{ mod.totalSites }} sites</span>
                    </div>
                </div>

                <div class="mt-2 h-2 rounded-full bg-border/40 overflow-hidden">
                    <div
                        class="h-2 rounded-full bg-primary transition-all duration-300"
                        :style="{ width: mod.adoption + '%' }"
                    ></div>
                </div>
            </div>
        </div>
    </UiCard>
</template>
