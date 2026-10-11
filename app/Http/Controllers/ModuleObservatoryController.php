<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

/**
 * Per-site version and menu manifest of one module, as reported by each
 * connected OneClick site (GET /api/cc/menus). The reference baseline
 * lives in the frontend; this only returns what the sites actually run,
 * limited to the caller's effective site access.
 */
class ModuleObservatoryController extends Controller
{
    public function show(Request $request, string $moduleKey)
    {
        $allowedIds = collect($request->user()->effectiveSiteAccess()['sites'])->pluck('id');

        $sites = Site::whereIn('id', $allowedIds)->whereNotNull('menus')->orderBy('name')->get()
            ->filter(fn (Site $site) => isset($site->menus[$moduleKey]))
            ->map(fn (Site $site) => [
                'code' => $site->code,
                'version' => $site->menus[$moduleKey]['version'] ?? null,
                'menus' => $site->menus[$moduleKey]['menus'] ?? null,
            ])
            ->values();

        return ['data' => [
            'moduleKey' => $moduleKey,
            'lastSyncedAt' => Site::whereIn('id', $allowedIds)->max('last_checked_at'),
            'sites' => $sites,
        ]];
    }
}
