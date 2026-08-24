<script setup>
import UiCard from '../components/ui/UiCard.vue';
import UiKpiCard from '../components/ui/UiKpiCard.vue';
import ModuleCard from '../components/modules/ModuleCard.vue';
import ModuleSiteMatrix from '../components/modules/ModuleSiteMatrix.vue';
import { useModulesOverview } from '../composables/useModulesOverview';

const { search, selectedModule, moduleCatalog, modules, visibleModules, summary, filteredSites } =
    useModulesOverview();

const fieldClasses =
    'rounded-md border border-border bg-surface px-3 py-2 text-sm text-text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40';
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-page-title">Modules</h1>
            <p class="text-body mt-2 max-w-2xl">
                Group-wide overview of OneClick module adoption and availability across all PPA
                sites.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <UiKpiCard title="Total Modules" :value="summary.totalModules" />
            <UiKpiCard title="Total Sites" :value="summary.totalSites" />
            <UiKpiCard title="Active Deployments" :value="summary.activeDeployments" />
            <UiKpiCard title="Overall Adoption" :value="summary.overallAdoption" unit="%" />
        </div>

        <UiCard padding="sm">
            <div class="flex flex-wrap items-center gap-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search sites by code, name, or location…"
                    :class="fieldClasses"
                    class="min-w-[220px] flex-1"
                />

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            selectedModule === 'all'
                                ? 'bg-primary text-white'
                                : 'border border-border bg-surface text-text-secondary hover:bg-background'
                        "
                        @click="selectedModule = 'all'"
                    >
                        All
                    </button>
                    <button
                        v-for="mod in moduleCatalog"
                        :key="mod.key"
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            selectedModule === mod.key
                                ? 'bg-primary text-white'
                                : 'border border-border bg-surface text-text-secondary hover:bg-background'
                        "
                        @click="selectedModule = mod.key"
                    >
                        {{ mod.label }}
                    </button>
                </div>
            </div>
        </UiCard>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <ModuleCard
                v-for="mod in visibleModules"
                :key="mod.key"
                :module="mod"
                :highlighted="selectedModule === mod.key"
            />
        </div>

        <ModuleSiteMatrix :modules="modules" :sites="filteredSites" :selected-module="selectedModule" />
    </div>
</template>
