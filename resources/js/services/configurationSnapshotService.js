// Real backend calls — Configuration Snapshots are immutable historical
// records persisted server-side (see App\Services\ConfigurationSnapshotService),
// not mock data. Page -> Composable -> Service -> lib/http.js -> Laravel
// API, matching every other real feature in this app.
import { apiJson } from '../lib/http';

export const configurationSnapshotService = {
    async fetchAll(siteId) {
        return apiJson(`/api/configuration-snapshots/${siteId}`);
    },

    async fetchOne(siteId, snapshotId) {
        return apiJson(`/api/configuration-snapshots/${siteId}/${snapshotId}`);
    },

    // Creates a NEW snapshot — there is no "update" call; a snapshot is
    // immutable once created (no PATCH/PUT endpoint exists at all).
    async create(siteId) {
        return apiJson(`/api/configuration-snapshots/${siteId}`, { method: 'POST' });
    },
};
