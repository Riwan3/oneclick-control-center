// One release's detail + changelog management — real backend, mirrors
// composables/useUsers.js's pattern.
import { ref } from 'vue';
import { releaseService } from '../services/releaseService';

export function useReleaseDetail() {
    const release = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const notFound = ref(false);

    async function fetchRelease(id) {
        loading.value = true;
        error.value = null;
        notFound.value = false;
        const { ok, status, data } = await releaseService.fetchRelease(id);
        if (ok) {
            release.value = data?.data ?? data;
        } else {
            error.value = data?.message ?? 'Unable to load release.';
            notFound.value = status === 404;
        }
        loading.value = false;
    }

    async function updateRelease(id, payload) {
        const { ok, data } = await releaseService.updateRelease(id, payload);
        if (ok) {
            release.value = data?.data ?? data;
            return { success: true };
        }
        return { success: false, message: data?.message ?? 'Unable to update release.', errors: data?.errors ?? {} };
    }

    async function createChangelog(releaseId, payload) {
        const { ok, data } = await releaseService.createChangelog(releaseId, payload);
        if (ok) {
            await fetchRelease(releaseId);
            return { success: true };
        }
        return { success: false, message: data?.message ?? 'Unable to create changelog entry.', errors: data?.errors ?? {} };
    }

    async function updateChangelog(releaseId, changelogId, payload) {
        const { ok, data } = await releaseService.updateChangelog(releaseId, changelogId, payload);
        if (ok) {
            await fetchRelease(releaseId);
            return { success: true };
        }
        return { success: false, message: data?.message ?? 'Unable to update changelog entry.', errors: data?.errors ?? {} };
    }

    async function deleteChangelog(releaseId, changelogId) {
        const { ok, data } = await releaseService.deleteChangelog(releaseId, changelogId);
        if (ok) {
            await fetchRelease(releaseId);
            return { success: true };
        }
        return { success: false, message: data?.message ?? 'Unable to delete changelog entry.' };
    }

    return {
        release,
        loading,
        error,
        notFound,
        fetchRelease,
        updateRelease,
        createChangelog,
        updateChangelog,
        deleteChangelog,
    };
}
