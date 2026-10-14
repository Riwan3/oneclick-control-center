import { defineStore } from 'pinia';

// GLOBAL SITE CONTEXT — which site the Control Center chrome is
// currently scoped to, selected from AppTopbar.vue's site selector.
//
// Pages that list sites filter through useEffectiveAccess().canAccessSite(),
// which narrows to the site selected here.
//
// Session-only, in-memory Pinia state (no localStorage) — the same
// pattern stores/ui.js's sidebarCollapsed already uses; state persists
// across in-app navigation (this is a single-page app) but resets on a
// hard reload, since no persistence layer exists anywhere in this app.
//
// Deliberately independent from stores/site.js (a separate, currently
// unused store already consumed by useNavigation.js for its own
// site/module nav-gating logic, seeded with placeholder site data) —
// this store is not touched, to avoid any risk of changing that
// existing, unrelated behavior.
export const useSiteContextStore = defineStore('siteContext', {
    state: () => ({
        // Contract: { key: 'all', name: 'All Sites' } or
        // { key: <site.code>, name: <site.name> } — site.code/site.name
        // are the existing identity fields already used as the
        // human-facing identity everywhere else in this app.
        selected: { key: 'all', name: 'All Sites' },
    }),
    actions: {
        selectSite(site) {
            this.selected = site ?? { key: 'all', name: 'All Sites' };
        },
    },
});
