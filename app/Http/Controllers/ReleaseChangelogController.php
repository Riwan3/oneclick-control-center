<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReleaseChangelogRequest;
use App\Http\Requests\UpdateReleaseChangelogRequest;
use App\Http\Resources\ReleaseChangelogResource;
use App\Models\Release;
use App\Models\ReleaseChangelog;
use App\Services\AuditLogger;

/**
 * Changelog entries are always managed in the context of their parent
 * Release (nested routes — see routes/web.php) — there is no
 * standalone changelog listing endpoint; ReleaseController::show()
 * already returns a release's full changelog list.
 */
class ReleaseChangelogController extends Controller
{
    public function __construct(private AuditLogger $auditLogger)
    {
    }

    public function store(StoreReleaseChangelogRequest $request, Release $release)
    {
        $changelog = $release->changelogs()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $this->auditLogger->log(
            action: 'changelog_created',
            description: "Changelog entry added to release {$release->version}",
            metadata: [
                'release_version' => $release->version,
                'category' => $changelog->category,
                'title' => $changelog->title,
            ],
            entityType: 'ReleaseChangelog',
            entityId: $changelog->id,
        );

        return (new ReleaseChangelogResource($changelog->load('createdBy')))->response()->setStatusCode(201);
    }

    public function update(UpdateReleaseChangelogRequest $request, Release $release, ReleaseChangelog $changelog)
    {
        $changelog->fill($request->validated());
        $changelog->save();

        $this->auditLogger->log(
            action: 'changelog_updated',
            description: "Changelog entry updated on release {$release->version}",
            metadata: [
                'release_version' => $release->version,
                'changelog_id' => $changelog->id,
                'category' => $changelog->category,
                'title' => $changelog->title,
            ],
            entityType: 'ReleaseChangelog',
            entityId: $changelog->id,
        );

        return new ReleaseChangelogResource($changelog->load('createdBy'));
    }

    public function destroy(Release $release, ReleaseChangelog $changelog)
    {
        $category = $changelog->category;
        $title = $changelog->title;
        $changelogId = $changelog->id;

        $changelog->delete();

        $this->auditLogger->log(
            action: 'changelog_deleted',
            description: "Changelog entry removed from release {$release->version}",
            metadata: [
                'release_version' => $release->version,
                'category' => $category,
                'title' => $title,
            ],
            entityType: 'ReleaseChangelog',
            entityId: $changelogId,
        );

        return response()->noContent();
    }
}
