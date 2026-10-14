// Single reusable frontend access-scope mechanism — every page that
// renders site- or module-scoped data reads from here instead of
// re-deriving "does this user see site X" itself. Backed entirely by
// the authenticated user's effective access already returned by
// /api/user (see App\Models\User::effectiveSiteAccess()/
// effectiveModuleAccess() and UserResource) — no separate fetch, no
// hardcoded usernames/site ids/role names.
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useSiteContextStore } from '../stores/siteContext';

export function useEffectiveAccess() {
    const auth = useAuthStore();
    const siteContext = useSiteContextStore();

    const isSuperAdmin = computed(() => auth.role === 'super_admin');

    // null (not []) while the user hasn't loaded yet, so canAccessSite()
    // fails closed (returns false) instead of matching an empty list by
    // accident of "not super admin -> [].includes(x) === false" — same
    // result, but explicit about the unloaded state for callers that
    // care (e.g. to avoid rendering a flash of "no sites" before the
    // user resolves).
    const siteAccessMode = computed(() => auth.user?.siteAccess?.mode ?? null);
    const allowedSiteCodes = computed(() => auth.user?.siteAccess?.siteCodes ?? []);
    const moduleAccessMode = computed(() => auth.user?.moduleAccess?.mode ?? null);
    const allowedModuleKeys = computed(() => auth.user?.moduleAccess?.moduleKeys ?? []);

    // Trusts the backend-resolved `mode` directly rather than
    // re-deriving "all" from role — effectiveSiteAccess()/
    // effectiveModuleAccess() already force 'all' for super_admin, but
    // checking `mode` (not role) here means a non-super-admin row that
    // is (or was, e.g. legacy data) stored as 'all' is still honored
    // correctly as full access, instead of silently resolving to zero
    // access because it isn't super_admin.
    function hasSiteAccess(code) {
        if (!auth.user) return false;
        return siteAccessMode.value === 'all' || allowedSiteCodes.value.includes(code);
    }

    // hasSiteAccess() narrowed by the topbar's Site Context selector, so
    // every page that filters through here follows the selected site.
    // The selector's own option list uses hasSiteAccess() instead.
    function canAccessSite(code) {
        if (!hasSiteAccess(code)) return false;
        const selected = siteContext.selected.key;
        return selected === 'all' || selected === code;
    }

    function canAccessModule(key) {
        if (!auth.user) return false;
        return moduleAccessMode.value === 'all' || allowedModuleKeys.value.includes(key);
    }

    return {
        isSuperAdmin,
        siteAccessMode,
        allowedSiteCodes,
        moduleAccessMode,
        allowedModuleKeys,
        hasSiteAccess,
        canAccessSite,
        canAccessModule,
    };
}
