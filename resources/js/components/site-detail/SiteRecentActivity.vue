<script setup>
import UiCard from '../ui/UiCard.vue';
import UiBadge from '../ui/UiBadge.vue';
import AppIcon from '../layout/AppIcon.vue';
import { formatSiteTime, SITE_LOG_CLOCK_IS_UTC } from '../../helpers/siteTime';

const props = defineProps({
    activities: {
        type: Array,
        required: true,
        // shape: [{ occurred_at, type, title, description }] — from the site's login log
    },
    // The site's location decides the time zone the times are shown in (WIB/WITA/WIT).
    location: {
        type: String,
        default: '',
    },
});

const typeVariant = { activity: 'success', system: 'warning' };

function formatTime(iso) {
    return formatSiteTime(iso, props.location, { digitsAreUtc: SITE_LOG_CLOCK_IS_UTC });
}

const typeIcons = {
    system: 'monitoring',
    module: 'dashboard',
    version: 'tag',
    activity: 'trend-up',
};
</script>

<template>
    <UiCard>
        <h3 class="text-card-title">Recent Activity</h3>

        <ul v-if="activities.length" class="mt-4 space-y-3">
            <li
                v-for="(item, index) in activities"
                :key="index"
                class="flex items-start gap-3 rounded-lg border border-border px-3 py-2.5"
            >
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary">
                    <AppIcon :name="typeIcons[item.type] ?? 'monitoring'" class="h-4 w-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-text-primary">{{ item.title }}</p>
                    <p class="text-caption mt-0.5">{{ item.description }}</p>
                    <p class="text-caption mt-0.5">{{ formatTime(item.occurred_at) }}</p>
                </div>
                <UiBadge :variant="typeVariant[item.type] ?? 'neutral'">{{ item.type }}</UiBadge>
            </li>
        </ul>
        <p v-else class="text-body mt-4">No recent activity reported by this site.</p>
    </UiCard>
</template>
