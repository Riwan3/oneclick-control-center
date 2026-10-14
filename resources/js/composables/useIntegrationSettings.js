// API Configuration business logic — Page -> Composable -> Service. Holds the
// real, read-only integration settings; nothing here is editable or simulated.
import { ref, computed, onMounted } from 'vue';
import { integrationSettingsService } from '../services/integrationSettingsService';

function formatTimestamp(iso) {
    if (!iso) return null;
    return new Date(iso).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function useIntegrationSettings() {
    const settings = ref(null);
    const loading = ref(false);
    const error = ref(null);

    async function load() {
        loading.value = true;
        error.value = null;
        const { ok, data } = await integrationSettingsService.fetch();
        if (ok) {
            settings.value = data?.data ?? null;
        } else {
            error.value = data?.message ?? 'Unable to load the integration settings.';
        }
        loading.value = false;
    }

    onMounted(load);

    const sites = computed(() => settings.value?.sites ?? null);

    // Warning and offline are the connections that need a look.
    const needsAttention = computed(() => (sites.value ? sites.value.warning + sites.value.offline : 0));

    const lastReport = computed(() => formatTimestamp(settings.value?.last_report_at));

    return { settings, sites, needsAttention, lastReport, loading, error, reload: load };
}
