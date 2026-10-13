<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Pulls telemetry from a OneClick site (GET /api/cc/status, then
 * /activity and /menus) and stores it on the site row. Never throws:
 * failures become status 'offline' (unreachable / bad response) with a
 * short last_error. /activity and /menus are best-effort — if either
 * fails the previous values are kept. The token is never logged or
 * included in error messages.
 */
class SiteConnectionService
{
    public function check(Site $site): Site
    {
        if (! $site->canPull()) {
            return $site;
        }

        try {
            $client = $this->client($site);
            $response = $client->get('/api/cc/status');

            if (! $response->successful()) {
                return $this->recordFailure($site, 'HTTP '.$response->status());
            }

            $site = $this->recordStatus($site, (array) $response->json('data'));
            $this->recordOptional($site, $client);

            return $site;
        } catch (Throwable $e) {
            return $this->recordFailure($site, 'Tidak dapat terhubung ke site.');
        }
    }

    /** Push mode: store a report the site sent us (same storage as a pull). */
    public function recordReport(Site $site, array $status, ?array $activity, ?array $menus): Site
    {
        $site = $this->recordStatus($site, $status);

        $site->forceFill([
            'activity' => $activity ?? $site->activity,
            'menus' => $menus ?? $site->menus,
        ])->save();

        return $site;
    }

    /** Push mode: a site that has reported before but went quiet is Offline. */
    public function flagIfSilent(Site $site): Site
    {
        $staleAfter = (int) config('services.site_connection.stale_after_minutes', 15);

        if ($site->last_checked_at && $site->status !== 'offline' && $site->last_checked_at->lt(now()->subMinutes($staleAfter))) {
            $site->forceFill([
                'status' => 'offline',
                'last_error' => "Tidak ada laporan dari site dalam {$staleAfter} menit.",
            ])->save();
        }

        return $site;
    }

    private function client(Site $site): PendingRequest
    {
        return Http::baseUrl(rtrim($site->base_url, '/'))
            ->withToken($site->api_token)
            ->acceptJson()
            ->timeout((int) config('services.site_connection.timeout', 10));
    }

    private function recordStatus(Site $site, array $data): Site
    {
        $site->forceFill([
            'status' => in_array($data['status'] ?? null, ['online', 'warning'], true) ? $data['status'] : 'warning',
            'version' => $data['version'] ?? null,
            'environment' => $data['environment'] ?? null,
            'deployed_at' => $data['deployed_at'] ?? null,
            'users_count' => $data['users'] ?? null,
            'modules' => $data['modules'] ?? [],
            'health' => $data['health'] ?? null,
            'config_sync' => $data['config_sync'] ?? null,
            'last_checked_at' => now(),
            'last_error' => null,
        ])->save();

        return $site;
    }

    private function recordOptional(Site $site, PendingRequest $client): void
    {
        try {
            $activity = $client->get('/api/cc/activity');
            $menus = $client->get('/api/cc/menus');

            $site->forceFill([
                'activity' => $activity->successful() ? $activity->json('data') : $site->activity,
                'menus' => $menus->successful() ? $menus->json('data.modules') : $site->menus,
            ])->save();
        } catch (Throwable) {
            // Keep whatever telemetry we had; /status already succeeded.
        }
    }

    private function recordFailure(Site $site, string $error): Site
    {
        $site->forceFill([
            'status' => 'offline',
            'last_checked_at' => now(),
            'last_error' => $error,
        ])->save();

        return $site;
    }
}
