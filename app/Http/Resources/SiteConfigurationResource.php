<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Raw per-site override rows only — never the baseline itself (which
 * this backend does not hold; see the site_module_configs migration).
 * The frontend combines this with its own Master Baseline Registry
 * data to compute effective module/menu state — see
 * composables/useSiteConfiguration.js.
 */
class SiteConfigurationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'siteId' => $this->id,
            'siteCode' => $this->code,
            'siteName' => $this->name,
            'moduleOverrides' => $this->moduleConfigs->map(fn ($config) => [
                'moduleKey' => $config->module_key,
                'enabled' => $config->enabled,
            ])->values()->all(),
            'menuOverrides' => $this->menuConfigs->map(fn ($config) => [
                'moduleKey' => $config->module_key,
                'menuKey' => $config->menu_key,
                'enabled' => $config->enabled,
            ])->values()->all(),
        ];
    }
}
