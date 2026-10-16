// Modules REGISTERED into the Master Baseline from the Control Center
// (GET /api/baseline). The 9 SKS modules stay in data/mock/baselineRegistry.js
// and are locked; a module a site reports that the baseline does not have can
// be registered, and then belongs to the baseline like any other.
//
// Everything that reads "the baseline" (Baseline page, Site Configuration,
// snapshot labels, Module Observatory) goes through allBaselineModules() /
// allBaselineMenus() so a registered module is included everywhere, and
// loadRegisteredBaseline() refreshes it after a register / unregister.
import { reactive } from 'vue';
import { apiJson } from '../lib/http';
import { baselineModules, baselineMenus } from './mock/baselineRegistry';

// Registered modules sort after the 9 core ones.
const REGISTERED_ORDER_START = 100;

export const registeredBaseline = reactive({
    // [{ module_key, label, description, order, enabled, registered, ... }]
    modules: [],
    // [{ menu_key, module_key, label, parent_key, order, enabled }]
    menus: [],
    // Extensions reported by sites that are not in the baseline yet:
    // [{ moduleKey, label, sites: [code], menuCount }]
    candidates: [],
    loaded: false,
});

export async function loadRegisteredBaseline() {
    const { ok, data } = await apiJson('/api/baseline');
    if (!ok) return false;

    const modules = [];
    const menus = [];

    (data?.data ?? []).forEach((entry, index) => {
        modules.push({
            module_key: entry.moduleKey,
            label: entry.label,
            description: entry.description ?? '',
            order: REGISTERED_ORDER_START + index,
            enabled: true,
            businessBaseline: 'verified',
            technicalMapping: 'not_connected',
            registered: true,
            sourceSiteCode: entry.sourceSiteCode,
            registeredBy: entry.registeredBy,
            registeredAt: entry.registeredAt,
        });

        for (const menu of entry.menus ?? []) {
            menus.push({
                menu_key: menu.menu_key,
                module_key: entry.moduleKey,
                label: menu.label,
                parent_key: menu.parent_key ?? null,
                order: menu.order,
                enabled: true,
            });
        }
    });

    registeredBaseline.modules = modules;
    registeredBaseline.menus = menus;
    registeredBaseline.candidates = data?.candidates ?? [];
    registeredBaseline.loaded = true;

    return true;
}

// Call inside a computed()/template and it re-evaluates when registrations change.
export function allBaselineModules() {
    return [...baselineModules, ...registeredBaseline.modules];
}

export function allBaselineMenus() {
    return [...baselineMenus, ...registeredBaseline.menus];
}
