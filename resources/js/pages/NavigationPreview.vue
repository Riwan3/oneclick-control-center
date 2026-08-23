<script setup>
// Temporary preview for validating the STEP 3F navigation architecture.
// Not linked in sidebar navigation. Safe to remove once reviewed.
import { useAuthStore, CONTROL_CENTER_ROLES } from '../stores/auth';
import { useSiteStore } from '../stores/site';
import { useNavigation } from '../composables/useNavigation';
import UiCard from '../components/ui/UiCard.vue';
import UiButton from '../components/ui/UiButton.vue';
import UiBadge from '../components/ui/UiBadge.vue';
import UiStatus from '../components/ui/UiStatus.vue';
import AppIcon from '../components/layout/AppIcon.vue';

const auth = useAuthStore();
const site = useSiteStore();
const { groups } = useNavigation();

const roleLabels = {
    super_admin: 'Super Admin',
    management: 'Management',
    view: 'View',
};
</script>

<template>
    <div class="space-y-8">
        <UiCard>
            <h2 class="text-page-title">Navigation Architecture Preview</h2>
            <p class="text-body mt-2 max-w-2xl">
                Temporary page for validating the STEP 3F dynamic navigation architecture. Switch
                the mock role/site below and watch the resolved navigation react — the real
                sidebar reacts to the same state. Not part of the application navigation.
            </p>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Mock role (stores/auth.js)</h3>
            <div class="flex flex-wrap gap-3">
                <UiButton
                    v-for="r in CONTROL_CENTER_ROLES"
                    :key="r"
                    :variant="auth.role === r ? 'primary' : 'secondary'"
                    @click="auth.setRole(r)"
                >
                    {{ roleLabels[r] }}
                </UiButton>
            </div>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Mock site (stores/site.js)</h3>
            <div class="flex flex-wrap gap-3">
                <UiButton
                    :variant="!site.currentSite ? 'primary' : 'secondary'"
                    @click="site.selectSite(null)"
                >
                    All Sites
                </UiButton>
                <UiButton
                    v-for="s in site.sites"
                    :key="s.id"
                    :variant="site.currentSite?.id === s.id ? 'primary' : 'secondary'"
                    @click="site.selectSite(s.id)"
                >
                    {{ s.name }}
                </UiButton>
            </div>
            <p v-if="site.currentSite" class="text-caption mt-4">
                Modules enabled for {{ site.currentSite.name }}:
                <UiBadge
                    v-for="(enabled, key) in site.currentSite.modules"
                    :key="key"
                    :variant="enabled ? 'success' : 'neutral'"
                    class="ml-1"
                >
                    {{ key }}
                </UiBadge>
            </p>
        </UiCard>

        <UiCard>
            <h3 class="text-section-title mb-4">Resolved navigation (mirrors the sidebar)</h3>
            <div class="space-y-5">
                <div v-for="group in groups" :key="group.section ?? 'top'">
                    <p class="text-label mb-2">{{ group.section ?? 'Top level' }}</p>
                    <div class="space-y-1">
                        <div
                            v-for="item in group.items"
                            :key="item.id"
                            class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2"
                        >
                            <div class="flex items-center gap-2">
                                <AppIcon
                                    :name="item.icon"
                                    class="h-4 w-4"
                                    :class="item.available ? 'text-text-secondary' : 'text-text-muted'"
                                />
                                <span
                                    class="text-sm font-medium"
                                    :class="item.available ? 'text-text-primary' : 'text-text-muted'"
                                >
                                    {{ item.label }}
                                </span>
                                <UiBadge v-if="item.module" variant="info">{{ item.module }}</UiBadge>
                            </div>
                            <UiStatus
                                :variant="item.available ? 'success' : 'neutral'"
                                :label="item.available ? 'Available' : 'Soon'"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </UiCard>
    </div>
</template>
