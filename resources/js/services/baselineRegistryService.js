// API BOUNDARY — the only file that changes if/when this registry stops
// being configuration/mock data and starts calling a real endpoint.
// getRegistry() is already async-shaped, matching every other service
// in resources/js/services, so composables/useBaselineRegistry.js will
// not need to change when that swap happens.
import {
    BASELINE_VERSION,
    LEGACY_MODULE_MAPPING,
    baselineModules,
    baselineMenus,
} from '../data/mock/baselineRegistry';

export const baselineRegistryService = {
    async getRegistry() {
        return Promise.resolve({
            baselineVersion: BASELINE_VERSION,
            modules: baselineModules,
            menus: baselineMenus,
            legacyModuleMapping: LEGACY_MODULE_MAPPING,
        });
    },
};
