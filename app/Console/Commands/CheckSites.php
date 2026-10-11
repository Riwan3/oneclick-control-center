<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\SiteConnectionService;
use Illuminate\Console\Command;

class CheckSites extends Command
{
    protected $signature = 'sites:check';

    protected $description = 'Pull status from every connected OneClick site';

    public function handle(SiteConnectionService $connections): int
    {
        Site::whereNotNull('base_url')->whereNotNull('api_token')->get()
            ->each(function (Site $site) use ($connections) {
                $site = $connections->check($site);
                $this->line("{$site->code}: {$site->status}");
            });

        return self::SUCCESS;
    }
}
