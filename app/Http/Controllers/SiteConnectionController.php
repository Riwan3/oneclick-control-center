<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SiteConnectionService;
use Illuminate\Http\Request;

/**
 * Live telemetry of connected OneClick sites, as last pulled by
 * sites:check. The list is light (one row per site); show() adds the
 * server-health breakdown and recent activity. Both are limited to the
 * caller's effective site access. The api_token is never exposed.
 */
class SiteConnectionController extends Controller
{
    public function index(Request $request)
    {
        $allowedIds = $this->allowedSiteIds($request);

        // base_url is only needed by the super admin's site editor.
        $isAdmin = $request->user()->role === 'super_admin';

        return ['data' => Site::whereIn('id', $allowedIds)->orderBy('name')->get()
            ->map(fn (Site $site) => $this->present($site) + ($isAdmin ? ['base_url' => $site->base_url] : []))];
    }

    public function show(Request $request, Site $site)
    {
        abort_unless($this->allowedSiteIds($request)->contains($site->id), 404);

        return ['data' => $this->present($site) + [
            'health' => $site->status === 'offline' ? null : $site->health,
            'recent_activity' => $site->activity['recent'] ?? [],
        ]];
    }

    public function check(Site $site, SiteConnectionService $connections)
    {
        return ['data' => $this->present($connections->check($site))];
    }

    private function allowedSiteIds(Request $request)
    {
        return collect($request->user()->effectiveSiteAccess()['sites'])->pluck('id');
    }

    private function present(Site $site): array
    {
        $reachable = $site->status !== 'offline';

        return [
            'id' => $site->id,
            'code' => $site->code,
            'name' => $site->name,
            'location' => $site->location,
            'connected' => $site->isConnectable(),
            // pull: we call the site; push: the site reports to us; none: no token yet.
            'mode' => $site->canPull() ? 'pull' : ($site->isConnectable() ? 'push' : 'none'),
            'status' => $site->status,
            'version' => $site->version,
            'environment' => $site->environment,
            'deployed_at' => $site->deployed_at?->toIso8601String(),
            'users' => $site->users_count,
            'modules' => $site->modules ?? [],
            'health_score' => $reachable ? ($site->health['score'] ?? null) : null,
            'active_users_today' => $reachable ? ($site->activity['active_users_today'] ?? null) : null,
            'daily_activity' => $site->activity['daily'] ?? [],
            // What the site says about applying our configuration (null until it reports).
            'config_sync' => $site->config_sync,
            'last_checked_at' => $site->last_checked_at?->toIso8601String(),
            'last_error' => $site->last_error,
        ];
    }
}
