// NAVIGATION STATE layer.
//
// Takes the static CONFIGURATION (config/navigation.js) and resolves it
// against the current mock role/site state (stores/auth.js, stores/site.js)
// into a grouped, ready-to-render structure. Role visibility is resolved
// via the centralized role capability matrix (config/roles.js, exposed
// through auth.can()) — this file has no role logic of its own.
// AppSidebar.vue (PRESENTATION) only consumes the output of this
// composable — it does not filter or group anything itself.
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useSiteStore } from '../stores/site';
import { navigationItems } from '../config/navigation';

function isVisibleForCapability(item, auth) {
    return !item.capability || auth.can(item.capability);
}

function isVisibleForSite(item, currentSite) {
    return item.sites !== 'selected' || Boolean(currentSite);
}

function isModuleAvailable(item, currentSite) {
    if (!item.module) return true;
    if (!currentSite) return false;
    return Boolean(currentSite.modules?.[item.module]);
}

export function useNavigation() {
    const auth = useAuthStore();
    const site = useSiteStore();

    const items = computed(() =>
        navigationItems
            .filter((item) => isVisibleForCapability(item, auth))
            .filter((item) => isVisibleForSite(item, site.currentSite))
            .map((item) => ({
                ...item,
                available: item.enabled && isModuleAvailable(item, site.currentSite),
            }))
            .sort((a, b) => a.order - b.order)
    );

    const groups = computed(() => {
        const bySection = [];
        for (const item of items.value) {
            const last = bySection[bySection.length - 1];
            if (last && last.section === item.section) {
                last.items.push(item);
            } else {
                bySection.push({ section: item.section, items: [item] });
            }
        }
        return bySection;
    });

    return { items, groups };
}
