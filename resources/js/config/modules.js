// Central OneClick module catalogue.
//
// Derives the module catalogue (the 9 SKS modules) directly from the Master
// Baseline Registry (data/mock/baselineRegistry.js). No duplicate module
// definitions are maintained here.
import { baselineModules } from '../data/mock/baselineRegistry';

export const modules = baselineModules.map((mod) => ({
    key: mod.module_key,
    label: mod.label,
    description: mod.description || `${mod.label} module.`,
}));
