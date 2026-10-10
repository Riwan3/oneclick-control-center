<script setup>
import { ref, computed, watch } from 'vue';
import UiCard from '../ui/UiCard.vue';
import UiButton from '../ui/UiButton.vue';
import UiStatus from '../ui/UiStatus.vue';
import SidebarManifest from '../module-observatory/SidebarManifest.vue';

const props = defineProps({
    snapshot: {
        type: Object,
        default: null,
    },
    modules: {
        type: Array,
        default: () => [],
        // shape: [{ moduleKey, label, enabled }]
    },
    menusForModule: {
        type: Function,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);

const selectedModuleKey = ref(null);

watch(
    () => props.snapshot?.id,
    () => {
        selectedModuleKey.value = props.modules[0]?.moduleKey ?? null;
    },
    { immediate: true }
);

const selectedModule = computed(() => props.modules.find((mod) => mod.moduleKey === selectedModuleKey.value) ?? null);
const selectedModuleMenus = computed(() => (selectedModuleKey.value ? props.menusForModule(selectedModuleKey.value) : []));
</script>

<template>
    <UiCard v-if="loading">
        <p class="text-caption">Loading snapshot…</p>
    </UiCard>

    <div v-else-if="snapshot" class="space-y-4">
        <UiCard padding="sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-label">
                        SNAP-{{ String(snapshot.snapshotNumber).padStart(6, '0') }}
                    </p>
                    <p class="text-caption mt-1">
                        {{ snapshot.site.code }} · Created {{ new Date(snapshot.createdAt).toLocaleString() }}
                        <span v-if="snapshot.createdBy"> by {{ snapshot.createdBy.name }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <UiStatus variant="neutral" label="Immutable" />
                    <UiButton size="sm" variant="ghost" @click="$emit('close')">Close</UiButton>
                </div>
            </div>
            <p class="text-caption mt-3">
                This is a frozen, read-only record of {{ snapshot.site.code }}'s effective configuration at the
                moment it was created — later Site Configuration changes never alter it.
            </p>
        </UiCard>

        <!-- Read-only module list — NO checkboxes/toggles, matching the
             immutability rule that snapshot detail has no edit controls. -->
        <UiCard padding="sm">
            <p class="text-label mb-3 px-1">Modules ({{ snapshot.moduleCount }} / {{ modules.length }} enabled)</p>
            <ul class="divide-y divide-border">
                <li
                    v-for="mod in modules"
                    :key="mod.moduleKey"
                    class="flex cursor-pointer items-center justify-between px-2 py-2 text-sm hover:bg-background"
                    :class="mod.moduleKey === selectedModuleKey ? 'bg-primary-soft/40' : ''"
                    @click="selectedModuleKey = mod.moduleKey"
                >
                    <span class="font-medium text-text-primary">{{ mod.label }}</span>
                    <span :class="mod.enabled ? 'text-success' : 'text-text-muted'">{{ mod.enabled ? '✓' : '✕' }}</span>
                </li>
            </ul>
        </UiCard>

        <SidebarManifest
            v-if="selectedModule"
            :title="`${selectedModule.label} — Snapshot Sidebar`"
            :menus="selectedModuleMenus"
        />
    </div>
</template>
