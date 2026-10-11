<script setup>
import { computed } from 'vue';
import UiCard from '../ui/UiCard.vue';
import { modules as moduleCatalog } from '../../config/modules';

const props = defineProps({
    site: {
        type: Object,
        required: true,
    },
});

const activeModuleCount = computed(
    () => moduleCatalog.filter((mod) => props.site.modules?.[mod.key]).length
);
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">Site Information</h3>
        <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-label">Site Code</dt>
                <dd class="mt-1 text-sm font-medium text-text-primary">{{ site.code }}</dd>
            </div>
            <div>
                <dt class="text-label">Site Name</dt>
                <dd class="mt-1 text-sm font-medium text-text-primary">{{ site.name }}</dd>
            </div>
            <div>
                <dt class="text-label">Location</dt>
                <dd class="mt-1 text-sm font-medium text-text-primary">{{ site.location }}</dd>
            </div>
            <div>
                <dt class="text-label">OneClick Version</dt>
                <dd class="mt-1 text-sm font-medium text-text-primary">{{ site.version }}</dd>
            </div>
            <div>
                <dt class="text-label">Users</dt>
                <dd class="mt-1 text-sm font-medium text-text-primary">{{ site.users.toLocaleString() }}</dd>
            </div>
            <div>
                <dt class="text-label">Active Modules</dt>
                <dd class="mt-1 text-sm font-medium text-text-primary">
                    {{ activeModuleCount }} / {{ moduleCatalog.length }}
                </dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <dt class="text-label">Last Checked</dt>
                <dd class="mt-1 text-sm font-medium text-text-primary">{{ site.lastActivity }}</dd>
            </div>
        </dl>
    </UiCard>
</template>
