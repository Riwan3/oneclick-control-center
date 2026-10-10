// Central OneClick module catalogue.
//
// Derives the official 8-module catalogue directly from the Master Baseline
// Registry (data/mock/baselineRegistry.js). No duplicate module definitions are
// maintained here.
import { baselineModules } from '../data/mock/baselineRegistry';

export const modules = baselineModules.map((mod) => ({
    key: mod.module_key,
    label: mod.label,
    description: mod.description || `${mod.label} module.`,
}));
