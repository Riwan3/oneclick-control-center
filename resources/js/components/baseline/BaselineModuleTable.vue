<script setup>
import UiCard from '../ui/UiCard.vue';
import UiStatus from '../ui/UiStatus.vue';
import UiBadge from '../ui/UiBadge.vue';

defineProps({
    modules: {
        type: Array,
        required: true,
    },
    selectedModuleKey: {
        type: String,
        default: null,
    },
});

defineEmits(['select']);

// Two distinct axes — NEVER folded into a single "status": a module's
// business baseline can be fully verified straight from OneClick.docx
// while its technical Laravel cross-check remains unavailable in this
// repository. Conflating the two was the exact mistake this correction
// fixes.
const BASELINE_VARIANT = {
    verified: 'success',
    unverified: 'neutral',
};
const BASELINE_LABEL = {
    verified: 'Verified',
    unverified: 'Unverified',
};

const MAPPING_VARIANT = {
    verified: 'success',
    partial: 'warning',
    not_connected: 'neutral',
};
const MAPPING_LABEL = {
    verified: 'Verified',
    partial: 'Partial',
    not_connected: 'Not Connected',
};
</script>

<template>
    <UiCard padding="sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-caption">
                        <th class="px-3 py-2">Module</th>
                        <th class="px-3 py-2">Key</th>
                        <th class="px-3 py-2">Sidebar Count</th>
                        <th class="px-3 py-2">Business Baseline</th>
                        <th class="px-3 py-2">Technical Mapping</th>
                        <th class="px-3 py-2">Enabled</th>
                        <th class="px-3 py-2">Order</th>
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
                        <td class="px-3 py-2 text-text-secondary">{{ mod.module_key }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ mod.sidebarCount }}</td>
                        <td class="px-3 py-2">
                            <UiStatus :variant="BASELINE_VARIANT[mod.businessBaseline]" :label="BASELINE_LABEL[mod.businessBaseline]" />
                        </td>
                        <td class="px-3 py-2">
                            <UiBadge :variant="MAPPING_VARIANT[mod.technicalMapping]">{{ MAPPING_LABEL[mod.technicalMapping] }}</UiBadge>
                        </td>
                        <td class="px-3 py-2 text-text-secondary">{{ mod.enabled ? 'Active' : 'Inactive' }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ mod.order }}</td>
                    </tr>
                    <tr v-if="!modules.length">
                        <td colspan="7" class="px-3 py-6 text-center text-caption">No modules in the registry.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </UiCard>
</template>
