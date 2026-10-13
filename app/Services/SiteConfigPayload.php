<?php

namespace App\Services;

use App\Models\Release;
use App\Models\Site;

/**
 * The configuration a OneClick site receives back from the Control Center
 * (in the response to its report): which modules and menus are disabled
 * for it by Site Configuration, plus the target release version.
 *
 * Only DELTAS are sent — everything not listed is enabled, matching the
 * sparse-override model. Module keys are translated to the keys the
 * OneClick site uses. Menus are identified by LABEL, because the baseline
 * and each site name the same menu with different keys; an override made
 * before labels were recorded cannot be matched and is counted in
 * `unmapped_menus` instead of being guessed.
 */
class SiteConfigPayload
{
    // Master Baseline Registry module key -> the key the OneClick site uses.
    public const MODULE_TO_SITE = [
        'one-core-value' => 'one-core',
        'one-procedur' => 'one-procedure',
    ];

    /** @return array<string, mixed> */
    public function forSite(Site $site): array
    {
        $disabledMenus = [];
        $unmapped = 0;

        foreach ($site->menuConfigs()->where('enabled', false)->get() as $menu) {
            if (blank($menu->menu_label)) {
                $unmapped++;

                continue;
            }

            $disabledMenus[$this->siteModuleKey($menu->module_key)][] = $menu->menu_label;
        }

        return [
            'disabled_modules' => $site->moduleConfigs()->where('enabled', false)->pluck('module_key')
                ->map(fn (string $key) => $this->siteModuleKey($key))->values()->all(),
            'disabled_menus' => $disabledMenus,
            'unmapped_menus' => $unmapped,
            'target_version' => $this->targetVersion(),
            'issued_at' => now()->toIso8601String(),
        ];
    }

    private function siteModuleKey(string $key): string
    {
        return self::MODULE_TO_SITE[$key] ?? $key;
    }

    private function targetVersion(): ?string
    {
        return Release::where('status', 'released')->pluck('version')
            ->reduce(fn (?string $max, string $version) => $max === null
                || version_compare(ltrim($version, 'vV'), ltrim($max, 'vV'), '>') ? $version : $max);
    }
}
