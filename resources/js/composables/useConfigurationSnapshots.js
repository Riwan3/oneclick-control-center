// Configuration Snapshot Foundation — Page -> Composable -> Service ->
// Laravel API. The snapshot's own enabled/disabled VALUES are 100%
// authoritative from the backend (immutable, historical) — this
// composable only resolves human-readable LABELS for display by
// joining the snapshot's module_key/menu_key against the current
// Master Baseline Registry. That join is presentation-only: stable
// keys are never silently renamed (see baselineRegistry.js's own
// rules), so this can't misrepresent what was actually enabled/disabled
// at snapshot time — only what it's called today.
import { ref, computed } from 'vue';
import { baselineModules, baselineMenus } from '../data/mock/baselineRegistry';
import { configurationSnapshotService } from '../services/configurationSnapshotService';

function labelForModule(moduleKey) {
    return baselineModules.find((mod) => mod.module_key === moduleKey)?.label ?? moduleKey;
}

// Menu keys are unique per module only (several modules have a 'dashboard').
function labelForMenu(moduleKey, menuKey) {
    return baselineMenus.find((menu) => menu.module_key === moduleKey && menu.menu_key === menuKey)?.label ?? menuKey;
}

export function useConfigurationSnapshots() {
    const snapshots = ref([]);
    const loadingList = ref(false);
    const listError = ref(null);

    const selectedSnapshot = ref(null);
    const loadingDetail = ref(false);
    const detailError = ref(null);

    const creating = ref(false);
    const createError = ref(null);

    async function loadSnapshots(siteId) {
        loadingList.value = true;
        listError.value = null;
        const { ok, data } = await configurationSnapshotService.fetchAll(siteId);
        if (ok) {
            snapshots.value = data?.data ?? [];
        } else {
            listError.value = data?.message ?? 'Unable to load snapshots.';
        }
        loadingList.value = false;
    }

    async function loadSnapshotDetail(siteId, snapshotId) {
        loadingDetail.value = true;
        detailError.value = null;
        const { ok, data } = await configurationSnapshotService.fetchOne(siteId, snapshotId);
        if (ok) {
            selectedSnapshot.value = data?.data ?? null;
        } else {
            detailError.value = data?.message ?? 'Unable to load snapshot detail.';
        }
        loadingDetail.value = false;
    }

    function clearSelectedSnapshot() {
        selectedSnapshot.value = null;
        detailError.value = null;
    }

    async function createSnapshot(siteId) {
        creating.value = true;
        createError.value = null;
        const { ok, data } = await configurationSnapshotService.create(siteId);
        if (ok) {
            await loadSnapshots(siteId);
        } else {
            createError.value = data?.message ?? 'Unable to create snapshot.';
        }
        creating.value = false;
        return ok;
    }

    // Snapshot modules/menus reshaped with resolved labels for display
    // — the tree shape here matches SidebarManifest.vue's existing
    // {id, label, parentId, order} contract, so the snapshot detail
    // reuses that component (already read-only, no toggle controls)
    // instead of a second tree renderer.
    const selectedSnapshotModules = computed(() => {
        if (!selectedSnapshot.value) return [];
        return (selectedSnapshot.value.modules ?? [])
            .map((mod) => ({ moduleKey: mod.moduleKey, label: labelForModule(mod.moduleKey), enabled: mod.enabled }))
            .sort((a, b) => a.label.localeCompare(b.label));
    });

    function selectedSnapshotMenusForModule(moduleKey) {
        if (!selectedSnapshot.value) return [];
        const baselineForModule = baselineMenus.filter((menu) => menu.module_key === moduleKey);
        const orderByKey = new Map(baselineForModule.map((menu) => [menu.menu_key, menu.order]));
        const parentByKey = new Map(baselineForModule.map((menu) => [menu.menu_key, menu.parent_key]));

        return (selectedSnapshot.value.menus ?? [])
            .filter((menu) => menu.moduleKey === moduleKey)
            .map((menu) => ({
                id: menu.menuKey,
                label: labelForMenu(moduleKey, menu.menuKey),
                parentId: parentByKey.get(menu.menuKey) ?? null,
                order: orderByKey.get(menu.menuKey) ?? 0,
                status: menu.enabled ? 'active' : 'inactive',
            }));
    }

    return {
        snapshots,
        loadingList,
        listError,
        loadSnapshots,
        selectedSnapshot,
        selectedSnapshotModules,
        selectedSnapshotMenusForModule,
        loadingDetail,
        detailError,
        loadSnapshotDetail,
        clearSelectedSnapshot,
        creating,
        createError,
        createSnapshot,
    };
}
