import { defineStore } from 'pinia';

export const useSiteStore = defineStore('site', {
    state: () => ({
        // Mock sites — stands in until real site management/API exists.
        // `modules` records which conceptual modules (config/modules.js)
        // are enabled for that site.
        sites: [
            {
                id: 'site-jakarta',
                name: 'Jakarta HQ',
                modules: {
                    'one-manpower': true,
                    'one-fleet': true,
                    'one-dms': false,
                    'one-qcc': false,
                },
            },
            {
                id: 'site-surabaya',
                name: 'Surabaya Plant',
                modules: {
                    'one-manpower': true,
                    'one-fleet': false,
                    'one-dms': true,
                    'one-qcc': false,
                },
            },
        ],
        // null represents "All Sites".
        currentSite: null,
    }),
    actions: {
        selectSite(siteId) {
            this.currentSite = siteId
                ? this.sites.find((site) => site.id === siteId) ?? null
                : null;
        },
    },
});
