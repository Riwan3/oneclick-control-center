<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Super-admin management of the site registry and each site's API token
 * (the credential a OneClick site uses to talk to the Control Center).
 * A token is shown exactly once, when it is issued; it is stored
 * encrypted and never returned by any other endpoint. Every change is
 * written to the audit log (never including the token).
 */
class SiteManagementController extends Controller
{
    public function __construct(private AuditLogger $auditLogger)
    {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $data['code'] = strtoupper($data['code']);
        abort_if(Site::where('code', $data['code'])->exists(), 422, 'Site code already exists.');

        $site = Site::create($data);

        $this->auditLogger->log(
            action: 'site_created',
            description: "Created site {$site->code}",
            metadata: ['code' => $site->code, 'name' => $site->name],
            entityType: 'Site',
            entityId: $site->id,
        );

        return response()->json(['data' => ['id' => $site->id, 'code' => $site->code]], 201);
    }

    public function update(Request $request, Site $site)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            // Pull mode only: leave empty for push mode (the site reports to us).
            'base_url' => ['nullable', 'url:https,http', 'max:255'],
        ]);

        $site->fill(['name' => $data['name'], 'location' => $data['location'] ?? null]);
        $site->forceFill(['base_url' => filled($data['base_url'] ?? null) ? rtrim($data['base_url'], '/') : null])->save();

        $this->auditLogger->log(
            action: 'site_updated',
            description: "Updated site {$site->code}",
            metadata: ['code' => $site->code, 'mode' => $site->canPull() ? 'pull' : 'push'],
            entityType: 'Site',
            entityId: $site->id,
        );

        return response()->noContent();
    }

    public function destroy(Site $site)
    {
        $code = $site->code;
        $id = $site->id;
        $site->delete();

        $this->auditLogger->log(
            action: 'site_deleted',
            description: "Deleted site {$code}",
            metadata: ['code' => $code],
            entityType: 'Site',
            entityId: $id,
        );

        return response()->noContent();
    }

    /**
     * Issues (or rotates) the site's token. The previous token stops
     * working immediately. Returned once, together with the exact .env
     * lines the site needs.
     */
    public function issueToken(Request $request, Site $site)
    {
        $token = bin2hex(random_bytes(32));

        $site->forceFill([
            'api_token' => $token,
            'status' => 'unknown',
            'last_error' => null,
        ])->save();

        $this->auditLogger->log(
            action: 'site_token_issued',
            description: "Issued API token for site {$site->code}",
            metadata: ['code' => $site->code],
            entityType: 'Site',
            entityId: $site->id,
        );

        return response()->json(['data' => [
            'token' => $token,
            'env' => [
                'CONTROL_CENTER_TOKEN' => $token,
                'CONTROL_CENTER_URL' => $request->getSchemeAndHttpHost(),
                'CONTROL_CENTER_SITE_CODE' => $site->code,
            ],
        ]]);
    }
}
