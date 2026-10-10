<?php

namespace App\Services;

use App\Models\ConfigurationSnapshot;
use App\Models\Site;
use App\Models\User;
use App\Support\BaselineKeys;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The ONLY place responsible for creating Configuration Snapshots. Uses
 * App\Support\BaselineKeys (a structural mirror of the Master Baseline
 * Registry's keys/hierarchy — see that class's doc comment for why)
 * combined with the site's current site_module_configs/
 * site_menu_configs overrides to resolve EFFECTIVE state, exactly
 * mirroring the rules in resources/js/composables/useSiteConfiguration.js
 * (not reimplemented differently — same rules, same defaults):
 *   - a baseline module/menu is enabled unless explicitly overridden
 *   - a leaf menu's effective state comes from its own override (or the
 *     baseline default if none exists)
 *   - a parent menu's effective state is DERIVED: enabled if at least
 *     one child is effectively enabled, disabled otherwise — it is
 *     never independently overridden
 */
class ConfigurationSnapshotService
{
    private const BASELINE_DEFAULT_ENABLED = true;

    public function createSnapshot(Site $site, ?User $createdBy): ConfigurationSnapshot
    {
        return DB::transaction(function () use ($site, $createdBy) {
            $moduleOverrides = $site->moduleConfigs()->pluck('enabled', 'module_key');
            $menuOverrides = $site->menuConfigs()->get()
                ->keyBy(fn ($config) => $config->module_key.'|'.$config->menu_key)
                ->map(fn ($config) => (bool) $config->enabled);

            $effectiveModules = $this->resolveEffectiveModules($moduleOverrides);
            $effectiveMenus = $this->resolveEffectiveMenus($menuOverrides);

            $nextNumber = (int) ($site->configurationSnapshots()->max('snapshot_number') ?? 0) + 1;

            $snapshot = ConfigurationSnapshot::create([
                'uuid' => (string) Str::uuid(),
                'site_id' => $site->id,
                'snapshot_number' => $nextNumber,
                // No formally established baseline release version
                // exists yet — deliberately left null. See the STEP
                // 3R-1 report; Release Version is a future step.
                'source_baseline_version' => null,
                'module_count' => count(array_filter($effectiveModules)),
                'menu_count' => count(array_filter($effectiveMenus, fn ($menu) => $menu['enabled'])),
                'status' => 'immutable',
                'created_by' => $createdBy?->id,
            ]);

            foreach ($effectiveModules as $moduleKey => $enabled) {
                $snapshot->modules()->create([
                    'module_key' => $moduleKey,
                    'enabled' => $enabled,
                ]);
            }

            foreach ($effectiveMenus as $menuKey => $menu) {
                $snapshot->menus()->create([
                    'module_key' => $menu['module_key'],
                    'menu_key' => $menuKey,
                    'enabled' => $menu['enabled'],
                ]);
            }

            return $snapshot->fresh(['modules', 'menus', 'site', 'createdBy']);
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<string, bool>  $moduleOverrides
     * @return array<string, bool>
     */
    private function resolveEffectiveModules($moduleOverrides): array
    {
        $effective = [];
        foreach (BaselineKeys::MODULE_KEYS as $moduleKey) {
            $effective[$moduleKey] = $moduleOverrides->has($moduleKey)
                ? (bool) $moduleOverrides->get($moduleKey)
                : self::BASELINE_DEFAULT_ENABLED;
        }

        return $effective;
    }

    /**
     * @param  \Illuminate\Support\Collection<string, bool>  $menuOverrides  keyed by "module_key|menu_key"
     * @return array<string, array{module_key: string, enabled: bool}>
     */
    private function resolveEffectiveMenus($menuOverrides): array
    {
        $effective = [];

        // Leaves first — parents are derived from these.
        foreach (BaselineKeys::MENUS as $menu) {
            if (! BaselineKeys::isLeaf($menu['module_key'], $menu['menu_key'])) {
                continue;
            }
            $key = $menu['module_key'].'|'.$menu['menu_key'];
            $effective[$menu['menu_key']] = [
                'module_key' => $menu['module_key'],
                'enabled' => $menuOverrides->has($key) ? (bool) $menuOverrides->get($key) : self::BASELINE_DEFAULT_ENABLED,
            ];
        }

        foreach (BaselineKeys::MENUS as $menu) {
            if (BaselineKeys::isLeaf($menu['module_key'], $menu['menu_key'])) {
                continue;
            }
            $children = BaselineKeys::childrenOf($menu['module_key'], $menu['menu_key']);
            $anyChildEnabled = collect($children)->contains(
                fn (array $child) => $effective[$child['menu_key']]['enabled'] ?? false
            );
            $effective[$menu['menu_key']] = [
                'module_key' => $menu['module_key'],
                'enabled' => $anyChildEnabled,
            ];
        }

        return $effective;
    }
}
