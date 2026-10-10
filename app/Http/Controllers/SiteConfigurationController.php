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
 * PARENT MENU SAFETY (hardening pass): this controller does not — and,
 * without duplicating the baseline's parent/child hierarchy, cannot —
 * know whether a given menu_key is a parent or a leaf. It intentionally
 * does not reject a parent key with 422 (Task 4/5 decision: doing so
 * safely would require mirroring the hierarchy server-side, a second
 * source of truth). Instead this relies on the frontend's effective
 * resolver (useSiteConfiguration.js) being — by construction — the only
 * place effective visibility is computed, and that resolver derives a
 * parent's effective state purely from its children, never reading any
 * override row keyed to the parent itself. A stray override row for a
 * parent key is therefore always inert: written, but never observed.
 * See the STEP report for the full reasoning.
 *
 * UNKNOWN KEY SAFETY: the same inertness argument applies to any
 * syntactically valid but non-baseline module_key/menu_key — the
 * frontend resolver only ever iterates the real baseline's keys when
 * computing effective state, so an override row for a key outside that
 * set can never affect what any site actually shows.
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
                ['enabled' => $enabled]
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
