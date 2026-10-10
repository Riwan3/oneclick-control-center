// Centralized status/category option lists for Release Management —
// same centralization pattern as config/roles.js / config/notificationTypes.js.
// Values must stay in sync with App\Models\Release::STATUSES and
// App\Models\ReleaseChangelog::CATEGORIES (backend validation is the
// real authority; this only drives display).
export const Release = {
    STATUSES: ['draft', 'ready', 'released', 'archived'],
    STATUS_LABEL: {
        draft: 'Draft',
        ready: 'Ready',
        released: 'Released',
        archived: 'Archived',
    },
    STATUS_VARIANT: {
        draft: 'neutral',
        ready: 'info',
        released: 'success',
        archived: 'warning',
    },
};

export const Changelog = {
    CATEGORIES: ['new', 'improvement', 'changed', 'fixed'],
    CATEGORY_LABEL: {
        new: 'New',
        improvement: 'Improvement',
        changed: 'Changed',
        fixed: 'Fixed',
    },
    CATEGORY_VARIANT: {
        new: 'success',
        improvement: 'info',
        changed: 'warning',
        fixed: 'neutral',
    },
};
