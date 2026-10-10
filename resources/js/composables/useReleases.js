// Talks to the real /api/releases endpoints — real database, not mock
// data. Mirrors composables/useUsers.js's pattern exactly.
import { ref } from 'vue';
import { releaseService } from '../services/releaseService';

export function useReleases() {
    const releases = ref([]);
    const loading = ref(false);
    const error = ref(null);

    async function fetchReleases() {
        loading.value = true;
        error.value = null;
        const { ok, data } = await releaseService.fetchReleases();
        if (ok) {
            releases.value = data?.data ?? [];
        } else {
            error.value = data?.message ?? 'Unable to load releases.';
        }
        loading.value = false;
    }

    async function createRelease(payload) {
        const { ok, data } = await releaseService.createRelease(payload);

        if (ok) {
            releases.value = [data?.data ?? data, ...releases.value];
            return { success: true, release: data?.data ?? data };
        }

        return {
            success: false,
            message: data?.message ?? 'Unable to create release.',
            errors: data?.errors ?? {},
        };
    }

    async function updateRelease(id, payload) {
        const { ok, status, data } = await releaseService.updateRelease(id, payload);

        if (ok) {
            const updated = data?.data ?? data;
            releases.value = releases.value.map((r) => (r.id === id ? updated : r));
            return { success: true, release: updated };
        }

        return {
            success: false,
            message: data?.message ?? 'Unable to update release.',
            errors: data?.errors ?? {},
            notFound: status === 404,
        };
    }

    return {
        releases,
        loading,
        error,
        fetchReleases,
        createRelease,
        updateRelease,
    };
}
