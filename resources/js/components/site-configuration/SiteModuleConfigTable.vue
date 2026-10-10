<script setup>
import UiCard from '../ui/UiCard.vue';
import UiStatus from '../ui/UiStatus.vue';
import UiBadge from '../ui/UiBadge.vue';

defineProps({
    modules: {
        type: Array,
        required: true,
        // shape: [{ module_key, label, baselineEnabled, siteEnabled }]
    },
    selectedModuleKey: {
        type: String,
        default: null,
    },
    canManage: {
        type: Boolean,
        default: false,
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['select', 'toggle']);

// A module has an override if and only if its effective (siteEnabled)
// state differs from the baseline default — the backend's sparse
// override model (SiteConfigurationController) only ever persists an
// override row when the requested state deviates from baseline, and
// deletes it when a toggle reverts to baseline. So this comparison is
// not a guess — it's the exact same rule the backend already enforces,
// read from data useSiteConfiguration() already exposes (baselineEnabled,
// siteEnabled), without a second governance resolver.
function hasOverride(mod) {
    return mod.siteEnabled !== mod.baselineEnabled;
}
</script>

<template>
    <UiCard padding="sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-caption">
                        <th class="px-3 py-2">Module</th>
                        <th class="px-3 py-2">Baseline</th>
                        <th class="px-3 py-2">Override</th>
                        <th class="px-3 py-2">Effective</th>
                        <th class="px-3 py-2">Source</th>
                        <th class="px-3 py-2 text-right">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="mod in modules"
                        :key="mod.module_key"
                        class="cursor-pointer border-b border-border last:border-0 hover:bg-background"
                        :class="mod.module_key === selectedModuleKey ? 'bg-primary-soft/40' : ''"
                        @click="$emit('select', mod.module_key)"
                    >
                        <td class="px-3 py-2 font-medium text-text-primary">{{ mod.label }}</td>
                        <td class="px-3 py-2">
                            <UiStatus :variant="mod.baselineEnabled ? 'success' : 'neutral'" :label="mod.baselineEnabled ? 'Enabled' : 'Disabled'" />
                        </td>
                        <td class="px-3 py-2">
                            <span v-if="!hasOverride(mod)" class="text-caption">None</span>
                            <UiStatus v-else :variant="mod.siteEnabled ? 'success' : 'neutral'" :label="mod.siteEnabled ? 'Enabled' : 'Disabled'" />
                        </td>
                        <td class="px-3 py-2">
                            <UiStatus :variant="mod.siteEnabled ? 'success' : 'neutral'" :label="mod.siteEnabled ? 'Enabled' : 'Disabled'" />
                        </td>
                        <td class="px-3 py-2">
                            <UiBadge :variant="hasOverride(mod) ? 'info' : 'neutral'">
                                {{ hasOverride(mod) ? 'Site Override' : 'Inherited' }}
                            </UiBadge>
                        </td>
                        <td class="px-3 py-2 text-right" @click.stop>
                            <input
                                type="checkbox"
                                :checked="mod.siteEnabled"
                                :disabled="!canManage || saving"
                                :aria-label="`Toggle ${mod.label}`"
                                @change="$emit('toggle', mod.module_key, $event.target.checked)"
                            />
                        </td>
                    </tr>
                    <tr v-if="!modules.length">
                        <td colspan="6" class="px-3 py-6 text-center text-caption">No modules in the registry.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </UiCard>
</template>
