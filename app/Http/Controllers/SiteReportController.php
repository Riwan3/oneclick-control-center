<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SiteConnectionService;
use Illuminate\Http\Request;

/**
 * Push mode: a OneClick site reports its own status (cc:push on the
 * site). Used when the Control Center cannot pull from the site (e.g. a
 * firewall in between). The site is identified by X-Site-Code and
 * authenticated with the same per-site token used for pull mode,
 * compared in constant time. Unknown site and wrong token get the same
 * 401 so site codes cannot be enumerated.
 */
class SiteReportController extends Controller
{
    private const MAX_PAYLOAD_BYTES = 512 * 1024;

    public function store(Request $request, SiteConnectionService $connections)
    {
        $site = Site::where('code', strtoupper((string) $request->header('X-Site-Code')))->first();

        abort_unless(
            $site && filled($site->api_token) && hash_equals($site->api_token, (string) $request->bearerToken()),
            401,
            'Unauthorized.'
        );
        abort_if(strlen($request->getContent()) > self::MAX_PAYLOAD_BYTES, 413, 'Payload too large.');

        $data = $request->validate([
            'status' => ['required', 'array'],
            'status.status' => ['required', 'in:online,warning'],
            'status.version' => ['nullable', 'string', 'max:50'],
            'status.environment' => ['nullable', 'string', 'max:30'],
            'status.deployed_at' => ['nullable', 'date'],
            'status.users' => ['nullable', 'integer', 'min:0'],
            'status.modules' => ['nullable', 'array'],
            'status.health' => ['nullable', 'array'],
            'activity' => ['nullable', 'array'],
            'menus' => ['nullable', 'array'],
        ]);

        $connections->recordReport($site, $data['status'], $data['activity'] ?? null, $data['menus']['modules'] ?? null);

        return response()->noContent();
    }
}
