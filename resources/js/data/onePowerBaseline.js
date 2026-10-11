// Reference (baseline) menu manifest for One-Power — the official business
// baseline, NOT site data. What each site actually runs comes from the API
// (GET /api/module-observatory/{moduleKey}); the Module Observatory diffs
// the two. Moved out of data/mock/ because nothing here is mock.
//
// BASELINE MENU MANIFEST — One-Power's reference sidebar structure.
//
// CORRECTED against the official OneClick.docx business baseline (see
// data/mock/baselineRegistry.js for the full source-of-truth account).
// The original STEP 3R version of this array was built from an
// approximate screenshot reading and has been superseded by the
// authoritative document; the corrections made here:
//   - labels refined to exact baseline terminology (ids kept stable —
//     e.g. 'karyawan-nonaktif' still backs "Data Karyawan Non-Aktif",
//     'mpp' still backs "MPP Departement")
//   - Roster gained 2 children (roster-departement, roster-personal) —
//     previously a leaf, not invented, confirmed by the baseline
//   - Personal Data Operator's children reduced from 5 to the
//     baseline's 3: pdo-kimber and pdo-attendance-v2 are REMOVED (not
//     present in the official document); pdo-mpr's label expanded to
//     its full official form, "Manpower Performance Report"
//   - Deviasi Operator gained 2 children (izin-karyawan,
//     data-unfit-karyawan) — previously a leaf
//   - 'kpi' is REMOVED — not present in the official document
//   - Pembinaan gained 7 children — previously a leaf
//   - Coaching Clinic gained 1 child — previously a leaf
//
// This is the single authoritative frontend manifest for One-Power —
// both Module Observatory and the Master Baseline Registry
// (data/mock/baselineRegistry.js) read this same array. There is no
// second One-Power baseline anywhere in the codebase.
//
// `id` is the stable identifier the diff engine matches on across
// baseline/current even when a label or route changes — that mismatch
// is exactly what "modified" needs to detect (see diffMenus() in
// composables/useModuleObservatory.js). Sibling `order` values restart
// at 1 within each parent group.
export const ONE_POWER_BASELINE_MENUS = [
    { id: 'dashboard', label: 'Dashboard ATR,HM,Deviasi Operator', parentId: null, order: 1, route: '/dashboard', status: 'active' },
    { id: 'data-karyawan', label: 'Data Karyawan', parentId: null, order: 2, route: '/data-karyawan', status: 'active' },
    { id: 'karyawan-nonaktif', label: 'Data Karyawan Non-Aktif', parentId: null, order: 3, route: '/data-karyawan-non-aktif', status: 'active' },
    { id: 'mpp', label: 'MPP Departement', parentId: null, order: 4, route: '/mpp-departement', status: 'active' },

    { id: 'roster', label: 'Roster', parentId: null, order: 5, route: '/roster', status: 'active' },
    { id: 'roster-departement', label: 'Roster Departement', parentId: 'roster', order: 1, route: '/roster/departement', status: 'active' },
    { id: 'roster-personal', label: 'Roster Personal', parentId: 'roster', order: 2, route: '/roster/personal', status: 'active' },

    { id: 'personal-data-operator', label: 'Personal Data Operator', parentId: null, order: 6, route: '/personal-data-operator', status: 'active' },
    { id: 'pdo-hm', label: 'HM', parentId: 'personal-data-operator', order: 1, route: '/personal-data-operator/hm', status: 'active' },
    { id: 'pdo-attendance', label: 'Attendance', parentId: 'personal-data-operator', order: 2, route: '/personal-data-operator/attendance', status: 'active' },
    { id: 'pdo-mpr', label: 'Manpower Performance Report', parentId: 'personal-data-operator', order: 3, route: '/personal-data-operator/mpr', status: 'active' },

    { id: 'mor', label: 'MOR', parentId: null, order: 7, route: '/mor', status: 'active' },
    { id: 'quartal-operator-report', label: 'Quartal Operator Report', parentId: null, order: 8, route: '/quartal-operator-report', status: 'active' },

    { id: 'deviasi-operator', label: 'Deviasi Operator', parentId: null, order: 9, route: '/deviasi-operator', status: 'active' },
    { id: 'deviasi-izin-karyawan', label: 'Izin Karyawan', parentId: 'deviasi-operator', order: 1, route: '/deviasi-operator/izin-karyawan', status: 'active' },
    { id: 'deviasi-data-unfit-karyawan', label: 'Data Unfit Karyawan', parentId: 'deviasi-operator', order: 2, route: '/deviasi-operator/data-unfit-karyawan', status: 'active' },

    { id: 'pembinaan', label: 'Pembinaan', parentId: null, order: 10, route: '/pembinaan', status: 'active' },
    { id: 'pembinaan-berita-acara', label: 'Berita Acara', parentId: 'pembinaan', order: 1, route: '/pembinaan/berita-acara', status: 'active' },
    { id: 'pembinaan-coaching-counseling', label: 'Coaching & Counseling', parentId: 'pembinaan', order: 2, route: '/pembinaan/coaching-counseling', status: 'active' },
    { id: 'pembinaan-pakta-integritas', label: 'Pakta Integritas', parentId: 'pembinaan', order: 3, route: '/pembinaan/pakta-integritas', status: 'active' },
    { id: 'pembinaan-sk-sanksi-pelubangan', label: 'SK Pembinaan & Sanksi Pelubangan', parentId: 'pembinaan', order: 4, route: '/pembinaan/sk-sanksi-pelubangan', status: 'active' },
    { id: 'pembinaan-surat-peringatan', label: 'Surat Peringatan', parentId: 'pembinaan', order: 5, route: '/pembinaan/surat-peringatan', status: 'active' },
    { id: 'pembinaan-surat-teguran', label: 'Surat Teguran', parentId: 'pembinaan', order: 6, route: '/pembinaan/surat-teguran', status: 'active' },
    { id: 'pembinaan-pemanggilan-karyawan', label: 'Pemanggilan Karyawan', parentId: 'pembinaan', order: 7, route: '/pembinaan/pemanggilan-karyawan', status: 'active' },

    { id: 'coaching-clinic', label: 'Coaching Clinic', parentId: null, order: 11, route: '/coaching-clinic', status: 'active' },
    { id: 'coaching-clinic-data-coaching-karyawan', label: 'Data Coaching Karyawan (Coaching Clinic V1)', parentId: 'coaching-clinic', order: 1, route: '/coaching-clinic/data-coaching-karyawan', status: 'active' },
];
