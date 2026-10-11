<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\SiteConnectionService;
use Illuminate\Console\Command;

class ConnectSite extends Command
{
    protected $signature = 'sites:connect {code : Site code, e.g. BIB} {base_url? : Site URL for pull mode, e.g. https://oneclickppa-test.my.id. Omit for push mode (the site reports to us)}';

    protected $description = 'Store a site API token (prompted, hidden) and, in pull mode, its URL, then test the connection';

    public function handle(SiteConnectionService $connections): int
    {
        $site = Site::where('code', strtoupper($this->argument('code')))->first();

        if (! $site) {
            $this->error('Site not found.');

            return self::FAILURE;
        }

        $token = $this->secret('CONTROL_CENTER_TOKEN of that site');

        if (! $token) {
            $this->error('Token is required.');

            return self::FAILURE;
        }

        $baseUrl = $this->argument('base_url');
        $site->forceFill(['base_url' => $baseUrl ? rtrim($baseUrl, '/') : null, 'api_token' => $token])->save();

        if (! $site->canPull()) {
            $this->info("{$site->code}: push mode. Waiting for the site to report (run `php artisan cc:push` on the site).");

            return self::SUCCESS;
        }

        $site = $connections->check($site);
        $this->info("{$site->code}: {$site->status}".($site->last_error ? " ({$site->last_error})" : " v{$site->version}, {$site->users_count} users"));

        return $site->status === 'offline' ? self::FAILURE : self::SUCCESS;
    }
}
