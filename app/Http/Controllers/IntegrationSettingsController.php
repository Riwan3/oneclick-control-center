<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

/**
 * Read-only description of how OneClick sites connect to this Control Center,
 * for API Integration > API Configuration: where a site reports to, how it
 * authenticates, the timing rules, and the live state of the connections.
 * Nothing here is configurable from the UI — the values come from the same
 * configuration and constants the rest of the code applies (never a copy).
 * Super admin only. The token itself is never exposed.
 */
class IntegrationSettingsController extends Controller
{
    public function __invoke(Request $request)
    {
        $sites = Site::all();
        $connected = $sites->filter(fn (Site $site) => $site->isConnectable());

        return ['data' => [
            'endpoint' => [
                'url' => $request->getSchemeAndHttpHost(),
                'report_path' => '/api/cc/report',
                'method' => 'POST',
                'rate_limit_per_minute' => SiteReportController::RATE_LIMIT_PER_MINUTE,
                'max_payload_kb' => intdiv(SiteReportController::MAX_PAYLOAD_BYTES, 1024),
            ],
            'authentication' => [
                'method' => 'Bearer token, one per site',
                'site_header' => 'X-Site-Code',
                'token_storage' => 'Encrypted; shown once when issued',
            ],
            'timing' => [
                'pull_timeout_seconds' => (int) config('services.site_connection.timeout'),
                'offline_after_minutes' => (int) config('services.site_connection.stale_after_minutes'),
            ],
            'sites' => [
                'registered' => $sites->count(),
                'connected' => $connected->count(),
                'push' => $connected->filter(fn (Site $site) => ! $site->canPull())->count(),
                'pull' => $connected->filter(fn (Site $site) => $site->canPull())->count(),
                'online' => $connected->where('status', 'online')->count(),
                'warning' => $connected->where('status', 'warning')->count(),
                'offline' => $connected->where('status', 'offline')->count(),
                'awaiting_first_report' => $connected->where('status', 'unknown')->count(),
            ],
            'last_report_at' => $connected->max('last_checked_at')?->toIso8601String(),
        ]];
    }
}
