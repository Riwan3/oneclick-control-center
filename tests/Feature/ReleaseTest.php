<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Release;
use App\Models\ReleaseChangelog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STEP 3R-2 — RELEASE VERSION + CHANGELOG FOUNDATION.
 *
 * Uses temporary in-memory sqlite records (RefreshDatabase) — never
 * touches real application users.
 */
class ReleaseTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
            'site_access_scope' => 'all',
            'module_access_scope' => 'all',
        ]);
    }

    private function management(): User
    {
        return User::factory()->create([
            'role' => 'management',
            'site_access_scope' => 'selected',
            'module_access_scope' => 'selected',
        ]);
    }

    private function viewer(): User
    {
        return User::factory()->create([
            'role' => 'view',
            'site_access_scope' => 'selected',
            'module_access_scope' => 'selected',
        ]);
    }

    // 1. Release creation.
    public function test_super_admin_can_create_release(): void
    {
        $response = $this->actingAs($this->superAdmin())->postJson('/api/releases', [
            'version' => 'CC-2026.09',
            'title' => 'September Governance Update',
            'status' => 'draft',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.version', 'CC-2026.09');
        $response->assertJsonPath('data.status', 'draft');
        $response->assertJsonPath('data.changelogCount', 0);
    }

    // 2. Release retrieval/listing.
    public function test_release_retrieval_and_listing(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin)->postJson('/api/releases', [
            'version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft',
        ])->assertCreated();

        $index = $this->actingAs($admin)->getJson('/api/releases');
        $index->assertOk();
        $index->assertJsonFragment(['version' => 'CC-2026.09']);

        $id = $index->json('data.0.id');
        $show = $this->actingAs($admin)->getJson("/api/releases/{$id}");
        $show->assertOk();
        $show->assertJsonPath('data.version', 'CC-2026.09');
        $show->assertJsonPath('data.changelogs', []);
    }

    // 3. Release update.
    public function test_super_admin_can_update_release(): void
    {
        $admin = $this->superAdmin();
        $created = $this->actingAs($admin)->postJson('/api/releases', [
            'version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft',
        ]);
        $id = $created->json('data.id');

        $response = $this->actingAs($admin)->patchJson("/api/releases/{$id}", [
            'version' => 'CC-2026.09', 'title' => 'September Update (revised)', 'status' => 'ready',
            'release_date' => '2026-09-15', 'description' => 'Now with a description.',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', 'September Update (revised)');
        $response->assertJsonPath('data.status', 'ready');
        $response->assertJsonPath('data.releaseDate', '2026-09-15');
    }

    // 4. Status validation.
    public function test_invalid_status_is_rejected(): void
    {
        $response = $this->actingAs($this->superAdmin())->postJson('/api/releases', [
            'version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'shipped',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    // 5. Changelog creation.
    public function test_super_admin_can_create_changelog_entry(): void
    {
        $admin = $this->superAdmin();
        $release = Release::create(['version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft']);

        $response = $this->actingAs($admin)->postJson("/api/releases/{$release->id}/changelogs", [
            'category' => 'new',
            'title' => 'Site Configuration Foundation',
            'description' => 'Introduced per-site module/menu overrides.',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.category', 'new');
        $response->assertJsonPath('data.title', 'Site Configuration Foundation');
    }

    // 6. Changelog update.
    public function test_super_admin_can_update_changelog_entry(): void
    {
        $admin = $this->superAdmin();
        $release = Release::create(['version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft']);
        $changelog = $release->changelogs()->create(['category' => 'new', 'title' => 'Draft entry']);

        $response = $this->actingAs($admin)->patchJson("/api/releases/{$release->id}/changelogs/{$changelog->id}", [
            'category' => 'improvement',
            'title' => 'Refined entry',
            'description' => 'Clarified wording.',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.category', 'improvement');
        $response->assertJsonPath('data.title', 'Refined entry');
    }

    // 7. Changelog deletion.
    public function test_super_admin_can_delete_changelog_entry(): void
    {
        $admin = $this->superAdmin();
        $release = Release::create(['version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft']);
        $changelog = $release->changelogs()->create(['category' => 'fixed', 'title' => 'Bug fix entry']);

        $response = $this->actingAs($admin)->deleteJson("/api/releases/{$release->id}/changelogs/{$changelog->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('release_changelogs', ['id' => $changelog->id]);
    }

    // 8. Invalid category rejection.
    public function test_invalid_changelog_category_is_rejected(): void
    {
        $admin = $this->superAdmin();
        $release = Release::create(['version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft']);

        $response = $this->actingAs($admin)->postJson("/api/releases/{$release->id}/changelogs", [
            'category' => 'breaking', 'title' => 'Invalid category entry',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category');
    }

    // 9. Invalid release association rejection.
    public function test_changelog_creation_for_nonexistent_release_is_rejected(): void
    {
        $response = $this->actingAs($this->superAdmin())->postJson('/api/releases/999999/changelogs', [
            'category' => 'new', 'title' => 'Orphan entry',
        ]);

        $response->assertStatus(404);
    }

    // 9b. A changelog id that exists but belongs to a DIFFERENT release
    // must not be reachable through this release's URL (scoped binding).
    public function test_changelog_from_another_release_is_not_reachable_through_this_release(): void
    {
        $admin = $this->superAdmin();
        $releaseA = Release::create(['version' => 'CC-2026.09', 'title' => 'A', 'status' => 'draft']);
        $releaseB = Release::create(['version' => 'CC-2026.10', 'title' => 'B', 'status' => 'draft']);
        $changelogOnA = $releaseA->changelogs()->create(['category' => 'new', 'title' => 'Belongs to A']);

        $response = $this->actingAs($admin)->patchJson(
            "/api/releases/{$releaseB->id}/changelogs/{$changelogOnA->id}",
            ['category' => 'new', 'title' => 'Attempted cross-release edit']
        );

        $response->assertStatus(404);
    }

    // 10. Authorization.
    public function test_management_can_read_but_not_write_releases(): void
    {
        $admin = $this->superAdmin();
        $release = $this->actingAs($admin)->postJson('/api/releases', [
            'version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft',
        ]);
        $id = $release->json('data.id');

        $mgmt = $this->management();
        $this->actingAs($mgmt)->getJson('/api/releases')->assertOk();
        $this->actingAs($mgmt)->getJson("/api/releases/{$id}")->assertOk();
        $this->actingAs($mgmt)->postJson('/api/releases', [
            'version' => 'CC-2026.10', 'title' => 'Blocked', 'status' => 'draft',
        ])->assertStatus(403);
        $this->actingAs($mgmt)->patchJson("/api/releases/{$id}", [
            'version' => 'CC-2026.09', 'title' => 'Blocked edit', 'status' => 'draft',
        ])->assertStatus(403);
        $this->actingAs($mgmt)->postJson("/api/releases/{$id}/changelogs", [
            'category' => 'new', 'title' => 'Blocked changelog',
        ])->assertStatus(403);
    }

    public function test_view_cannot_read_or_write_releases(): void
    {
        $viewer = $this->viewer();

        $this->actingAs($viewer)->getJson('/api/releases')->assertStatus(403);
        $this->actingAs($viewer)->postJson('/api/releases', [
            'version' => 'CC-2026.09', 'title' => 'Blocked', 'status' => 'draft',
        ])->assertStatus(403);
    }

    // 11. Audit logging.
    public function test_audit_events_are_logged_for_release_and_changelog_actions(): void
    {
        $admin = $this->superAdmin();

        $created = $this->actingAs($admin)->postJson('/api/releases', [
            'version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft',
        ]);
        $id = $created->json('data.id');
        $this->assertSame(1, AuditLog::where('action', 'release_created')->count());

        $this->actingAs($admin)->patchJson("/api/releases/{$id}", [
            'version' => 'CC-2026.09', 'title' => 'September Update (v2)', 'status' => 'draft',
        ])->assertOk();
        $this->assertSame(1, AuditLog::where('action', 'release_updated')->count());
        $this->assertSame(0, AuditLog::where('action', 'release_status_changed')->count());

        $this->actingAs($admin)->patchJson("/api/releases/{$id}", [
            'version' => 'CC-2026.09', 'title' => 'September Update (v2)', 'status' => 'released',
        ])->assertOk();
        $log = AuditLog::where('action', 'release_status_changed')->first();
        $this->assertNotNull($log);
        $this->assertSame('draft', $log->metadata['previous_status']);
        $this->assertSame('released', $log->metadata['new_status']);

        $changelog = $this->actingAs($admin)->postJson("/api/releases/{$id}/changelogs", [
            'category' => 'new', 'title' => 'Entry',
        ]);
        $this->assertSame(1, AuditLog::where('action', 'changelog_created')->count());

        $changelogId = $changelog->json('data.id');
        $this->actingAs($admin)->patchJson("/api/releases/{$id}/changelogs/{$changelogId}", [
            'category' => 'improvement', 'title' => 'Entry (revised)',
        ])->assertOk();
        $this->assertSame(1, AuditLog::where('action', 'changelog_updated')->count());

        $this->actingAs($admin)->deleteJson("/api/releases/{$id}/changelogs/{$changelogId}")->assertNoContent();
        $this->assertSame(1, AuditLog::where('action', 'changelog_deleted')->count());
    }

    public function test_noop_release_update_does_not_create_audit_events(): void
    {
        $admin = $this->superAdmin();
        $created = $this->actingAs($admin)->postJson('/api/releases', [
            'version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft',
        ]);
        $id = $created->json('data.id');
        $countAfterCreate = AuditLog::count();

        // Identical payload — nothing actually changes.
        $this->actingAs($admin)->patchJson("/api/releases/{$id}", [
            'version' => 'CC-2026.09', 'title' => 'September Update', 'status' => 'draft',
        ])->assertOk();

        $this->assertSame($countAfterCreate, AuditLog::count());
    }

    // Version uniqueness (justified in the migration/request doc
    // comments — a formal catalog should not have two ambiguous
    // entries claiming the same version).
    public function test_duplicate_version_is_rejected(): void
    {
        $admin = $this->superAdmin();
        Release::create(['version' => 'CC-2026.09', 'title' => 'First', 'status' => 'draft']);

        $response = $this->actingAs($admin)->postJson('/api/releases', [
            'version' => 'CC-2026.09', 'title' => 'Duplicate', 'status' => 'draft',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('version');
    }
}
