// Reference (baseline) menu manifest for One-Power — taken from the OneClick
// SKS site, which is the baseline for every other OneClick site (see
// data/mock/baselineRegistry.js for the account of where the baseline comes
// from). What each site actually runs comes from the API
// (GET /api/module-observatory/{moduleKey}); the Module Observatory diffs the
// two. Not mock data.
//
// Regenerated from the SKS repo's config/one_power_menu.php: `id` is that
// file's `menu_key`, `route` its `href`, `order` the position among siblings.
// Keep ids identical to the site's — `id` is what the diff engine matches on
// across baseline/current even when a label or route changes, which is
// exactly what "modified" needs to detect (see diffMenus() in
// composables/useModuleObservatory.js).
//
// This is the single authoritative frontend One-Power manifest — both Module
// Observatory and the Master Baseline Registry (data/mock/baselineRegistry.js)
// read this same array.
export const ONE_POWER_BASELINE_MENUS = [
    { id: 'dashboard', label: 'Dashboard ATR, HM, Deviasi Operator', parentId: null, order: 1, route: '/one-power', status: 'active' },
    { id: 'data-karyawan', label: 'Data Karyawan', parentId: null, order: 2, route: '/one-power/employees', status: 'active' },
    { id: 'karyawan-nonaktif', label: 'Data Karyawan Non-Aktif', parentId: null, order: 3, route: '/one-power/employees/inactive', status: 'active' },
    { id: 'mpp-departemen', label: 'MPP Departement', parentId: null, order: 4, route: null, status: 'active' },
    { id: 'mpp-departemen-dashboard', label: 'Dashboard', parentId: 'mpp-departemen', order: 1, route: '/one-power/mpp-departemen', status: 'active' },
    { id: 'mpp-departemen-analisis', label: 'Analisis MPP', parentId: 'mpp-departemen', order: 2, route: '/one-power/mpp-departemen/analisis', status: 'active' },
    { id: 'roster', label: 'Roster', parentId: null, order: 5, route: null, status: 'active' },
    { id: 'roster-departemen', label: 'Roster Departement', parentId: 'roster', order: 1, route: '/one-power/roster-departemen', status: 'active' },
    { id: 'roster-personal', label: 'Roster Personal', parentId: 'roster', order: 2, route: '/one-power/roster-personal', status: 'active' },
    { id: 'personal-data-operator', label: 'Personal Data Operator', parentId: null, order: 6, route: null, status: 'active' },
    { id: 'personal-data-hm', label: 'HM', parentId: 'personal-data-operator', order: 1, route: '/one-power/personal-data/hm', status: 'active' },
    { id: 'personal-data-attendance', label: 'Attendance', parentId: 'personal-data-operator', order: 2, route: '/one-power/personal-data/attendance', status: 'active' },
    { id: 'personal-data-attendance-ratio', label: 'Attendance Ratio', parentId: 'personal-data-operator', order: 3, route: '/one-power/personal-data/attendance-ratio', status: 'active' },
    { id: 'personal-data-mpr', label: 'Manpower Performance Report', parentId: 'personal-data-operator', order: 4, route: '/one-power/personal-data/mpr', status: 'active' },
    { id: 'quartal-operator-report', label: 'Laporan Performa Operator', parentId: null, order: 7, route: '/one-power/quartal-operator-report', status: 'active' },
    { id: 'deviasi-operator', label: 'Deviasi Operator', parentId: null, order: 8, route: null, status: 'active' },
    { id: 'izin-karyawan', label: 'Izin Karyawan', parentId: 'deviasi-operator', order: 1, route: '/one-power/deviasi-operator/izin-karyawan', status: 'active' },
    { id: 'operator-unfit', label: 'Operator Unfit', parentId: 'deviasi-operator', order: 2, route: '/one-power/deviasi-operator/unfit', status: 'active' },
    { id: 'rekap-pemulangan', label: 'Rekap Pemulangan', parentId: 'deviasi-operator', order: 3, route: '/one-power/deviasi-operator/rekap-pemulangan', status: 'active' },
    { id: 'pembinaan', label: 'Pembinaan', parentId: null, order: 9, route: null, status: 'active' },
    { id: 'berita-acara', label: 'Berita Acara', parentId: 'pembinaan', order: 1, route: '/one-power/pembinaan/berita-acara', status: 'active' },
    { id: 'coaching-counseling', label: 'Coaching & Counseling', parentId: 'pembinaan', order: 2, route: '/one-power/pembinaan/coaching-counseling', status: 'active' },
    { id: 'interview-fatigue', label: 'Interview Fatigue', parentId: 'pembinaan', order: 3, route: '/one-power/pembinaan/interview-fatigue', status: 'active' },
    { id: 'pakta-integritas', label: 'Pakta Integritas', parentId: 'pembinaan', order: 4, route: '/one-power/pembinaan/pakta-integritas', status: 'active' },
    { id: 'sk-pembinaan', label: 'SK Pembinaan & Sanksi Pelubangan', parentId: 'pembinaan', order: 5, route: '/one-power/pembinaan/sk-pembinaan', status: 'active' },
    { id: 'surat-peringatan', label: 'Surat Peringatan', parentId: 'pembinaan', order: 6, route: '/one-power/pembinaan/surat-peringatan', status: 'active' },
    { id: 'surat-teguran', label: 'Surat Teguran', parentId: 'pembinaan', order: 7, route: '/one-power/pembinaan/surat-teguran', status: 'active' },
    { id: 'pemanggilan-karyawan', label: 'Pemanggilan Karyawan', parentId: 'pembinaan', order: 8, route: '/one-power/pembinaan/pemanggilan-karyawan', status: 'active' },
    { id: 'pemulangan-karyawan', label: 'Pemulangan Karyawan', parentId: 'pembinaan', order: 9, route: '/one-power/pembinaan/pemulangan-karyawan', status: 'active' },
    { id: 'validasi-istirahat', label: 'Validasi Istirahat', parentId: 'pembinaan', order: 10, route: '/one-power/pembinaan/validasi-istirahat', status: 'active' },
    { id: 'coaching-clinic', label: 'Coaching Clinic', parentId: null, order: 10, route: null, status: 'active' },
    { id: 'coaching-clinic-data', label: 'Data Coaching Karyawan (Coaching Clinic V1)', parentId: 'coaching-clinic', order: 1, route: '/one-power/coaching-clinic', status: 'active' },
];
