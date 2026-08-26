<?php

namespace App\Support;

/**
 * STRUCTURAL SKELETON of the Master Baseline Registry — module keys and
 * menu keys/hierarchy ONLY. No labels, no descriptions, no
 * presentational content, no order values used for display.
 *
 * WHY THIS EXISTS: App\Services\ConfigurationSnapshotService must
 * compute a site's EFFECTIVE module/menu state as a genuine backend
 * operation — a snapshot is an immutable historical record, so it
 * cannot be computed by trusting a client-submitted payload (any caller
 * with API access could submit a fabricated "effective configuration"
 * that doesn't reflect reality). That computation is impossible without
 * the backend knowing the full baseline KEY SET, because "effective
 * enabled" for anything NOT explicitly overridden in
 * site_module_configs/site_menu_configs defaults to the baseline itself
 * (always enabled), and determining a parent menu's derived visibility
 * requires knowing which keys are its children.
 *
 * This is intentionally NOT "duplicating the baseline" in the sense the
 * project has repeatedly guarded against — it carries none of the
 * baseline's actual content (labels, descriptions). It is the smallest
 * possible mirror that still makes snapshot computation possible.
 *
 * *** KEEP IN SYNC ***
 * The single source of truth for the baseline's real content remains
 * resources/js/data/mock/baselineRegistry.js. If a module or menu key
 * is added, removed, or its parent changes there, the corresponding
 * entries here MUST be updated to match, or snapshot effective-state
 * calculations will silently use a stale key set. This file was
 * generated from that registry's state at the time STEP 3R-1
 * (Configuration Snapshot Foundation) was built — see the STEP report
 * for the explicit decision not to build a shared/generated registry
 * mechanism (would require restructuring baselineRegistry.js itself,
 * judged out of scope for this step).
 */
class BaselineKeys
{
    public const MODULE_KEYS = [
        'one-power',
        'one-academy',
        'one-core-value',
        'one-dms',
        'one-gmp',
        'one-reporting',
        'one-setting',
        'one-procedur',
    ];

    /**
     * Flat list — every baseline menu item across every module.
     * `parent_key` is null for a top-level item.
     *
     * @var array<int, array{menu_key: string, module_key: string, parent_key: ?string}>
     */
    public const MENUS = [
        // one-power (26)
        ['menu_key' => 'dashboard', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'data-karyawan', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'karyawan-nonaktif', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'mpp', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'roster', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'roster-departement', 'module_key' => 'one-power', 'parent_key' => 'roster'],
        ['menu_key' => 'roster-personal', 'module_key' => 'one-power', 'parent_key' => 'roster'],
        ['menu_key' => 'personal-data-operator', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'pdo-hm', 'module_key' => 'one-power', 'parent_key' => 'personal-data-operator'],
        ['menu_key' => 'pdo-attendance', 'module_key' => 'one-power', 'parent_key' => 'personal-data-operator'],
        ['menu_key' => 'pdo-mpr', 'module_key' => 'one-power', 'parent_key' => 'personal-data-operator'],
        ['menu_key' => 'mor', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'quartal-operator-report', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'deviasi-operator', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'deviasi-izin-karyawan', 'module_key' => 'one-power', 'parent_key' => 'deviasi-operator'],
        ['menu_key' => 'deviasi-data-unfit-karyawan', 'module_key' => 'one-power', 'parent_key' => 'deviasi-operator'],
        ['menu_key' => 'pembinaan', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'pembinaan-berita-acara', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pembinaan-coaching-counseling', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pembinaan-pakta-integritas', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pembinaan-sk-sanksi-pelubangan', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pembinaan-surat-peringatan', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pembinaan-surat-teguran', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pembinaan-pemanggilan-karyawan', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'coaching-clinic', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'coaching-clinic-data-coaching-karyawan', 'module_key' => 'one-power', 'parent_key' => 'coaching-clinic'],

        // one-academy (3)
        ['menu_key' => 'monitoring-grade-jabatan', 'module_key' => 'one-academy', 'parent_key' => null],
        ['menu_key' => 'evaluasi-kompetensi', 'module_key' => 'one-academy', 'parent_key' => null],
        ['menu_key' => 'best-employe', 'module_key' => 'one-academy', 'parent_key' => null],

        // one-core-value (4)
        ['menu_key' => 'user-oneclick', 'module_key' => 'one-core-value', 'parent_key' => null],
        ['menu_key' => 'unit-produksi', 'module_key' => 'one-core-value', 'parent_key' => null],
        ['menu_key' => 'nomor-penting', 'module_key' => 'one-core-value', 'parent_key' => null],
        ['menu_key' => 'request-reset-password', 'module_key' => 'one-core-value', 'parent_key' => null],

        // one-dms (14)
        ['menu_key' => 'dashboard-dms', 'module_key' => 'one-dms', 'parent_key' => null],
        ['menu_key' => 'formulir-dms', 'module_key' => 'one-dms', 'parent_key' => null],
        ['menu_key' => 'data-icc', 'module_key' => 'one-dms', 'parent_key' => null],
        ['menu_key' => 'icc-deviasi-fatigue', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-smoking', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-driver-distraction', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-phone-detection', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-yawning-detection', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-eyeblink', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-camera-covering', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-forward-collision', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-video-loss', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-no-driver', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'icc-abnormal-over-speed', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],

        // one-gmp (11)
        ['menu_key' => 'dashboard-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'trend-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'gmp-area', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'penilaian', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'approval', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'master-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'master-gmp-mhr', 'module_key' => 'one-gmp', 'parent_key' => 'master-gmp'],
        ['menu_key' => 'master-gmp-mfob', 'module_key' => 'one-gmp', 'parent_key' => 'master-gmp'],
        ['menu_key' => 'master-gmp-mfoc', 'module_key' => 'one-gmp', 'parent_key' => 'master-gmp'],
        ['menu_key' => 'master-gmp-disposal', 'module_key' => 'one-gmp', 'parent_key' => 'master-gmp'],
        ['menu_key' => 'master-gmp-dewatering', 'module_key' => 'one-gmp', 'parent_key' => 'master-gmp'],

        // one-reporting (5)
        ['menu_key' => 'dashboard-compliance', 'module_key' => 'one-reporting', 'parent_key' => null],
        ['menu_key' => 'daily-compliance', 'module_key' => 'one-reporting', 'parent_key' => null],
        ['menu_key' => 'laporan-compliance', 'module_key' => 'one-reporting', 'parent_key' => null],
        ['menu_key' => 'master-data-compliance', 'module_key' => 'one-reporting', 'parent_key' => null],
        ['menu_key' => 'ojk', 'module_key' => 'one-reporting', 'parent_key' => null],

        // one-setting (6)
        ['menu_key' => 'settingan', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'settingan-input-manpower', 'module_key' => 'one-setting', 'parent_key' => 'settingan'],
        ['menu_key' => 'settingan-input-fleet', 'module_key' => 'one-setting', 'parent_key' => 'settingan'],
        ['menu_key' => 'settingan-data-alokasi-manpower', 'module_key' => 'one-setting', 'parent_key' => 'settingan'],
        ['menu_key' => 'monitoring-settingan-manpower', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'monitoring-setting-fleet', 'module_key' => 'one-setting', 'parent_key' => null],

        // one-procedur (3)
        ['menu_key' => 'prosedur', 'module_key' => 'one-procedur', 'parent_key' => null],
        ['menu_key' => 'activity', 'module_key' => 'one-procedur', 'parent_key' => null],
        ['menu_key' => 'evaluasi-prosedur', 'module_key' => 'one-procedur', 'parent_key' => null],
    ];

    public static function menusForModule(string $moduleKey): array
    {
        return array_values(array_filter(self::MENUS, fn (array $menu) => $menu['module_key'] === $moduleKey));
    }

    public static function childrenOf(string $moduleKey, string $menuKey): array
    {
        return array_values(array_filter(
            self::MENUS,
            fn (array $menu) => $menu['module_key'] === $moduleKey && $menu['parent_key'] === $menuKey
        ));
    }

    public static function isLeaf(string $moduleKey, string $menuKey): bool
    {
        return count(self::childrenOf($moduleKey, $menuKey)) === 0;
    }
}
