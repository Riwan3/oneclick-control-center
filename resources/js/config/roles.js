// Centralized ROLE DEFINITION + ROLE CAPABILITIES layer.
//
// This is a frontend UI-visibility model only. It decides what a Control
// Center role can currently SEE, and documents what it will eventually be
// able to MANAGE — it is not a security boundary. Real authorization will
// live on the backend once authentication exists.
//
// A OneClick site's own local "Super Admin" is a separate, site-level
// concept and is not represented here.
export const roles = [
    {
        key: 'super_admin',
        label: 'Super Admin',
        description: 'Full Control Center administration and governance.',
        capabilities: {
            overview: true,
            kpi: true,
            site_view: true,
            site_manage: true,
            module_view: true,
            module_manage: true,
            monitoring: true,
            audit_view: true,
            audit_manage: true,
            change_log_view: true,
            version_view: true,
            user_manage: true,
            settings_manage: true,
            site_configuration_manage: true,
            release_manage: true,
        },
    },
    {
        key: 'management',
        label: 'Management',
        description: 'Management visibility and monitoring. Read/monitor only — cannot modify site, module, user, or governance configuration.',
        capabilities: {
            overview: true,
            kpi: true,
            site_view: true,
            site_manage: false,
            module_view: true,
            module_manage: false,
            monitoring: true,
            audit_view: true,
            audit_manage: false,
            change_log_view: true,
            version_view: true,
            user_manage: false,
            settings_manage: false,
            site_configuration_manage: false,
            release_manage: false,
        },
    },
    {
        key: 'view',
        label: 'View',
        description: 'Restricted, high-level KPI and site performance visibility only.',
        capabilities: {
            overview: true,
            kpi: true,
            site_view: true,
            site_manage: false,
            module_view: false,
            module_manage: false,
            monitoring: false,
            audit_view: false,
            audit_manage: false,
            change_log_view: false,
            version_view: false,
            user_manage: false,
            settings_manage: false,
            site_configuration_manage: false,
            release_manage: false,
        },
    },
];

export const CONTROL_CENTER_ROLES = roles.map((role) => role.key);

export function getRole(roleKey) {
    return roles.find((role) => role.key === roleKey) ?? null;
}
