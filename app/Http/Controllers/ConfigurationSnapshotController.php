<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConfigurationSnapshotResource;
use App\Models\ConfigurationSnapshot;
use App\Models\Site;
use App\Services\AuditLogger;
use App\Services\ConfigurationSnapshotService;
use Illuminate\Http\Request;

/**
 * Read + create only — deliberately no update/delete routes exist
 * anywhere for this resource (see routes/web.php). A snapshot is
 * immutable by construction: the only way to reflect a new
 * configuration state is to create another one.
 */
class ConfigurationSnapshotController extends Controller
{
    public function __construct(
        private ConfigurationSnapshotService $snapshotService,
        private AuditLogger $auditLogger,
    ) {
    }

    public function index(Site $site)
    {
        $snapshots = $site->configurationSnapshots()->orderByDesc('snapshot_number')->get();

        return ConfigurationSnapshotResource::collection($snapshots);
    }

    public function store(Request $request, Site $site)
    {
        $snapshot = $this->snapshotService->createSnapshot($site, $request->user());

        // Metadata deliberately excludes the module/menu payload — the
        // snapshot's own child rows already hold that; the audit log
        // only needs to record that the event happened and its summary
        // counts.
        $this->auditLogger->log(
            action: 'configuration_snapshot_created',
            description: "Configuration snapshot #{$snapshot->snapshot_number} created for site {$site->code}",
            metadata: [
                'site_code' => $site->code,
                'snapshot_id' => $snapshot->uuid,
                'snapshot_number' => $snapshot->snapshot_number,
                'module_count' => $snapshot->module_count,
                'menu_count' => $snapshot->menu_count,
            ],
            entityType: 'ConfigurationSnapshot',
            entityId: $snapshot->id,
        );

        return (new ConfigurationSnapshotResource($snapshot))->response()->setStatusCode(201);
    }

    public function show(Site $site, ConfigurationSnapshot $configurationSnapshot)
    {
        $configurationSnapshot->load(['modules', 'menus', 'site', 'createdBy']);

        return new ConfigurationSnapshotResource($configurationSnapshot);
    }
}
