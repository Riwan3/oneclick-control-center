// Site API connections: the real state of every site's link to this Control
// Center (token, mode, last report, whether the site applies our
// configuration) plus the two actions that make a connection work — issue
// a token and test the connection. Nothing here is simulated: everything
// comes from /api/site-connections and the site-management endpoints.
import { ref, computed } from 'vue';
import { sites as allSites, loadSites } from '../data/siteCatalogue';
import { siteManagementService } from '../services/siteManagementService';
import { useEffectiveAccess } from './useEffectiveAccess';

const MODE_META = {
    push: { variant: 'info', label: 'Push', hint: 'The site reports to this Control Center (cc:push).' },
    pull: { variant: 'info', label: 'Pull', hint: 'This Control Center calls the site at its Site URL.' },
    none: { variant: 'neutral', label: 'Not connected', hint: 'No token issued yet.' },
};

const STATUS_VARIANT = { online: 'success', warning: 'warning', offline: 'danger', unknown: 'neutral' };
const STATUS_LABEL = { online: 'Online', warning: 'Warning', offline: 'Offline', unknown: 'Not Connected' };

function errorMessage(result) {
    return Object.values(result.data?.errors ?? {}).flat()[0] ?? result.data?.message ?? 'Request failed.';
}

export function useSiteApi() {
    const { canAccessSite } = useEffectiveAccess();

    const connections = computed(() => allSites.filter((site) => canAccessSite(site.code)));

    const busyId = ref(null);
    const actionError = ref(null);
    const notice = ref(null);

    // Shown exactly once, right after a token is issued.
    const issued = ref(null);
    const copied = ref(false);

    const envSnippet = computed(() =>
        issued.value
            ? Object.entries(issued.value.env)
                  .map(([key, value]) => `${key}=${value}`)
                  .join('\n')
            : ''
    );

    const summary = computed(() => ({
        total: connections.value.length,
        connected: connections.value.filter((site) => site.connected).length,
        online: connections.value.filter((site) => site.connected && site.status === 'online').length,
    }));

    async function issueToken(site) {
        if (site.mode !== 'none' && !window.confirm(`Issue a new token for ${site.code}? The current token stops working immediately.`)) {
            return;
        }

        actionError.value = null;
        notice.value = null;
        busyId.value = site.dbId;
        const result = await siteManagementService.issueToken(site.dbId);
        busyId.value = null;

        if (!result.ok) {
            actionError.value = errorMessage(result);
            return;
        }

        issued.value = { code: site.code, env: result.data.data.env };
        copied.value = false;
        await loadSites();
    }

    // Pull mode only: this Control Center calls the site right now.
    async function testConnection(site) {
        actionError.value = null;
        notice.value = null;
        busyId.value = site.dbId;
        const result = await siteManagementService.checkNow(site.dbId);
        busyId.value = null;

        if (!result.ok) {
            actionError.value = errorMessage(result);
            return;
        }

        await loadSites();
        const checked = result.data?.data;
        notice.value =
            checked?.status === 'offline'
                ? `${site.code}: connection failed${checked.last_error ? ` — ${checked.last_error}` : ''}.`
                : `${site.code}: connected (${STATUS_LABEL[checked?.status] ?? checked?.status}).`;
    }

    async function copySnippet() {
        try {
            await navigator.clipboard.writeText(envSnippet.value);
            copied.value = true;
        } catch {
            copied.value = false;
        }
    }

    return {
        connections,
        summary,
        busyId,
        actionError,
        notice,
        issued,
        copied,
        envSnippet,
        issueToken,
        testConnection,
        copySnippet,
        modeMeta: MODE_META,
        statusVariant: STATUS_VARIANT,
        statusLabel: STATUS_LABEL,
    };
}
