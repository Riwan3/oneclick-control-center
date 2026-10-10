<script setup>
import { useAuthStore } from '../../stores/auth';
import UiCard from '../ui/UiCard.vue';

const auth = useAuthStore();

defineProps({
    modules: {
        type: Array,
        required: true,
    },
    sites: {
        type: Array,
        required: true,
    },
    selectedModule: {
        type: String,
        default: 'all',
    },
});
</script>

<template>
    <UiCard padding="sm">
        <div class="mb-3 flex flex-wrap items-center gap-6 text-caption">
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-success"></span>
                Active Deployment
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block h-2.5 w-2.5 rounded-full border-2 border-primary bg-primary/10"></span>
                Configured / Pending Deployment
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block h-2.5 w-2.5 rounded-full border border-border bg-neutral-soft"></span>
                Disabled / Not Configured
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-warning ring-2 ring-warning/30"></span>
                Policy Violation / Unentitled
            </span>
        </div>

        <div v-if="sites.length" class="overflow-x-auto">
            <table class="w-full min-w-[640px] border-collapse text-sm">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-surface px-3 py-2 text-left text-label align-bottom">Site</th>
                        <th
                            v-for="mod in modules"
                            :key="mod.key"
                            class="px-2 py-2 text-center align-bottom"
                            :class="selectedModule === mod.key ? 'bg-primary-soft' : ''"
                        >
                            <span class="text-label">{{ mod.label }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="site in sites" :key="site.id">
                        <td class="sticky left-0 z-10 bg-inherit px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-text-primary">{{ site.code }}</span>
                                <router-link
                                    v-if="auth.can('site_configuration_manage')"
                                    to="/site-configuration"
                                    class="text-[10px] font-medium text-primary hover:text-primary-hover hover:underline"
                                    title="Configure Site"
                                >
                                    Configure
                                </router-link>
                            </div>
                        </td>
                        <td
                            v-for="mod in modules"
                            :key="mod.key"
                            class="px-2 py-2 text-center"
                            :class="selectedModule === mod.key ? 'bg-primary-soft' : ''"
                        >
                            <span
                                v-if="site.effectiveModules?.[mod.key]?.effectiveStatus === 'active'"
                                class="inline-block h-2.5 w-2.5 rounded-full bg-success"
                                :title="`${mod.label} — ${site.code}: Active Deployment`"
                            ></span>
                            <span
                                v-else-if="site.effectiveModules?.[mod.key]?.effectiveStatus === 'pending'"
                                class="inline-block h-2.5 w-2.5 rounded-full border-2 border-primary bg-primary/10"
                                :title="`${mod.label} — ${site.code}: Configured (Pending Deployment)`"
                            ></span>
                            <span
                                v-else-if="site.effectiveModules?.[mod.key]?.effectiveStatus === 'policy_violation'"
                                class="inline-block h-2.5 w-2.5 rounded-full bg-warning ring-2 ring-warning/30"
                                :title="`${mod.label} — ${site.code}: POLICY VIOLATION (Running but Disabled by Governance)`"
                            ></span>
                            <span
                                v-else
                                class="inline-block h-2.5 w-2.5 rounded-full border border-border bg-neutral-soft"
                                :title="`${mod.label} — ${site.code}: Disabled / Not Configured`"
                            ></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-else class="py-6 text-center text-body">No sites match the current filters.</p>
    </UiCard>
</template>
