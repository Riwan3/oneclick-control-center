// MASTER BASELINE REGISTRY — the governance reference for
// MODULE -> SIDEBAR/MENU -> CHILD MENU -> ORDER -> ENABLED STATE across
// the OneClick ecosystem. Site Configuration, Configuration Snapshots and the
// Module Observatory all compare a site against this baseline.
//
// SOURCE OF TRUTH
// ================================================================
// The OneClick SKS site is the baseline for every other OneClick site. The
// modules and menus below are taken from its repo: config/modules.php (module
// list) and config/one_*_menu.php (sidebar hierarchy, order, labels). The
// menu_key of each item is the site's own menu_key — so the ids the SKS site
// reports through /api/cc/menus match the baseline exactly, and the Module
// Observatory diff is clean for SKS itself.
//
// To follow a change on SKS: update this file, onePowerBaseline.js and the
// key mirror app/Support/BaselineKeys.php together (BaselineKeys carries the
// same keys, no content).
//
// `technicalMapping` stays 'not_connected' on every module: it records that
// no automated cross-check of this file against the site repo exists yet.
//
// One-Power's manifest lives in data/onePowerBaseline.js and is reshaped here
// to this registry's menu_key/parent_key naming. There is exactly one
// authoritative frontend One-Power baseline manifest; Module Observatory and
// this registry both read it.
import { ONE_POWER_BASELINE_MENUS } from '../onePowerBaseline';

export const BASELINE_VERSION = 'v2.5.0';

// ================================================================
// LEGACY CATALOGUE MAPPING — config/modules.js vs. this baseline.
// ================================================================
// config/modules.js (one-manpower, one-fleet, one-dms, one-qcc) backs
// the EXISTING, live Control Center user site/module ACCESS ASSIGNMENT
// feature (User Management picker, effective module access, the
// user_module_access pivot). It is NOT overwritten, renamed, or deleted
// here. Its 4 keys do not match this baseline's 9 module keys
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
        note: 'No corresponding module found in the SKS baseline.',
    },
    {
        legacyKey: 'one-dms',
        legacyLabel: 'One-DMS',
        suggestedBaselineKey: 'one-dms',
        confidence: 'likely',
        note: "Key matches exactly, but config/modules.js carries no further detail (menus, description) to confirm it is the same module as the baseline's One-ICC.",
    },
    {
        legacyKey: 'one-qcc',
        legacyLabel: 'One-QCC',
        suggestedBaselineKey: null,
        confidence: 'unresolved',
        note: 'No corresponding module found in the SKS baseline.',
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
// MODULE REGISTRY — the 9 modules of the OneClick SKS site, in the order
// its config/modules.php lists them. Keys are the baseline's own; the two
// that differ from the site's (one-core-value, one-procedur) are translated
// by SiteConfigPayload (to the site) and SITE_TO_BASELINE_MODULE in
// data/siteCatalogue.js (from the site). One-Admin is not here: the SKS
// site only has a placeholder menu for it and does not list it as a module.
// ================================================================
export const baselineModules = [
    { module_key: 'one-power', label: 'One-Power', description: 'Kelola manpower, roster bulanan/harian, MPP planning, personal data operator (HM, attendance, MPR, Kimper), KPI, pembinaan, dan coaching clinic.', order: 1, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-academy', label: 'One-Academy', description: 'Manajemen database training karyawan, pengajuan pelatihan, monitoring grade dan jenjang jabatan.', order: 2, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-core-value', label: 'One-Core&Value', description: 'Pusat data master aplikasi, konfigurasi user, data unit, SPDK, serta daftar kontak/nomor penting.', order: 3, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-dms', label: 'One-ICC', description: 'Dashboard fatigue operator (ICC), Driving Monitoring System (DMS), Berita Acara, ADAS Driver Distraction, Phone & Yawning detection, dsb.', order: 4, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-gmp', label: 'One-GMP', description: 'Checklist dan penilaian Good Mining Practice (GMP) area, approval GMP, dan konfigurasi master data GMP.', order: 5, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-reporting', label: 'One-Reporting', description: 'Laporan kepatuhan operasional (Operational Compliance Report), daily assignment, daily compliance, dan rekapitulasi data compliance.', order: 6, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-setting', label: 'One-Setting', description: 'Pengaturan setting manpower, setting fleet, setting manpower V2, setting fleet V2, monitoring setting manpower, kesiapan operator, & daily ATR.', order: 7, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-procedur', label: 'One-Procedur', description: 'Kelola prosedur operasional, activity, dan evaluasi prosedur.', order: 8, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
    { module_key: 'one-roster', label: 'One-Roster', description: 'Roster V2: dashboard monitoring, roster per departemen, analisis harian, MONCU (monitoring cuti), dan share roster.', order: 9, enabled: true, businessBaseline: 'verified', technicalMapping: 'not_connected' },
];

// ================================================================
// MENU REGISTRY — the full sidebar hierarchy of every module, generated
// from the SKS site's config/one_*_menu.php. `menu_key` is the site's own
// menu_key, so it is unique per MODULE, not globally (several modules have
// a 'dashboard'). Always look a menu up by (module_key, menu_key).
// ================================================================
export const baselineMenus = [
    // ---- One-Power (32 items) — reused from onePowerBaseline.js ----
    ...reshapeOnePowerMenus(),

    // ---- One-Academy (4 items, 4 top-level) ----
    { menu_key: 'training', module_key: 'one-academy', label: 'Training', parent_key: null, order: 1, enabled: true },
    { menu_key: 'monitoring-grade-jabatan', module_key: 'one-academy', label: 'Monitoring Grade & Jabatan', parent_key: null, order: 2, enabled: true },
    { menu_key: 'evaluasi-kompetensi', module_key: 'one-academy', label: 'Evaluasi Kompetensi', parent_key: null, order: 3, enabled: true },
    { menu_key: 'best-employee', module_key: 'one-academy', label: 'Best Employee', parent_key: null, order: 4, enabled: true },

    // ---- One-Core&Value (6 items, 6 top-level) ----
    { menu_key: 'user', module_key: 'one-core-value', label: 'User Oneclick', parent_key: null, order: 1, enabled: true },
    { menu_key: 'role-akses', module_key: 'one-core-value', label: 'Role & Akses', parent_key: null, order: 2, enabled: true },
    { menu_key: 'unit', module_key: 'one-core-value', label: 'Unit Produksi', parent_key: null, order: 3, enabled: true },
    { menu_key: 'master-spdk', module_key: 'one-core-value', label: 'Master SPDK', parent_key: null, order: 4, enabled: true },
    { menu_key: 'nomor-penting', module_key: 'one-core-value', label: 'Nomor Penting', parent_key: null, order: 5, enabled: true },
    { menu_key: 'request-reset-password', module_key: 'one-core-value', label: 'Request Reset Password', parent_key: null, order: 6, enabled: true },

    // ---- One-ICC (15 items, 3 top-level) ----
    { menu_key: 'dashboard', module_key: 'one-dms', label: 'Dashboard DMS', parent_key: null, order: 1, enabled: true },
    { menu_key: 'formulir', module_key: 'one-dms', label: 'Formulir DMS', parent_key: null, order: 2, enabled: true },
    { menu_key: 'data-icc', module_key: 'one-dms', label: 'Data ICC', parent_key: null, order: 3, enabled: true },
    { menu_key: 'deviasi-fatigue', module_key: 'one-dms', label: 'Deviasi Fatigue', parent_key: 'data-icc', order: 1, enabled: true },
    { menu_key: 'izin-fatigue', module_key: 'one-dms', label: 'Izin Fatigue', parent_key: 'data-icc', order: 2, enabled: true },
    { menu_key: 'smoking-deviasi', module_key: 'one-dms', label: 'Smoking Deviasi', parent_key: 'data-icc', order: 3, enabled: true },
    { menu_key: 'driver-distraction', module_key: 'one-dms', label: 'Driver Distraction', parent_key: 'data-icc', order: 4, enabled: true },
    { menu_key: 'phone-detection', module_key: 'one-dms', label: 'Phone Detection', parent_key: 'data-icc', order: 5, enabled: true },
    { menu_key: 'yawning-detection', module_key: 'one-dms', label: 'Yawning Detection', parent_key: 'data-icc', order: 6, enabled: true },
    { menu_key: 'eye-blink', module_key: 'one-dms', label: 'Eye Blink', parent_key: 'data-icc', order: 7, enabled: true },
    { menu_key: 'camera-covering', module_key: 'one-dms', label: 'Camera Covering', parent_key: 'data-icc', order: 8, enabled: true },
    { menu_key: 'forward-collision', module_key: 'one-dms', label: 'Forward Collision', parent_key: 'data-icc', order: 9, enabled: true },
    { menu_key: 'video-loss-alarm', module_key: 'one-dms', label: 'Video Loss Alarm', parent_key: 'data-icc', order: 10, enabled: true },
    { menu_key: 'no-driver', module_key: 'one-dms', label: 'No Driver', parent_key: 'data-icc', order: 11, enabled: true },
    { menu_key: 'abnormal-speed', module_key: 'one-dms', label: 'Abnormal Speed', parent_key: 'data-icc', order: 12, enabled: true },

    // ---- One-GMP (13 items, 8 top-level) ----
    { menu_key: 'dashboard-gmp', module_key: 'one-gmp', label: 'Dashboard GMP', parent_key: null, order: 1, enabled: true },
    { menu_key: 'monitoring-gmp', module_key: 'one-gmp', label: 'Monitoring GMP', parent_key: null, order: 2, enabled: true },
    { menu_key: 'gmp-area', module_key: 'one-gmp', label: 'GMP Area', parent_key: null, order: 3, enabled: true },
    { menu_key: 'trend-gmp', module_key: 'one-gmp', label: 'Trend GMP', parent_key: null, order: 4, enabled: true },
    { menu_key: 'temuan-gmp', module_key: 'one-gmp', label: 'Temuan & Tindak Lanjut', parent_key: null, order: 5, enabled: true },
    { menu_key: 'penilaian-gmp', module_key: 'one-gmp', label: 'Penilaian', parent_key: null, order: 6, enabled: true },
    { menu_key: 'penilaian-mhr', module_key: 'one-gmp', label: 'Form MHR', parent_key: 'penilaian-gmp', order: 1, enabled: true },
    { menu_key: 'penilaian-mfob', module_key: 'one-gmp', label: 'Form MFOB', parent_key: 'penilaian-gmp', order: 2, enabled: true },
    { menu_key: 'penilaian-mfco', module_key: 'one-gmp', label: 'Form MFCO', parent_key: 'penilaian-gmp', order: 3, enabled: true },
    { menu_key: 'penilaian-disposal', module_key: 'one-gmp', label: 'Form DISPOSAL', parent_key: 'penilaian-gmp', order: 4, enabled: true },
    { menu_key: 'penilaian-dewatering', module_key: 'one-gmp', label: 'Form DEWATERING', parent_key: 'penilaian-gmp', order: 5, enabled: true },
    { menu_key: 'approval-gmp', module_key: 'one-gmp', label: 'Approval GMP', parent_key: null, order: 7, enabled: true },
    { menu_key: 'master-gmp', module_key: 'one-gmp', label: 'Master GMP', parent_key: null, order: 8, enabled: true },

    // ---- One-Reporting (8 items, 4 top-level) ----
    { menu_key: 'dashboard-compliance', module_key: 'one-reporting', label: 'Dashboard Compliance', parent_key: null, order: 1, enabled: true },
    { menu_key: 'compliance', module_key: 'one-reporting', label: 'Compliance', parent_key: null, order: 2, enabled: true },
    { menu_key: 'daily-compliance', module_key: 'one-reporting', label: 'Daily Compliance', parent_key: 'compliance', order: 1, enabled: true },
    { menu_key: 'laporan-compliance', module_key: 'one-reporting', label: 'Laporan Compliance', parent_key: 'compliance', order: 2, enabled: true },
    { menu_key: 'setting-gl', module_key: 'one-reporting', label: 'Setting GL', parent_key: 'compliance', order: 3, enabled: true },
    { menu_key: 'master-compliance', module_key: 'one-reporting', label: 'Master Data Compliance', parent_key: 'compliance', order: 4, enabled: true },
    { menu_key: 'perbaikan-pasca-insiden', module_key: 'one-reporting', label: 'Perbaikan Pasca-Insiden', parent_key: null, order: 3, enabled: true },
    { menu_key: 'ojk', module_key: 'one-reporting', label: 'OJK (Observasi Jam Kritis)', parent_key: null, order: 4, enabled: true },

    // ---- One-Setting (13 items, 9 top-level) ----
    { menu_key: 'dashboard', module_key: 'one-setting', label: 'Dashboard Setting', parent_key: null, order: 1, enabled: true },
    { menu_key: 'setting-board', module_key: 'one-setting', label: 'Setting Board', parent_key: null, order: 2, enabled: true },
    { menu_key: 'settingan-v2', module_key: 'one-setting', label: 'Settingan Manpower', parent_key: null, order: 3, enabled: true },
    { menu_key: 'settingan-v2-manpower', module_key: 'one-setting', label: 'Setting Manpower', parent_key: 'settingan-v2', order: 1, enabled: true },
    { menu_key: 'settingan-v2-monitoring-mp', module_key: 'one-setting', label: 'Monitoring Settingan Manpower', parent_key: 'settingan-v2', order: 2, enabled: true },
    { menu_key: 'setting-fleet', module_key: 'one-setting', label: 'Setting Fleet', parent_key: null, order: 4, enabled: true },
    { menu_key: 'setting-fleet-master', module_key: 'one-setting', label: 'Master Unit', parent_key: 'setting-fleet', order: 1, enabled: true },
    { menu_key: 'setting-fleet-harian', module_key: 'one-setting', label: 'Setting Fleet', parent_key: 'setting-fleet', order: 2, enabled: true },
    { menu_key: 'settingan-v2-monitoring', module_key: 'one-setting', label: 'Monitoring Settingan', parent_key: null, order: 5, enabled: true },
    { menu_key: 'setting-gl', module_key: 'one-setting', label: 'Settingan GL', parent_key: null, order: 6, enabled: true },
    { menu_key: 'setting-unit-support', module_key: 'one-setting', label: 'Lokasi Unit Support', parent_key: null, order: 7, enabled: true },
    { menu_key: 'setting-dozer-pc', module_key: 'one-setting', label: 'Posisi Dozer & PC', parent_key: null, order: 8, enabled: true },
    { menu_key: 'setting-pw-cp', module_key: 'one-setting', label: 'Password CP', parent_key: null, order: 9, enabled: true },

    // ---- One-Procedur (4 items, 4 top-level) ----
    { menu_key: 'dashboard', module_key: 'one-procedur', label: 'Dashboard Procedure', parent_key: null, order: 1, enabled: true },
    { menu_key: 'prosedur', module_key: 'one-procedur', label: 'Prosedur', parent_key: null, order: 2, enabled: true },
    { menu_key: 'activity', module_key: 'one-procedur', label: 'Activity', parent_key: null, order: 3, enabled: true },
    { menu_key: 'evaluasi', module_key: 'one-procedur', label: 'Evaluasi Prosedur', parent_key: null, order: 4, enabled: true },

    // ---- One-Roster (8 items, 8 top-level) ----
    { menu_key: 'dashboard', module_key: 'one-roster', label: 'Dashboard Monitoring', parent_key: null, order: 1, enabled: true },
    { menu_key: 'monitoring-departemen', module_key: 'one-roster', label: 'Monitoring Roster Departement', parent_key: null, order: 2, enabled: true },
    { menu_key: 'roster', module_key: 'one-roster', label: 'Roster', parent_key: null, order: 3, enabled: true },
    { menu_key: 'analisis-harian', module_key: 'one-roster', label: 'Analisis Harian', parent_key: null, order: 4, enabled: true },
    { menu_key: 'moncu', module_key: 'one-roster', label: 'MONCU', parent_key: null, order: 5, enabled: true },
    { menu_key: 'moncu-monitoring', module_key: 'one-roster', label: 'MONCU Monitoring', parent_key: null, order: 6, enabled: true },
    { menu_key: 'moncu-calendar', module_key: 'one-roster', label: 'MONCU Calendar', parent_key: null, order: 7, enabled: true },
    { menu_key: 'share', module_key: 'one-roster', label: 'Share Roster', parent_key: null, order: 8, enabled: true },
];
