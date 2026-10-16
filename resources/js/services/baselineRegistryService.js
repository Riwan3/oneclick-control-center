// API BOUNDARY for the Master Baseline Registry. The 9 SKS modules and their
// menus are the locked part (data/mock/baselineRegistry.js); modules registered
// from the Control Center come from the backend (data/baselineRegistered.js).
// Register / unregister are the only writes, and they only touch the registered
// modules — the locked core cannot be changed from here.
import { apiJson } from '../lib/http';
import { BASELINE_VERSION, LEGACY_MODULE_MAPPING } from '../data/mock/baselineRegistry';
import { allBaselineModules, allBaselineMenus, loadRegisteredBaseline } from '../data/baselineRegistered';

export const baselineRegistryService = {
    async getRegistry() {
        await loadRegisteredBaseline();

        return {
            baselineVersion: BASELINE_VERSION,
            modules: allBaselineModules(),
            menus: allBaselineMenus(),
            legacyModuleMapping: LEGACY_MODULE_MAPPING,
        };
    },

    async registerModule(moduleKey, { label, description } = {}) {
        const result = await apiJson('/api/baseline/modules', {
            method: 'POST',
            body: JSON.stringify({ module_key: moduleKey, label: label || null, description: description || null }),
        });
        if (result.ok) await loadRegisteredBaseline();
        return result;
    },

    async unregisterModule(moduleKey) {
        const result = await apiJson(`/api/baseline/modules/${encodeURIComponent(moduleKey)}`, { method: 'DELETE' });
        if (result.ok) await loadRegisteredBaseline();
        return result;
    },
};
