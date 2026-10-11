<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\SiteConnectionService;
use Illuminate\Console\Command;

class ConnectSite extends Command
{
    protected $signature = 'sites:connect {code : Site code, e.g. BIB} {base_url : e.g. https://oneclickppa-test.my.id}';

    protected $description = 'Store a site\'s URL and API token (prompted, hidden) and test the connection';

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

        $site->forceFill(['base_url' => rtrim($this->argument('base_url'), '/'), 'api_token' => $token])->save();
        $site = $connections->check($site);

        $this->info("{$site->code}: {$site->status}".($site->last_error ? " ({$site->last_error})" : " v{$site->version}, {$site->users_count} users"));

        return $site->status === 'offline' ? self::FAILURE : self::SUCCESS;
    }
}
