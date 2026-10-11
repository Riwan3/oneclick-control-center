<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\SiteConnectionService;
use Illuminate\Console\Command;

class CheckSites extends Command
{
    protected $signature = 'sites:check';

    protected $description = 'Pull status from connected sites and flag push-mode sites that stopped reporting';

    public function handle(SiteConnectionService $connections): int
    {
        Site::whereNotNull('api_token')->get()->each(function (Site $site) use ($connections) {
            $site = $site->canPull() ? $connections->check($site) : $connections->flagIfSilent($site);
            $this->line("{$site->code}: {$site->status}");
        });

        return self::SUCCESS;
    }
}
