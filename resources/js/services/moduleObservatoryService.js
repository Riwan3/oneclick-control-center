// API BOUNDARY — the only file that changes when this stops being mock
// data and starts calling a real endpoint (e.g.
// GET /api/v1/module-observatory/{moduleKey}). getOverview() is already
// async-shaped, matching the `await apiJson(...)` pattern every other
// service in resources/js/services uses, so
// composables/useModuleObservatory.js will not need to change when that
// swap happens — only the implementation below.
import { moduleObservatoryData } from '../data/mock/moduleObservatory';

export const moduleObservatoryService = {
    // Returns the raw (unjoined — site identity not yet resolved)
    // record for one observed application/module, or null if it isn't
    // monitored yet.
    async getOverview(moduleKey) {
        return Promise.resolve(moduleObservatoryData[moduleKey] ?? null);
    },
};
