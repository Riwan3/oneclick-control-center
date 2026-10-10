import { apiJson } from '../lib/http';

export const releaseService = {
    async fetchReleases() {
        return apiJson('/api/releases');
    },

    async fetchRelease(id) {
        return apiJson(`/api/releases/${id}`);
    },

    async createRelease(payload) {
        return apiJson('/api/releases', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
    },

    async updateRelease(id, payload) {
        return apiJson(`/api/releases/${id}`, {
            method: 'PATCH',
            body: JSON.stringify(payload),
        });
    },

    async createChangelog(releaseId, payload) {
        return apiJson(`/api/releases/${releaseId}/changelogs`, {
            method: 'POST',
            body: JSON.stringify(payload),
        });
    },

    async updateChangelog(releaseId, changelogId, payload) {
        return apiJson(`/api/releases/${releaseId}/changelogs/${changelogId}`, {
            method: 'PATCH',
            body: JSON.stringify(payload),
        });
    },

    async deleteChangelog(releaseId, changelogId) {
        return apiJson(`/api/releases/${releaseId}/changelogs/${changelogId}`, {
            method: 'DELETE',
        });
    },
};
