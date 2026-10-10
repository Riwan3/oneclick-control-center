<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReleaseRequest;
use App\Http\Requests\UpdateReleaseRequest;
use App\Http\Resources\ReleaseResource;
use App\Models\Release;
use App\Services\AuditLogger;

/**
 * Release Version Foundation: a formal, independent Control Center
 * Release catalog. Not connected to the Master Baseline's own version,
 * per-module versions, the Deployments page's per-site platform
 * version, or Configuration Snapshot — see the create_releases_table
 * migration's doc comment.
 *
 * Read (index/show) is available to super_admin and management,
 * matching the existing version_view capability (also used by the
 * Deployments page). Write (store/update) is restricted to super_admin
 * only, via the new release_manage capability — see routes/web.php.
 */
class ReleaseController extends Controller
{
    public function __construct(private AuditLogger $auditLogger)
    {
    }

    public function index()
    {
        $releases = Release::with('createdBy')->withCount('changelogs')->orderByDesc('created_at')->get();

        return ReleaseResource::collection($releases);
    }

    public function show(Release $release)
    {
        $release->load(['createdBy', 'changelogs.createdBy']);

        return new ReleaseResource($release);
    }

    public function store(StoreReleaseRequest $request)
    {
        $release = Release::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $this->auditLogger->log(
            action: 'release_created',
            description: "Release {$release->version} created",
            metadata: [
                'version' => $release->version,
                'title' => $release->title,
                'status' => $release->status,
            ],
            entityType: 'Release',
            entityId: $release->id,
        );

        return (new ReleaseResource($release->load('createdBy')->loadCount('changelogs')))->response()->setStatusCode(201);
    }

    public function update(UpdateReleaseRequest $request, Release $release)
    {
        // Captured before mutating — read via Eloquent's own dirty
        // tracking (wasChanged()) after save() rather than manually
        // diffing attribute arrays: release_date is cast to a Carbon
        // instance, and comparing two Carbon objects with !== compares
        // object identity, not the date value, which would false
        // positive on every save. wasChanged() compares the actual
        // underlying values correctly.
        $originalStatus = $release->status;

        $release->fill($request->validated());
        $release->save();

        $contentChanged = $release->wasChanged(['version', 'title', 'release_date', 'description']);
        $statusChanged = $release->wasChanged('status');

        if ($contentChanged) {
            $this->auditLogger->log(
                action: 'release_updated',
                description: "Release {$release->version} updated",
                metadata: ['version' => $release->version],
                entityType: 'Release',
                entityId: $release->id,
            );
        }

        if ($statusChanged) {
            $this->auditLogger->log(
                action: 'release_status_changed',
                description: "Release {$release->version} status changed from {$originalStatus} to {$release->status}",
                metadata: [
                    'version' => $release->version,
                    'previous_status' => $originalStatus,
                    'new_status' => $release->status,
                ],
                entityType: 'Release',
                entityId: $release->id,
            );
        }

        return new ReleaseResource($release->load('createdBy')->loadCount('changelogs'));
    }
}
