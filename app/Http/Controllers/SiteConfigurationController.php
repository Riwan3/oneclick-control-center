<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToggleSiteMenuConfigRequest;
use App\Http\Requests\ToggleSiteModuleConfigRequest;
use App\Http\Resources\SiteConfigurationResource;
use App\Models\Site;
use App\Services\AuditLogger;

/**
 * Site Configuration Foundation: persists per-site DELTAS against the
 * Master Baseline Registry (module/menu enabled overrides) — never the
 * baseline itself, which is not duplicated here (see the migrations'
 * doc comments). Independent from user site/module ACCESS
 * (user_site_access / user_module_access) — this controls what a site
 * releases, not who may access the Control Center for it.
 *
 * SPARSE OVERRIDE MODEL (hardening pass): every baseline module/menu
 * defaults to enabled. An override row is persisted ONLY when the
 * requested state deviates from that default; a request that matches
 * the default deletes any existing row instead of storing a redundant
 * "enabled=true" row. This keeps the override tables containing only
 * genuine deviations, not a full mirror of the baseline. A true no-op
 * (requested state already equals the current effective state) performs
 * no database write and logs no audit event at all — see toggleModule()/
 * toggleMenu() below for the three-way branch (no-op / delete-to-revert
 * / upsert-to-deviate) this implements.
 *
 * LOCKED BASELINE: the baseline itself is read-only — there is no endpoint
 * that edits it. The requests (ToggleSite*ConfigRequest) only accept a
 * module/menu that exists in the baseline (App\Support\BaselineKeys, the
 * backend mirror of the frontend registry) — or in an EXTENSION, a module
 * outside the baseline that this very site reported
 * (App\Services\SiteExtensions) — and only LEAF menus: a parent menu's
 * effective state is derived from its children, so it is never stored.
 * Anything else is rejected with 422 before reaching this class.
 */
class SiteConfigurationController extends Controller
{
    // Every baseline module/menu is enabled by definition in the Master
    // Baseline Registry — this mirrors only that single boolean, not
    // the registry itself.
    private const BASELINE_DEFAULT_ENABLED = true;

    public function __construct(private AuditLogger $auditLogger)
    {
    }

    public function index()
    {
        $sites = Site::with(['moduleConfigs', 'menuConfigs'])->orderBy('name')->get();

        return SiteConfigurationResource::collection($sites);
    }

    public function toggleModule(ToggleSiteModuleConfigRequest $request, Site $site)
    {
        $moduleKey = $request->validated('module_key');
        $enabled = $request->validated('enabled');

        $existing = $site->moduleConfigs()->where('module_key', $moduleKey)->first();
        $previousState = $existing?->enabled ?? self::BASELINE_DEFAULT_ENABLED;

        if ($previousState === $enabled) {
            // No-op: current effective state already matches the
            // request. No database transition, no audit event.
            return response()->noContent();
        }

        if ($enabled === self::BASELINE_DEFAULT_ENABLED) {
            // Reverting to the baseline default — remove the override
            // entirely rather than storing a redundant matching row.
            $existing?->delete();
        } else {
            $site->moduleConfigs()->updateOrCreate(
                ['module_key' => $moduleKey],
                ['enabled' => $enabled]
            );
        }

        $this->auditLogger->log(
            action: $enabled ? 'site_module_enabled' : 'site_module_disabled',
            description: "Module {$moduleKey} ".($enabled ? 'enabled' : 'disabled')." for site {$site->code}",
            metadata: [
                'site_code' => $site->code,
                'module_key' => $moduleKey,
                'previous_state' => $previousState,
                'new_state' => $enabled,
            ],
            entityType: 'SiteModuleConfig',
            entityId: $site->id,
        );

        return response()->noContent();
    }

    public function toggleMenu(ToggleSiteMenuConfigRequest $request, Site $site)
    {
        $moduleKey = $request->validated('module_key');
        $menuKey = $request->validated('menu_key');
        $enabled = $request->validated('enabled');

        $existing = $site->menuConfigs()
            ->where('module_key', $moduleKey)
            ->where('menu_key', $menuKey)
            ->first();
        $previousState = $existing?->enabled ?? self::BASELINE_DEFAULT_ENABLED;

        if ($previousState === $enabled) {
            return response()->noContent();
        }

        if ($enabled === self::BASELINE_DEFAULT_ENABLED) {
            $existing?->delete();
        } else {
            $site->menuConfigs()->updateOrCreate(
                ['module_key' => $moduleKey, 'menu_key' => $menuKey],
                ['enabled' => $enabled, 'menu_label' => $request->validated('menu_label')]
            );
        }

        $this->auditLogger->log(
            action: $enabled ? 'site_menu_enabled' : 'site_menu_disabled',
            description: "Menu {$menuKey} ".($enabled ? 'enabled' : 'disabled')." for site {$site->code}",
            metadata: [
                'site_code' => $site->code,
                'module_key' => $moduleKey,
                'menu_key' => $menuKey,
                'previous_state' => $previousState,
                'new_state' => $enabled,
            ],
            entityType: 'SiteMenuConfig',
            entityId: $site->id,
        );

        return response()->noContent();
    }
}
