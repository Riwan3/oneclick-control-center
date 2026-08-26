<script setup>
import UiCard from '../ui/UiCard.vue';
import UiStatus from '../ui/UiStatus.vue';

defineProps({
    moduleName: {
        type: String,
        required: true,
    },
    baselineVersion: {
        type: String,
        required: true,
    },
    latestVersion: {
        type: String,
        default: null,
    },
    overallStatus: {
        type: String,
        required: true,
        validator: (v) => ['synced', 'updated', 'changed', 'drifted', 'not_synced'].includes(v),
    },
    lastSyncedAt: {
        type: String,
        default: null,
    },
});

const STATUS_VARIANT = {
    synced: 'success',
    updated: 'info',
    changed: 'warning',
    drifted: 'danger',
    not_synced: 'neutral',
};

const STATUS_LABEL = {
    synced: 'Synced',
    updated: 'Updated',
    changed: 'Changed',
    drifted: 'Drifted',
    not_synced: 'Not Synced',
};
</script>

<template>
    <UiCard>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-label">Module Observatory</p>
                <h1 class="text-page-title mt-1">{{ moduleName }}</h1>
                <p class="text-caption mt-1">
                    Baseline {{ baselineVersion }}
                    <span v-if="latestVersion && latestVersion !== baselineVersion">
                        · Latest observed {{ latestVersion }}
                    </span>
                </p>
            </div>

            <div class="text-right">
                <UiStatus :variant="STATUS_VARIANT[overallStatus]" :label="STATUS_LABEL[overallStatus]" />
                <p v-if="lastSyncedAt" class="text-caption mt-2">Last synchronized {{ lastSyncedAt }}</p>
            </div>
        </div>
    </UiCard>
</template>
