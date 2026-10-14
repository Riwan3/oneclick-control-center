// Site Configuration Foundation business logic — Page -> Composable ->
// Service -> Laravel API (see siteConfigurationService.js). No business
// logic lives in SiteConfiguration.vue itself.
//
// EFFECTIVE CONFIGURATION = BASELINE (data/mock/baselineRegistry.js,
// the Master Baseline Registry — untouched, not duplicated) + SITE
// CONFIGURATION (backend overrides fetched from /api/site-configurations).
// effectiveSiteModules()/effectiveSiteMenus() are the one reusable
// resolver every KPI/tree/table in this feature is built from — no
// second copy of this combination logic exists anywhere else.
//
// Independent from user site/module ACCESS (useEffectiveAccess.js,
// user_site_access/user_module_access) — this composable answers "what
// does this site release", not "what may this user see." The two are
// never combined here.
import { ref, computed, onMounted } from 'vue';
import { sites as mockSites } from '../data/siteCatalogue';
import { useSiteContextStore } from '../stores/siteContext';
import { baselineModules, baselineMenus } from '../data/mock/baselineRegistry';
import { siteConfigurationService } from '../services/siteConfigurationService';

const TOTAL_MODULES = baselineModules.length;
const TOTAL_MENUS = baselineMenus.length;

function round1(value) {
    return Math.round(value * 10) / 10;
}

export function useSiteConfiguration() {
    const siteContext = useSiteContextStore();
    const rawConfigs = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const saving = ref(false);
    const selectedSiteCode = ref(null);
    const selectedModuleKey = ref(null);

    async function load() {
        loading.value = true;
        error.value = null;
        const { ok, data } = await siteConfigurationService.fetchAll();
        if (ok) {
            rawConfigs.value = data?.data ?? [];
        } else {
            error.value = data?.message ?? 'Unable to load site configuration.';
        }
        loading.value = false;
    }

    // Joins the backend's override rows to the frontend mock site
    // catalogue by `code` — the same join key useEffectiveAccess()/
    // Sites.vue already use between real backend site codes and the
    // mock catalogue; no second site table is introduced.
    const sites = computed(() =>
        rawConfigs.value
            .map((raw) => ({ ...raw, identity: mockSites.find((site) => site.code === raw.siteCode) }))
            .filter((entry) => entry.identity)
            .filter((entry) => siteContext.selected.key === 'all' || siteContext.selected.key === entry.siteCode)
            .sort((a, b) => a.identity.name.localeCompare(b.identity.name))
    );

    function findRaw(siteId) {
        return rawConfigs.value.find((entry) => entry.siteId === siteId);
    }

    function effectiveSiteModules(siteId) {
        const raw = findRaw(siteId);
        const overrides = new Map((raw?.moduleOverrides ?? []).map((o) => [o.moduleKey, o.enabled]));

        const baseline = baselineModules.map((mod) => ({
            module_key: mod.module_key,
            label: mod.label,
            order: mod.order,
            extension: false,
            // Baseline default is always "enabled" — every baseline
            // module is enabled by definition in the registry. An
            // explicit site override, if present, wins.
            baselineEnabled: mod.enabled,
            siteEnabled: overrides.has(mod.module_key) ? overrides.get(mod.module_key) : mod.enabled,
        }));

        // Extensions: modules this site reports that are not in the baseline.
        // They follow the same sparse-override rule (on unless switched off)
        // but are not part of the baseline, so they never count in its totals.
        const extensions = (raw?.extensions ?? []).map((ext, index) => ({
            module_key: ext.key,
            label: ext.label,
            order: 1000 + index,
            extension: true,
            baselineEnabled: true,
            siteEnabled: overrides.has(ext.key) ? overrides.get(ext.key) : true,
        }));

        return [...baseline, ...extensions];
    }

    // PARENT/CHILD RULE (documented once, here — see the STEP report
    // for the full reasoning; not re-derived anywhere else):
    // - A LEAF menu item (no children in the baseline hierarchy) is
    //   independently configurable. An explicit site override, if
    //   present, wins over the baseline default.
    // - A PARENT item (has children) has no independently stored
    //   override — its effective visibility is DERIVED: visible if at
    //   least one child is effectively enabled, hidden if every child
    //   is disabled. This matches "if a parent is a standalone menu
    //   WITHOUT children, its own enabled state controls visibility" —
    //   by contrast, a parent WITH children is governed by its
    //   children, not a separate flag of its own. The baseline parent
    //   row itself is never deleted or mutated either way.
    function effectiveSiteMenus(siteId, moduleKey) {
        const raw = findRaw(siteId);
        const overrides = new Map(
            (raw?.menuOverrides ?? [])
                .filter((o) => o.moduleKey === moduleKey)
                .map((o) => [o.menuKey, o.enabled])
        );
        // An extension has no baseline: its menus are the ones the site reported.
        const extension = (raw?.extensions ?? []).find((ext) => ext.key === moduleKey);
        const moduleMenus = extension
            ? extension.menus.map((menu) => ({
                  menu_key: menu.id,
                  label: menu.label,
                  parent_key: menu.parentId,
                  order: menu.order,
                  enabled: true,
              }))
            : baselineMenus.filter((menu) => menu.module_key === moduleKey);

        function leafEnabled(menu) {
            return overrides.has(menu.menu_key) ? overrides.get(menu.menu_key) : menu.enabled;
        }

        return moduleMenus.map((menu) => {
            const children = moduleMenus.filter((candidate) => candidate.parent_key === menu.menu_key);
            const isParent = children.length > 0;

            return {
                menu_key: menu.menu_key,
                label: menu.label,
                parent_key: menu.parent_key,
                order: menu.order,
                configurable: !isParent,
                effectiveEnabled: isParent ? children.some((child) => leafEnabled(child)) : leafEnabled(menu),
            };
        });
    }

    function siteSummary(siteId) {
        const modules = effectiveSiteModules(siteId).filter((mod) => !mod.extension);
        const modulesEnabled = modules.filter((mod) => mod.siteEnabled).length;

        // Menu completeness counts across ALL baseline menus (TOTAL_MENUS)
        // regardless of whether their owning module is itself enabled
        // — module state and menu state are two independent
        // dimensions (see the STEP report), so this denominator stays
        // fixed for every site.
        let menusEnabled = 0;
        for (const mod of baselineModules) {
            menusEnabled += effectiveSiteMenus(siteId, mod.module_key).filter((menu) => menu.effectiveEnabled).length;
        }

        return {
            modulesEnabled,
            modulesTotal: TOTAL_MODULES,
            menusEnabled,
            menusTotal: TOTAL_MENUS,
            completeness: TOTAL_MENUS ? round1((menusEnabled / TOTAL_MENUS) * 100) : 0,
        };
    }

    const sitesWithSummary = computed(() =>
        sites.value.map((site) => ({ ...site, summary: siteSummary(site.siteId) }))
    );

    const selectedSite = computed(() => {
        if (!sitesWithSummary.value.length) return null;
        return sitesWithSummary.value.find((site) => site.siteCode === selectedSiteCode.value) ?? sitesWithSummary.value[0];
    });

    const selectedSiteModules = computed(() =>
        selectedSite.value ? effectiveSiteModules(selectedSite.value.siteId) : []
    );

    const selectedModule = computed(() => {
        if (!selectedSiteModules.value.length) return null;
        return selectedSiteModules.value.find((mod) => mod.module_key === selectedModuleKey.value) ?? selectedSiteModules.value[0];
    });

    const selectedModuleMenus = computed(() => {
        if (!selectedSite.value || !selectedModule.value) return [];
        return effectiveSiteMenus(selectedSite.value.siteId, selectedModule.value.module_key);
    });

    function selectSite(siteCode) {
        if (sitesWithSummary.value.some((site) => site.siteCode === siteCode)) {
            selectedSiteCode.value = siteCode;
            selectedModuleKey.value = null;
        }
    }

    function selectModule(moduleKey) {
        if (selectedSiteModules.value.some((mod) => mod.module_key === moduleKey)) {
            selectedModuleKey.value = moduleKey;
        }
    }

    async function toggleModule(moduleKey, enabled) {
        if (!selectedSite.value) return;
        saving.value = true;
        const { ok, data } = await siteConfigurationService.toggleModule(selectedSite.value.siteId, moduleKey, enabled);
        if (ok) {
            await load();
        } else {
            error.value = data?.message ?? 'Unable to update module configuration.';
        }
        saving.value = false;
    }

    // Never called for a non-configurable (parent) menu item — the UI
    // disables interaction on those; see SiteMenuConfigTree.vue.
    async function toggleMenu(menuKey, enabled) {
        if (!selectedSite.value || !selectedModule.value) return;
        saving.value = true;
        const moduleKey = selectedModule.value.module_key;
        const extensionMenu = (selectedSite.value.extensions ?? [])
            .find((ext) => ext.key === moduleKey)
            ?.menus.find((menu) => menu.id === menuKey);
        const menuLabel =
            baselineMenus.find((menu) => menu.module_key === moduleKey && menu.menu_key === menuKey)?.label ??
            extensionMenu?.label;
        const { ok, data } = await siteConfigurationService.toggleMenu(
            selectedSite.value.siteId,
            moduleKey,
            menuKey,
            enabled,
            menuLabel
        );
        if (ok) {
            await load();
        } else {
            error.value = data?.message ?? 'Unable to update menu configuration.';
        }
        saving.value = false;
    }

    onMounted(load);

    return {
        loading,
        error,
        saving,
        sites: sitesWithSummary,
        selectedSite,
        selectedSiteModules,
        selectedModule,
        selectedModuleMenus,
        selectSite,
        selectModule,
        toggleModule,
        toggleMenu,
        totalModules: TOTAL_MODULES,
        totalMenus: TOTAL_MENUS,
    };
}
