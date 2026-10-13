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
        'one-roster',
    ];

    /**
     * Flat list — every baseline menu item across every module.
     * `parent_key` is null for a top-level item.
     *
     * @var array<int, array{menu_key: string, module_key: string, parent_key: ?string}>
     */
    public const MENUS = [
        // one-power (32)
        ['menu_key' => 'dashboard', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'data-karyawan', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'karyawan-nonaktif', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'mpp-departemen', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'mpp-departemen-dashboard', 'module_key' => 'one-power', 'parent_key' => 'mpp-departemen'],
        ['menu_key' => 'mpp-departemen-analisis', 'module_key' => 'one-power', 'parent_key' => 'mpp-departemen'],
        ['menu_key' => 'roster', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'roster-departemen', 'module_key' => 'one-power', 'parent_key' => 'roster'],
        ['menu_key' => 'roster-personal', 'module_key' => 'one-power', 'parent_key' => 'roster'],
        ['menu_key' => 'personal-data-operator', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'personal-data-hm', 'module_key' => 'one-power', 'parent_key' => 'personal-data-operator'],
        ['menu_key' => 'personal-data-attendance', 'module_key' => 'one-power', 'parent_key' => 'personal-data-operator'],
        ['menu_key' => 'personal-data-attendance-ratio', 'module_key' => 'one-power', 'parent_key' => 'personal-data-operator'],
        ['menu_key' => 'personal-data-mpr', 'module_key' => 'one-power', 'parent_key' => 'personal-data-operator'],
        ['menu_key' => 'quartal-operator-report', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'deviasi-operator', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'izin-karyawan', 'module_key' => 'one-power', 'parent_key' => 'deviasi-operator'],
        ['menu_key' => 'operator-unfit', 'module_key' => 'one-power', 'parent_key' => 'deviasi-operator'],
        ['menu_key' => 'rekap-pemulangan', 'module_key' => 'one-power', 'parent_key' => 'deviasi-operator'],
        ['menu_key' => 'pembinaan', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'berita-acara', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'coaching-counseling', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'interview-fatigue', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pakta-integritas', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'sk-pembinaan', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'surat-peringatan', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'surat-teguran', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pemanggilan-karyawan', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'pemulangan-karyawan', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'validasi-istirahat', 'module_key' => 'one-power', 'parent_key' => 'pembinaan'],
        ['menu_key' => 'coaching-clinic', 'module_key' => 'one-power', 'parent_key' => null],
        ['menu_key' => 'coaching-clinic-data', 'module_key' => 'one-power', 'parent_key' => 'coaching-clinic'],

        // one-academy (4)
        ['menu_key' => 'training', 'module_key' => 'one-academy', 'parent_key' => null],
        ['menu_key' => 'monitoring-grade-jabatan', 'module_key' => 'one-academy', 'parent_key' => null],
        ['menu_key' => 'evaluasi-kompetensi', 'module_key' => 'one-academy', 'parent_key' => null],
        ['menu_key' => 'best-employee', 'module_key' => 'one-academy', 'parent_key' => null],

        // one-core-value (6)
        ['menu_key' => 'user', 'module_key' => 'one-core-value', 'parent_key' => null],
        ['menu_key' => 'role-akses', 'module_key' => 'one-core-value', 'parent_key' => null],
        ['menu_key' => 'unit', 'module_key' => 'one-core-value', 'parent_key' => null],
        ['menu_key' => 'master-spdk', 'module_key' => 'one-core-value', 'parent_key' => null],
        ['menu_key' => 'nomor-penting', 'module_key' => 'one-core-value', 'parent_key' => null],
        ['menu_key' => 'request-reset-password', 'module_key' => 'one-core-value', 'parent_key' => null],

        // one-dms (15)
        ['menu_key' => 'dashboard', 'module_key' => 'one-dms', 'parent_key' => null],
        ['menu_key' => 'formulir', 'module_key' => 'one-dms', 'parent_key' => null],
        ['menu_key' => 'data-icc', 'module_key' => 'one-dms', 'parent_key' => null],
        ['menu_key' => 'deviasi-fatigue', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'izin-fatigue', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'smoking-deviasi', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'driver-distraction', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'phone-detection', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'yawning-detection', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'eye-blink', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'camera-covering', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'forward-collision', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'video-loss-alarm', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'no-driver', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],
        ['menu_key' => 'abnormal-speed', 'module_key' => 'one-dms', 'parent_key' => 'data-icc'],

        // one-gmp (13)
        ['menu_key' => 'dashboard-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'monitoring-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'gmp-area', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'trend-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'temuan-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'penilaian-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'penilaian-mhr', 'module_key' => 'one-gmp', 'parent_key' => 'penilaian-gmp'],
        ['menu_key' => 'penilaian-mfob', 'module_key' => 'one-gmp', 'parent_key' => 'penilaian-gmp'],
        ['menu_key' => 'penilaian-mfco', 'module_key' => 'one-gmp', 'parent_key' => 'penilaian-gmp'],
        ['menu_key' => 'penilaian-disposal', 'module_key' => 'one-gmp', 'parent_key' => 'penilaian-gmp'],
        ['menu_key' => 'penilaian-dewatering', 'module_key' => 'one-gmp', 'parent_key' => 'penilaian-gmp'],
        ['menu_key' => 'approval-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],
        ['menu_key' => 'master-gmp', 'module_key' => 'one-gmp', 'parent_key' => null],

        // one-reporting (8)
        ['menu_key' => 'dashboard-compliance', 'module_key' => 'one-reporting', 'parent_key' => null],
        ['menu_key' => 'compliance', 'module_key' => 'one-reporting', 'parent_key' => null],
        ['menu_key' => 'daily-compliance', 'module_key' => 'one-reporting', 'parent_key' => 'compliance'],
        ['menu_key' => 'laporan-compliance', 'module_key' => 'one-reporting', 'parent_key' => 'compliance'],
        ['menu_key' => 'setting-gl', 'module_key' => 'one-reporting', 'parent_key' => 'compliance'],
        ['menu_key' => 'master-compliance', 'module_key' => 'one-reporting', 'parent_key' => 'compliance'],
        ['menu_key' => 'perbaikan-pasca-insiden', 'module_key' => 'one-reporting', 'parent_key' => null],
        ['menu_key' => 'ojk', 'module_key' => 'one-reporting', 'parent_key' => null],

        // one-setting (13)
        ['menu_key' => 'dashboard', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'setting-board', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'settingan-v2', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'settingan-v2-manpower', 'module_key' => 'one-setting', 'parent_key' => 'settingan-v2'],
        ['menu_key' => 'settingan-v2-monitoring-mp', 'module_key' => 'one-setting', 'parent_key' => 'settingan-v2'],
        ['menu_key' => 'setting-fleet', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'setting-fleet-master', 'module_key' => 'one-setting', 'parent_key' => 'setting-fleet'],
        ['menu_key' => 'setting-fleet-harian', 'module_key' => 'one-setting', 'parent_key' => 'setting-fleet'],
        ['menu_key' => 'settingan-v2-monitoring', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'setting-gl', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'setting-unit-support', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'setting-dozer-pc', 'module_key' => 'one-setting', 'parent_key' => null],
        ['menu_key' => 'setting-pw-cp', 'module_key' => 'one-setting', 'parent_key' => null],

        // one-procedur (4)
        ['menu_key' => 'dashboard', 'module_key' => 'one-procedur', 'parent_key' => null],
        ['menu_key' => 'prosedur', 'module_key' => 'one-procedur', 'parent_key' => null],
        ['menu_key' => 'activity', 'module_key' => 'one-procedur', 'parent_key' => null],
        ['menu_key' => 'evaluasi', 'module_key' => 'one-procedur', 'parent_key' => null],

        // one-roster (8)
        ['menu_key' => 'dashboard', 'module_key' => 'one-roster', 'parent_key' => null],
        ['menu_key' => 'monitoring-departemen', 'module_key' => 'one-roster', 'parent_key' => null],
        ['menu_key' => 'roster', 'module_key' => 'one-roster', 'parent_key' => null],
        ['menu_key' => 'analisis-harian', 'module_key' => 'one-roster', 'parent_key' => null],
        ['menu_key' => 'moncu', 'module_key' => 'one-roster', 'parent_key' => null],
        ['menu_key' => 'moncu-monitoring', 'module_key' => 'one-roster', 'parent_key' => null],
        ['menu_key' => 'moncu-calendar', 'module_key' => 'one-roster', 'parent_key' => null],
        ['menu_key' => 'share', 'module_key' => 'one-roster', 'parent_key' => null],
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
