// MASTER BASELINE REGISTRY — the governance reference for
// MODULE -> SIDEBAR/MENU -> CHILD MENU -> ORDER -> ENABLED STATE across
// the OneClick ecosystem. This is the "BASELINE" layer that a future
// SITE CONFIGURATION / ROLE CONFIGURATION / RELEASE CONTROL / MONITORING
// pipeline will compare against — none of those later layers are
// implemented here.
//
// SOURCE OF TRUTH (CORRECTED)
// ================================================================
// The official, approved OneClick Business Baseline is "OneClick.docx",
// available in this project's context. It is authoritative for the
// module list AND the sidebar/menu hierarchy below — this is NOT mock
// business data. A prior version of this registry incorrectly treated
// 9 of 10 modules as an unverifiable "mapping_gap" because no Laravel
// config file exists in this repository; that conflated "business
// baseline unknown" with "technical Laravel cross-check unavailable."
// Corrected per instruction: those are two separate axes, tracked here
// as `businessBaseline` (verified for every module below, straight from
// OneClick.docx) and `technicalMapping` (not_connected for every module
// — no Laravel config/one_xxx_menu.php file exists in this repository
// to cross-check against, which remains true and is tracked honestly,
// but is no longer mislabeled as a baseline gap).
//
// One-Power's manifest is NOT re-authored here — it is imported from
// data/onePowerBaseline.js's ONE_POWER_BASELINE_MENUS (itself
// corrected against OneClick.docx in this same pass) and reshaped to
// this registry's menu_key/parent_key naming. There is exactly one
// authoritative frontend One-Power baseline manifest; Module
// Observatory and this registry both read it.
import { ONE_POWER_BASELINE_MENUS } from '../onePowerBaseline';

export const BASELINE_VERSION = 'v2.5.0';

// ================================================================
// LEGACY CATALOGUE MAPPING — config/modules.js vs. this baseline.
// ================================================================
// config/modules.js (one-manpower, one-fleet, one-dms, one-qcc) backs
// the EXISTING, live Control Center user site/module ACCESS ASSIGNMENT
// feature (User Management picker, effective module access, the
// user_module_access pivot). It is NOT overwritten, renamed, or deleted
// here. Its 4 keys do not match this baseline's 8 official module keys
// except for 'one-dms', which coincides by name only — not confirmed to
// be the same underlying module. This is reported, not silently
// reconciled.
export const LEGACY_MODULE_MAPPING = [
    {
        legacyKey: 'one-manpower',
        legacyLabel: 'One-Manpower',
        suggestedBaselineKey: 'one-power',
        confidence: 'unconfirmed',
        note: 'Conceptually similar (workforce/roster/manpower) but not confirmed by the business as the same module — needs explicit confirmation before any merge.',
    },
    {
        legacyKey: 'one-fleet',
        legacyLabel: 'One-Fleet',
        suggestedBaselineKey: null,
        confidence: 'unresolved',
        note: 'No corresponding module found in the 8-module official baseline.',
    },
    {
        legacyKey: 'one-dms',
        legacyLabel: 'One-DMS',
        suggestedBaselineKey: 'one-dms',
        confidence: 'likely',
        note: "Key matches exactly, but config/modules.js carries no further detail (menus, description) to confirm it is the same module as the baseline's One-DMS.",
    },
    {
        legacyKey: 'one-qcc',
        legacyLabel: 'One-QCC',
        suggestedBaselineKey: null,
        confidence: 'unresolved',
        note: 'No corresponding module found in the 8-module official baseline.',
    },
];

function reshapeOnePowerMenus() {
    return ONE_POWER_BASELINE_MENUS.map((menu) => ({
        menu_key: menu.id,
        module_key: 'one-power',
        label: menu.label,
        parent_key: menu.parentId,
        order: menu.order,
        enabled: menu.status === 'active',
    }));
}

// ================================================================
// MODULE REGISTRY — the 8 official OneClick.docx baseline modules.
// ================================================================
// Order matches the numbered list in OneClick.docx exactly. One-Admin
// and One-Coal are deliberately excluded — they are not present in the
// official baseline and were not invented.
export const baselineModules = [
    { module_key: 'one-power', label: 'One-Power', description: 'Workforce, roster, and operator performance management.', order: 1, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-academy', label: 'One-Academy', description: null, order: 2, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-core-value', label: 'One-Core&Value', description: null, order: 3, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-dms', label: 'One-DMS', description: null, order: 4, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-gmp', label: 'One-GMP', description: null, order: 5, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-reporting', label: 'One-Reporting', description: null, order: 6, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-setting', label: 'One-Setting', description: null, order: 7, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-procedur', label: 'One-Procedur', description: null, order: 8, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
];

// ================================================================
// MENU REGISTRY — full sidebar hierarchy for every module, exactly as
// given in OneClick.docx. Every module below has a complete, verified
// business baseline; none render "no manifest available."
// ================================================================
export const baselineMenus = [
    // ---- One-Power (26 items) — reused from onePowerBaseline.js ----
    ...reshapeOnePowerMenus(),

    // ---- One-Academy (3 items, no children) ----
    { menu_key: 'monitoring-grade-jabatan', module_key: 'one-academy', label: 'Monitoring Grade & Jabatan', parent_key: null, order: 1, enabled: true },
    { menu_key: 'evaluasi-kompetensi', module_key: 'one-academy', label: 'Evaluasi Kompetensi', parent_key: null, order: 2, enabled: true },
    { menu_key: 'best-employe', module_key: 'one-academy', label: 'Best Employe', parent_key: null, order: 3, enabled: true },

    // ---- One-Core&Value (4 items, no children) ----
    { menu_key: 'user-oneclick', module_key: 'one-core-value', label: 'User Oneclick', parent_key: null, order: 1, enabled: true },
    { menu_key: 'unit-produksi', module_key: 'one-core-value', label: 'Unit Produksi', parent_key: null, order: 2, enabled: true },
    { menu_key: 'nomor-penting', module_key: 'one-core-value', label: 'Nomor Penting', parent_key: null, order: 3, enabled: true },
    { menu_key: 'request-reset-password', module_key: 'one-core-value', label: 'Request Reset Password', parent_key: null, order: 4, enabled: true },

    // ---- One-DMS (3 top-level; Data ICC has 11 children = 14 items) ----
    { menu_key: 'dashboard-dms', module_key: 'one-dms', label: 'Dashboard DMS', parent_key: null, order: 1, enabled: true },
    { menu_key: 'formulir-dms', module_key: 'one-dms', label: 'Formulir DMS', parent_key: null, order: 2, enabled: true },
    { menu_key: 'data-icc', module_key: 'one-dms', label: 'Data ICC', parent_key: null, order: 3, enabled: true },
    { menu_key: 'icc-deviasi-fatigue', module_key: 'one-dms', label: 'Deviasi Fatigue', parent_key: 'data-icc', order: 1, enabled: true },
    { menu_key: 'icc-smoking', module_key: 'one-dms', label: 'Smoking', parent_key: 'data-icc', order: 2, enabled: true },
    { menu_key: 'icc-driver-distraction', module_key: 'one-dms', label: 'Driver Distraction', parent_key: 'data-icc', order: 3, enabled: true },
    { menu_key: 'icc-phone-detection', module_key: 'one-dms', label: 'Phone Detection', parent_key: 'data-icc', order: 4, enabled: true },
    { menu_key: 'icc-yawning-detection', module_key: 'one-dms', label: 'Yawning Detection', parent_key: 'data-icc', order: 5, enabled: true },
    { menu_key: 'icc-eyeblink', module_key: 'one-dms', label: 'Eyeblink', parent_key: 'data-icc', order: 6, enabled: true },
    { menu_key: 'icc-camera-covering', module_key: 'one-dms', label: 'Camera covering', parent_key: 'data-icc', order: 7, enabled: true },
    { menu_key: 'icc-forward-collision', module_key: 'one-dms', label: 'Forward Collision', parent_key: 'data-icc', order: 8, enabled: true },
    { menu_key: 'icc-video-loss', module_key: 'one-dms', label: 'Video Loss', parent_key: 'data-icc', order: 9, enabled: true },
    { menu_key: 'icc-no-driver', module_key: 'one-dms', label: 'No Driver', parent_key: 'data-icc', order: 10, enabled: true },
    { menu_key: 'icc-abnormal-over-speed', module_key: 'one-dms', label: 'Abnormal/Over Speed', parent_key: 'data-icc', order: 11, enabled: true },

    // ---- One-GMP (6 top-level; Master GMP has 5 children = 11 items) ----
    { menu_key: 'dashboard-gmp', module_key: 'one-gmp', label: 'Dashboard GMP', parent_key: null, order: 1, enabled: true },
    { menu_key: 'trend-gmp', module_key: 'one-gmp', label: 'Trend GMP', parent_key: null, order: 2, enabled: true },
    { menu_key: 'gmp-area', module_key: 'one-gmp', label: 'GMP Area', parent_key: null, order: 3, enabled: true },
    { menu_key: 'penilaian', module_key: 'one-gmp', label: 'Penilaian', parent_key: null, order: 4, enabled: true },
    { menu_key: 'approval', module_key: 'one-gmp', label: 'Approval', parent_key: null, order: 5, enabled: true },
    { menu_key: 'master-gmp', module_key: 'one-gmp', label: 'Master GMP', parent_key: null, order: 6, enabled: true },
    { menu_key: 'master-gmp-mhr', module_key: 'one-gmp', label: 'MHR', parent_key: 'master-gmp', order: 1, enabled: true },
    { menu_key: 'master-gmp-mfob', module_key: 'one-gmp', label: 'MFOB', parent_key: 'master-gmp', order: 2, enabled: true },
    { menu_key: 'master-gmp-mfoc', module_key: 'one-gmp', label: 'MFOC', parent_key: 'master-gmp', order: 3, enabled: true },
    { menu_key: 'master-gmp-disposal', module_key: 'one-gmp', label: 'DISPOSAL', parent_key: 'master-gmp', order: 4, enabled: true },
    { menu_key: 'master-gmp-dewatering', module_key: 'one-gmp', label: 'DEWATERING', parent_key: 'master-gmp', order: 5, enabled: true },

    // ---- One-Reporting (5 items, no children) ----
    { menu_key: 'dashboard-compliance', module_key: 'one-reporting', label: 'Dashboard Compliance', parent_key: null, order: 1, enabled: true },
    { menu_key: 'daily-compliance', module_key: 'one-reporting', label: 'Daily Compliance', parent_key: null, order: 2, enabled: true },
    { menu_key: 'laporan-compliance', module_key: 'one-reporting', label: 'Laporan Compliance', parent_key: null, order: 3, enabled: true },
    { menu_key: 'master-data-compliance', module_key: 'one-reporting', label: 'Master Data Compliance', parent_key: null, order: 4, enabled: true },
    { menu_key: 'ojk', module_key: 'one-reporting', label: 'OJK', parent_key: null, order: 5, enabled: true },

    // ---- One-Setting (3 top-level; Settingan has 3 children = 6 items) ----
    { menu_key: 'settingan', module_key: 'one-setting', label: 'Settingan', parent_key: null, order: 1, enabled: true },
    { menu_key: 'settingan-input-manpower', module_key: 'one-setting', label: 'Input Setting Manpower', parent_key: 'settingan', order: 1, enabled: true },
    { menu_key: 'settingan-input-fleet', module_key: 'one-setting', label: 'Input Setting Fleet', parent_key: 'settingan', order: 2, enabled: true },
    { menu_key: 'settingan-data-alokasi-manpower', module_key: 'one-setting', label: 'Data Alokasi Manpower', parent_key: 'settingan', order: 3, enabled: true },
    { menu_key: 'monitoring-settingan-manpower', module_key: 'one-setting', label: 'Monitoring Settingan Manpower', parent_key: null, order: 2, enabled: true },
    { menu_key: 'monitoring-setting-fleet', module_key: 'one-setting', label: 'Monitoring Setting Fleet', parent_key: null, order: 3, enabled: true },

    // ---- One-Procedur (3 items, no children) ----
    { menu_key: 'prosedur', module_key: 'one-procedur', label: 'Prosedur', parent_key: null, order: 1, enabled: true },
    { menu_key: 'activity', module_key: 'one-procedur', label: 'Activity', parent_key: null, order: 2, enabled: true },
    { menu_key: 'evaluasi-prosedur', module_key: 'one-procedur', label: 'Evaluasi Prosedur', parent_key: null, order: 3, enabled: true },
];
