<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ConfigurationSnapshot;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STEP 3R-1 — CONFIGURATION SNAPSHOT FOUNDATION.
 *
 * Covers immutability (no route can modify a snapshot after creation),
 * correct effective-state resolution (matching
 * resources/js/composables/useSiteConfiguration.js's rules — baseline
 * default enabled, explicit override wins, parent derived from
 * children), per-site isolation, and audit integration.
 *
 * Uses temporary in-memory sqlite records (RefreshDatabase) — never
 * touches real application users.
 */
class ConfigurationSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private Site $bib;
    private Site $mhu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bib = Site::create(['code' => 'BIB', 'name' => 'PT Borneo Indobara']);
        $this->mhu = Site::create(['code' => 'MHU', 'name' => 'PT Mitra Hutama Utama']);
    }

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

    // 1. Super Admin can create snapshot.
    public function test_super_admin_can_create_snapshot(): void
    {
        $response = $this->actingAs($this->superAdmin())->postJson("/api/configuration-snapshots/{$this->bib->id}");

        $response->assertCreated();
        $response->assertJsonPath('data.snapshotNumber', 1);
        $response->assertJsonPath('data.site.code', 'BIB');
        $response->assertJsonPath('data.status', 'immutable');
    }

    // 2. Management cannot create snapshot.
    public function test_management_cannot_create_snapshot(): void
    {
        $response = $this->actingAs($this->management())->postJson("/api/configuration-snapshots/{$this->bib->id}");

        $response->assertStatus(403);
    }

    // 3. View cannot create snapshot.
    public function test_view_cannot_create_snapshot(): void
    {
        $response = $this->actingAs($this->viewer())->postJson("/api/configuration-snapshots/{$this->bib->id}");

        $response->assertStatus(403);
    }

    // 4. Snapshot contains correct effective module state.
    public function test_snapshot_contains_correct_effective_module_state(): void
    {
        $admin = $this->superAdmin();

        // Disable one baseline module before snapshotting.
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => false,
        ])->assertNoContent();

        $response = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $response->assertCreated();
        $response->assertJsonPath('data.moduleCount', 7); // 8 baseline - 1 disabled

        $uuid = $response->json('data.id');
        $detail = $this->actingAs($admin)->getJson("/api/configuration-snapshots/{$this->bib->id}/{$uuid}");
        $detail->assertOk();
        $this->assertContains(
            ['moduleKey' => 'one-reporting', 'enabled' => false],
            $detail->json('data.modules')
        );
        $this->assertContains(
            ['moduleKey' => 'one-power', 'enabled' => true],
            $detail->json('data.modules')
        );
    }

    // 5. Snapshot contains correct effective menu state (including
    // parent-derived-from-children behavior).
    public function test_snapshot_contains_correct_effective_menu_state(): void
    {
        $admin = $this->superAdmin();

        // Disable a leaf menu ('mor', a standalone leaf).
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/menus", [
            'module_key' => 'one-power',
            'menu_key' => 'mor',
            'enabled' => false,
        ])->assertNoContent();

        // Disable BOTH children of 'roster' -> the parent must derive to disabled.
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/menus", [
            'module_key' => 'one-power',
            'menu_key' => 'roster-departement',
            'enabled' => false,
        ])->assertNoContent();
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/menus", [
            'module_key' => 'one-power',
            'menu_key' => 'roster-personal',
            'enabled' => false,
        ])->assertNoContent();

        $response = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $response->assertCreated();
        // 72 baseline - mor - roster (derived, both children disabled)
        // - roster-departement - roster-personal = 68
        $response->assertJsonPath('data.menuCount', 68);

        $uuid = $response->json('data.id');
        $detail = $this->actingAs($admin)->getJson("/api/configuration-snapshots/{$this->bib->id}/{$uuid}");
        $menus = collect($detail->json('data.menus'))->keyBy('menuKey');

        $this->assertFalse($menus['mor']['enabled']);
        $this->assertFalse($menus['roster-departement']['enabled']);
        $this->assertFalse($menus['roster-personal']['enabled']);
        $this->assertFalse($menus['roster']['enabled'], 'Parent must derive to disabled when all children are disabled.');
        // Untouched leaf sibling of roster stays enabled.
        $this->assertTrue($menus['data-karyawan']['enabled']);
    }

    // 6. Snapshot number increments per site.
    public function test_snapshot_number_increments_per_site(): void
    {
        $admin = $this->superAdmin();

        $first = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $second = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $third = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");

        $this->assertSame(1, $first->json('data.snapshotNumber'));
        $this->assertSame(2, $second->json('data.snapshotNumber'));
        $this->assertSame(3, $third->json('data.snapshotNumber'));
    }

    // 7. Snapshot from BIB does not affect MHU.
    public function test_snapshot_from_one_site_does_not_affect_another(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}")->assertCreated();

        $mhuIndex = $this->actingAs($admin)->getJson("/api/configuration-snapshots/{$this->mhu->id}");
        $mhuIndex->assertOk();
        $this->assertCount(0, $mhuIndex->json('data'));

        $bibIndex = $this->actingAs($admin)->getJson("/api/configuration-snapshots/{$this->bib->id}");
        $this->assertCount(1, $bibIndex->json('data'));
    }

    // 8. Changing Site Configuration after snapshot does NOT change the snapshot.
    public function test_site_configuration_change_after_snapshot_does_not_change_it(): void
    {
        $admin = $this->superAdmin();

        $created = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $created->assertJsonPath('data.moduleCount', 8);
        $uuid = $created->json('data.id');

        // Disable a module AFTER the snapshot was taken.
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-gmp',
            'enabled' => false,
        ])->assertNoContent();

        $detail = $this->actingAs($admin)->getJson("/api/configuration-snapshots/{$this->bib->id}/{$uuid}");
        $detail->assertJsonPath('data.moduleCount', 8);
        $this->assertContains(
            ['moduleKey' => 'one-gmp', 'enabled' => true],
            $detail->json('data.modules'),
            'The snapshot must still show the state at creation time, not the current live state.'
        );
    }

    // 9. Creating a second snapshot creates a NEW snapshot.
    public function test_second_snapshot_reflects_current_state(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}")->assertJsonPath('data.moduleCount', 8);

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-setting',
            'enabled' => false,
        ])->assertNoContent();

        $second = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $second->assertJsonPath('data.snapshotNumber', 2);
        $second->assertJsonPath('data.moduleCount', 7);
    }

    // 10. First snapshot remains unchanged after second snapshot.
    public function test_first_snapshot_unchanged_after_second_snapshot(): void
    {
        $admin = $this->superAdmin();

        $first = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $firstUuid = $first->json('data.id');

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-setting',
            'enabled' => false,
        ])->assertNoContent();
        $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}")->assertCreated();

        $firstAfter = $this->actingAs($admin)->getJson("/api/configuration-snapshots/{$this->bib->id}/{$firstUuid}");
        $firstAfter->assertJsonPath('data.moduleCount', 8);
        $firstAfter->assertJsonPath('data.snapshotNumber', 1);
    }

    // 11. Snapshot child rows persist correctly.
    public function test_snapshot_child_rows_persist_in_database(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $snapshot = ConfigurationSnapshot::where('uuid', $response->json('data.id'))->firstOrFail();

        $this->assertSame(8, $snapshot->modules()->count());
        $this->assertSame(72, $snapshot->menus()->count());
        $this->assertDatabaseHas('configuration_snapshot_modules', [
            'snapshot_id' => $snapshot->id,
            'module_key' => 'one-power',
            'enabled' => true,
        ]);
    }

    // 12. Audit event created on snapshot creation.
    public function test_audit_event_created_on_snapshot_creation(): void
    {
        $admin = $this->superAdmin();

        $this->assertSame(0, AuditLog::where('action', 'configuration_snapshot_created')->count());

        $response = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");

        $log = AuditLog::where('action', 'configuration_snapshot_created')->first();
        $this->assertNotNull($log);
        $this->assertSame('BIB', $log->metadata['site_code']);
        $this->assertSame($response->json('data.id'), $log->metadata['snapshot_id']);
        $this->assertSame(1, $log->metadata['snapshot_number']);
        $this->assertArrayNotHasKey('modules', $log->metadata);
        $this->assertArrayNotHasKey('menus', $log->metadata);
    }

    // 13. Reading a snapshot does not create an audit event.
    public function test_reading_snapshot_does_not_create_audit_event(): void
    {
        $admin = $this->superAdmin();
        $created = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $uuid = $created->json('data.id');

        $countAfterCreate = AuditLog::count();

        $this->actingAs($admin)->getJson("/api/configuration-snapshots/{$this->bib->id}")->assertOk();
        $this->actingAs($admin)->getJson("/api/configuration-snapshots/{$this->bib->id}/{$uuid}")->assertOk();

        $this->assertSame($countAfterCreate, AuditLog::count(), 'Reading a snapshot must not create any audit event.');
    }

    // 14. Snapshot cannot be modified through any exposed route.
    public function test_snapshot_cannot_be_modified_through_any_route(): void
    {
        $admin = $this->superAdmin();
        $created = $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$this->bib->id}");
        $uuid = $created->json('data.id');

        // No PATCH/PUT/DELETE route exists for this resource at all —
        // the URI pattern is only ever registered for GET/POST, so
        // Laravel correctly reports 405 (method not allowed on this
        // URI) rather than 404 (no such URI), which equally proves no
        // mutating verb can reach this resource.
        $this->actingAs($admin)->patchJson("/api/configuration-snapshots/{$this->bib->id}/{$uuid}", ['module_count' => 1])
            ->assertStatus(405);
        $this->actingAs($admin)->putJson("/api/configuration-snapshots/{$this->bib->id}/{$uuid}", ['module_count' => 1])
            ->assertStatus(405);
        $this->actingAs($admin)->deleteJson("/api/configuration-snapshots/{$this->bib->id}/{$uuid}")
            ->assertStatus(405);

        // Defense-in-depth: the model itself refuses a direct update.
        $snapshot = ConfigurationSnapshot::where('uuid', $uuid)->firstOrFail();
        $this->expectException(\RuntimeException::class);
        $snapshot->update(['module_count' => 999]);
    }
}
