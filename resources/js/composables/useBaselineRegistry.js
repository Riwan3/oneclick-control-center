// Master Baseline Registry business logic — Page -> Composable ->
// Service (see baselineRegistryService.js). No business logic lives in
// Baseline.vue itself.
//
// This registry is business-wide, not site-scoped (site configuration
// is explicitly out of scope for this step — see the STEP report), so
// unlike Module Observatory this composable does not filter through
// useEffectiveAccess(); page-level visibility is capability-gated only
// (auth.can('change_log_view') in Baseline.vue).
import { ref, computed, onMounted } from 'vue';
import { baselineRegistryService } from '../services/baselineRegistryService';

export function useBaselineRegistry() {
    const raw = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const selectedModuleKey = ref(null);

    async function load() {
        loading.value = true;
        error.value = null;
        try {
            raw.value = await baselineRegistryService.getRegistry();
        } catch {
            error.value = 'Unable to load the baseline registry.';
        } finally {
            loading.value = false;
        }
    }

    const menus = computed(() => raw.value?.menus ?? []);

    // Every count here is derived from the registry data itself — no
    // hardcoded KPI values anywhere in this composable or the page.
    const modules = computed(() => {
        if (!raw.value) return [];
        return [...raw.value.modules]
            .sort((a, b) => a.order - b.order)
            .map((mod) => ({
                ...mod,
                sidebarCount: menus.value.filter((menu) => menu.module_key === mod.module_key).length,
            }));
    });

    // "Active" here means the registry's own enabled/disabled toggle —
    // NOT businessBaseline/technicalMapping, which are a separate axis
    // (every module in this registry has a fully verified business
    // baseline; see BaselineModuleTable.vue for how those two are shown
    // distinctly rather than folded into one status).
    const summary = computed(() => {
        const list = modules.value;
        return {
            totalModules: list.length,
            totalSidebarItems: menus.value.length,
            activeModules: list.filter((mod) => mod.enabled).length,
            activeSidebarItems: menus.value.filter((menu) => menu.enabled).length,
        };
    });

    const selectedModule = computed(() => {
        if (!modules.value.length) return null;
        return modules.value.find((mod) => mod.module_key === selectedModuleKey.value) ?? modules.value[0];
    });

    // Reshaped to the {id, label, parentId, order} shape that
    // components/module-observatory/SidebarManifest.vue already expects
    // — reusing that existing tree renderer (and its own internal
    // parent/child grouping logic) instead of writing a second one.
    const selectedModuleMenus = computed(() => {
        if (!selectedModule.value) return [];
        return menus.value
            .filter((menu) => menu.module_key === selectedModule.value.module_key)
            .map((menu) => ({
                id: menu.menu_key,
                label: menu.label,
                parentId: menu.parent_key,
                order: menu.order,
                status: menu.enabled ? 'active' : 'inactive',
            }));
    });

    function selectModule(moduleKey) {
        if (modules.value.some((mod) => mod.module_key === moduleKey)) {
            selectedModuleKey.value = moduleKey;
        }
    }

    onMounted(load);

    return {
        loading,
        error,
        baselineVersion: computed(() => raw.value?.baselineVersion ?? null),
        modules,
        summary,
        selectedModule,
        selectedModuleMenus,
        selectModule,
        legacyModuleMapping: computed(() => raw.value?.legacyModuleMapping ?? []),
    };
}
