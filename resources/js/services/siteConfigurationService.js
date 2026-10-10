// Real backend calls (NOT mock data) — this is the one part of the
// Site Configuration feature that must persist across a page reload
// and be audit-logged server-side, so it goes through the same
// Page -> Composable -> Service -> lib/http.js -> Laravel API layering
// every other real feature (Users, Audit, Notifications) already uses.
// The Master Baseline Registry itself remains frontend-only mock data;
// only the per-site override deltas are persisted here.
import { apiJson } from '../lib/http';

export const siteConfigurationService = {
    async fetchAll() {
        return apiJson('/api/site-configurations');
    },

    async toggleModule(siteId, moduleKey, enabled) {
        return apiJson(`/api/site-configurations/${siteId}/modules`, {
            method: 'PATCH',
            body: JSON.stringify({ module_key: moduleKey, enabled }),
        });
    },

    async toggleMenu(siteId, moduleKey, menuKey, enabled) {
        return apiJson(`/api/site-configurations/${siteId}/menus`, {
            method: 'PATCH',
            body: JSON.stringify({ module_key: moduleKey, menu_key: menuKey, enabled }),
        });
    },
};
