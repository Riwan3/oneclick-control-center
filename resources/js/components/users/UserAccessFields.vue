<script setup>
import { watchEffect } from 'vue';
import UiStatus from '../ui/UiStatus.vue';

const props = defineProps({
    // The form's currently selected role — Super Admin always shows the
    // fixed "All Sites / All Modules" state instead of the picker (see
    // the Super Admin UI rule). This is a display-only guard: the
    // backend independently enforces the same rule regardless of what
    // this form ever submits.
    role: {
        type: String,
        required: true,
    },
    siteAccessMode: {
        type: String,
        required: true,
    },
    siteIds: {
        type: Array,
        required: true,
    },
    moduleAccessMode: {
        type: String,
        required: true,
    },
    moduleIds: {
        type: Array,
        required: true,
    },
    sites: {
        type: Array,
        default: () => [],
    },
    modules: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:siteAccessMode', 'update:siteIds', 'update:moduleAccessMode', 'update:moduleIds']);

// SECURITY CORRECTION: non-Super-Admin roles have no "All Sites"/"All
// Modules" choice — access must always be explicit selected sites/
// modules (see StoreUserRequest/UpdateUserRequest, which reject
// anything else with a 422). This keeps the form's mode fields in sync
// with that invariant even for a user record loaded with a legacy
// 'all' value, rather than silently submitting an implicit-all mode.
watchEffect(() => {
    if (props.role === 'super_admin') {
        return;
    }
    if (props.siteAccessMode !== 'selected') {
        emit('update:siteAccessMode', 'selected');
    }
    if (props.moduleAccessMode !== 'selected') {
        emit('update:moduleAccessMode', 'selected');
    }
});

function toggleSite(id) {
    const next = props.siteIds.includes(id) ? props.siteIds.filter((x) => x !== id) : [...props.siteIds, id];
    emit('update:siteIds', next);
}

function toggleModule(id) {
    const next = props.moduleIds.includes(id) ? props.moduleIds.filter((x) => x !== id) : [...props.moduleIds, id];
    emit('update:moduleIds', next);
}
</script>

<template>
    <div class="space-y-6">
        <div v-if="role === 'super_admin'" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <p class="text-label">Site Access</p>
                <UiStatus class="mt-1.5" variant="success" label="All Sites" />
            </div>
            <div>
                <p class="text-label">Module Access</p>
                <UiStatus class="mt-1.5" variant="success" label="All Modules" />
            </div>
        </div>

        <template v-else>
            <div>
                <p class="text-label mb-2">Selected Sites</p>
                <p class="text-caption mb-2">
                    Non-Super-Admin users must have at least one explicit site selected — there is no "All Sites" option
                    for this role.
                </p>

                <div class="grid grid-cols-2 gap-2 rounded-md border border-border p-3 sm:grid-cols-3">
                    <p v-if="sites.length === 0" class="col-span-full text-caption">No sites available.</p>
                    <label v-for="site in sites" :key="site.id" class="flex items-center gap-2 text-sm text-text-secondary">
                        <input type="checkbox" :checked="siteIds.includes(site.id)" @change="toggleSite(site.id)" />
                        {{ site.code }}
                    </label>
                </div>
            </div>

            <div>
                <p class="text-label mb-2">Selected Modules</p>
                <p class="text-caption mb-2">
                    Non-Super-Admin users must have at least one explicit module selected — there is no "All Modules"
                    option for this role.
                </p>

                <div class="grid grid-cols-2 gap-2 rounded-md border border-border p-3 sm:grid-cols-4">
                    <p v-if="modules.length === 0" class="col-span-full text-caption">No modules available.</p>
                    <label v-for="mod in modules" :key="mod.id" class="flex items-center gap-2 text-sm text-text-secondary">
                        <input type="checkbox" :checked="moduleIds.includes(mod.id)" @change="toggleModule(mod.id)" />
                        {{ mod.label }}
                    </label>
                </div>
            </div>
        </template>
    </div>
</template>
