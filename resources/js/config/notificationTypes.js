// Display metadata for the notification taxonomy. The taxonomy itself
// (user_created, deployment_update, site_warning, system, audit) is
// controlled backend-side by App\Services\NotificationService::TYPES —
// this only maps those same type keys to how they render here, so the
// mapping isn't duplicated across every component that shows a
// notification.
export const NOTIFICATION_TYPES = [
    { value: 'user_created', label: 'User Created', icon: 'user' },
    { value: 'deployment_update', label: 'Deployment Update', icon: 'package' },
    { value: 'site_warning', label: 'Site Warning', icon: 'sites' },
    { value: 'system', label: 'System', icon: 'monitoring' },
    { value: 'audit', label: 'Audit', icon: 'audit' },
];

const SEVERITY_CLASSES = {
    info: 'bg-info-soft text-info',
    success: 'bg-success-soft text-success',
    warning: 'bg-warning-soft text-warning',
    danger: 'bg-danger-soft text-danger',
};

export function notificationIcon(type) {
    return NOTIFICATION_TYPES.find((t) => t.value === type)?.icon ?? 'bell';
}

export function notificationSeverityClass(severity) {
    return SEVERITY_CLASSES[severity] ?? 'bg-neutral-soft text-neutral';
}
