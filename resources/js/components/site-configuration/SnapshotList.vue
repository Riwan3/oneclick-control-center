<script setup>
import { ref } from 'vue';
import UiCard from '../ui/UiCard.vue';
import UiButton from '../ui/UiButton.vue';
import UiStatus from '../ui/UiStatus.vue';

defineProps({
    snapshots: {
        type: Array,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    creating: {
        type: Boolean,
        default: false,
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['create', 'open']);

// A snapshot is a real, non-reversible historical record — a
// two-step confirm avoids creating one from a stray click, without
// resorting to a native browser confirm() dialog.
const confirming = ref(false);

function requestCreate() {
    if (!confirming.value) {
        confirming.value = true;
        return;
    }
    confirming.value = false;
    emit('create');
}

function cancelCreate() {
    confirming.value = false;
}

function formatDate(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<template>
    <UiCard padding="sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 px-1">
            <p class="text-label">Snapshots</p>

            <div v-if="canManage" class="flex items-center gap-2">
                <template v-if="confirming">
                    <span class="text-caption">Create a new immutable snapshot?</span>
                    <UiButton size="sm" variant="secondary" :disabled="creating" @click="cancelCreate">Cancel</UiButton>
                    <UiButton size="sm" variant="primary" :disabled="creating" @click="requestCreate">
                        {{ creating ? 'Creating…' : 'Confirm' }}
                    </UiButton>
                </template>
                <UiButton v-else size="sm" variant="primary" @click="requestCreate">Create Snapshot</UiButton>
            </div>
        </div>

        <p v-if="loading" class="px-1 text-caption">Loading snapshots…</p>

        <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border text-caption">
                        <th class="px-3 py-2">Snapshot</th>
                        <th class="px-3 py-2">Created</th>
                        <th class="px-3 py-2">Created By</th>
                        <th class="px-3 py-2">Modules</th>
                        <th class="px-3 py-2">Menus</th>
                        <th class="px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="snapshot in snapshots"
                        :key="snapshot.id"
                        class="cursor-pointer border-b border-border last:border-0 hover:bg-background"
                        @click="$emit('open', snapshot.id)"
                    >
                        <td class="px-3 py-2 font-medium text-text-primary">
                            SNAP-{{ String(snapshot.snapshotNumber).padStart(6, '0') }}
                        </td>
                        <td class="px-3 py-2 text-text-secondary">{{ formatDate(snapshot.createdAt) }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ snapshot.createdBy?.name ?? '—' }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ snapshot.moduleCount }}</td>
                        <td class="px-3 py-2 text-text-secondary">{{ snapshot.menuCount }}</td>
                        <td class="px-3 py-2"><UiStatus variant="neutral" label="Immutable" /></td>
                    </tr>
                    <tr v-if="!snapshots.length">
                        <td colspan="6" class="px-3 py-6 text-center text-caption">No snapshots yet for this site.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </UiCard>
</template>
