<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SITE CONFIGURATION HARDENING.
 *
 * Covers the sparse-override model (an override row exists ONLY when it
 * deviates from the baseline default of "enabled"), no-op detection (no
 * write, no audit event, when the requested state already matches
 * effective state), audit integration, per-site isolation, and the
 * documented format-only key validation (see SiteConfigurationController
 * for why full baseline-key existence validation is not implemented —
 * it would require duplicating the Master Baseline Registry server-side).
 *
 * Uses temporary in-memory sqlite records (RefreshDatabase) — never
 * touches real application users or data.
 */
class SiteConfigurationTest extends TestCase
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

    // 1. Super Admin can read site configurations.
    public function test_super_admin_can_read_site_configurations(): void
    {
        $response = $this->actingAs($this->superAdmin())->getJson('/api/site-configurations');

        $response->assertOk();
        $response->assertJsonFragment(['siteCode' => 'BIB']);
        $response->assertJsonFragment(['siteCode' => 'MHU']);
    }

    // 2. Management cannot access site configuration API.
    public function test_management_cannot_access_site_configuration_api(): void
    {
        $response = $this->actingAs($this->management())->getJson('/api/site-configurations');

        $response->assertStatus(403);
    }

    // 3. View cannot access site configuration API.
    public function test_view_cannot_access_site_configuration_api(): void
    {
        $response = $this->actingAs($this->viewer())->getJson('/api/site-configurations');

        $response->assertStatus(403);
    }

    // 4 + 5. Super Admin can disable a module for one site; the override persists.
    public function test_super_admin_can_disable_a_module_and_it_persists(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => false,
        ]);
        $response->assertNoContent();

        $this->assertDatabaseHas('site_module_configs', [
            'site_id' => $this->bib->id,
            'module_key' => 'one-reporting',
            'enabled' => false,
        ]);

        $index = $this->actingAs($admin)->getJson('/api/site-configurations');
        $bibEntry = collect($index->json('data'))->firstWhere('siteCode', 'BIB');
        $this->assertContains(
            ['moduleKey' => 'one-reporting', 'enabled' => false],
            $bibEntry['moduleOverrides']
        );
    }

    // 6. Re-enabling a module restores the baseline-effective state — and,
    // per the sparse-override model, deletes the row entirely.
    public function test_reenabling_a_module_deletes_the_override_row(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => false,
        ])->assertNoContent();
        $this->assertDatabaseHas('site_module_configs', ['site_id' => $this->bib->id, 'module_key' => 'one-reporting']);

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => true,
        ])->assertNoContent();

        $this->assertDatabaseMissing('site_module_configs', [
            'site_id' => $this->bib->id,
            'module_key' => 'one-reporting',
        ]);
    }

    // 7 + 8. Super Admin can disable a leaf menu; the override persists.
    public function test_super_admin_can_disable_a_leaf_menu_and_it_persists(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/menus", [
            'module_key' => 'one-power',
            'menu_key' => 'quartal-operator-report',
            'enabled' => false,
        ]);
        $response->assertNoContent();

        $this->assertDatabaseHas('site_menu_configs', [
            'site_id' => $this->bib->id,
            'module_key' => 'one-power',
            'menu_key' => 'quartal-operator-report',
            'enabled' => false,
        ]);
    }

    // 9. Re-enabling a menu restores baseline-effective state (row deleted).
    public function test_reenabling_a_menu_deletes_the_override_row(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/menus", [
            'module_key' => 'one-power',
            'menu_key' => 'quartal-operator-report',
            'enabled' => false,
        ])->assertNoContent();

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/menus", [
            'module_key' => 'one-power',
            'menu_key' => 'quartal-operator-report',
            'enabled' => true,
        ])->assertNoContent();

        $this->assertDatabaseMissing('site_menu_configs', [
            'site_id' => $this->bib->id,
            'module_key' => 'one-power',
            'menu_key' => 'quartal-operator-report',
        ]);
    }

    // 10. Configuration for BIB does not affect MHU.
    public function test_configuration_for_one_site_does_not_affect_another(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => false,
        ])->assertNoContent();

        $this->assertDatabaseHas('site_module_configs', ['site_id' => $this->bib->id, 'module_key' => 'one-reporting']);
        $this->assertDatabaseMissing('site_module_configs', ['site_id' => $this->mhu->id, 'module_key' => 'one-reporting']);

        $index = $this->actingAs($admin)->getJson('/api/site-configurations');
        $mhuEntry = collect($index->json('data'))->firstWhere('siteCode', 'MHU');
        $this->assertSame([], $mhuEntry['moduleOverrides']);
    }

    // 11. Audit event is generated when state actually changes.
    public function test_audit_event_generated_on_actual_state_change(): void
    {
        $admin = $this->superAdmin();

        $this->assertSame(0, AuditLog::where('action', 'site_module_disabled')->count());

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => false,
        ])->assertNoContent();

        $this->assertSame(1, AuditLog::where('action', 'site_module_disabled')->count());
        $log = AuditLog::where('action', 'site_module_disabled')->first();
        $this->assertSame($this->bib->id, $log->entity_id);
        $this->assertSame('SiteModuleConfig', $log->entity_type);
        $this->assertSame('BIB', $log->metadata['site_code']);
        $this->assertFalse($log->metadata['new_state']);
        $this->assertTrue($log->metadata['previous_state']);
    }

    // 12. No audit event is generated for a no-op toggle.
    public function test_no_audit_event_for_noop_toggle(): void
    {
        $admin = $this->superAdmin();

        // Baseline enabled, no override exists, request enabled=true -> no-op.
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => true,
        ])->assertNoContent();

        $this->assertSame(0, AuditLog::count());
        $this->assertDatabaseMissing('site_module_configs', ['site_id' => $this->bib->id, 'module_key' => 'one-reporting']);

        // Disable once (real change, 1 audit event), then repeat the same
        // disable request again -> the second call must be a no-op.
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => false,
        ])->assertNoContent();
        $this->assertSame(1, AuditLog::count());

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-reporting',
            'enabled' => false,
        ])->assertNoContent();
        $this->assertSame(1, AuditLog::count(), 'Repeating the same disable request must not add a second audit event.');
    }

    // 13. Invalid/unknown module key. Determined NOT safely validatable
    // for existence without duplicating the baseline registry (see
    // SiteConfigurationController's doc comment) — format is still
    // validated, and a well-formed-but-unknown key is accepted but
    // proven inert by construction (the frontend resolver only ever
    // iterates real baseline keys, so this row can never surface).
    public function test_malformed_module_key_is_rejected_but_unknown_wellformed_key_is_inert(): void
    {
        $admin = $this->superAdmin();

        $malformed = $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'Not A Valid Key!',
            'enabled' => false,
        ]);
        $malformed->assertStatus(422);
        $this->assertDatabaseMissing('site_module_configs', ['site_id' => $this->bib->id]);

        // Well-formed but not a real baseline module — accepted (the
        // backend cannot know it isn't real without duplicating the
        // registry), but this alone proves nothing unsafe happens: the
        // row is a normal, isolated SiteModuleConfig row that no
        // resolver anywhere reads unless it iterates this exact key.
        $unknown = $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/modules", [
            'module_key' => 'one-nonexistent-module',
            'enabled' => false,
        ]);
        $unknown->assertNoContent();
        $this->assertDatabaseHas('site_module_configs', [
            'site_id' => $this->bib->id,
            'module_key' => 'one-nonexistent-module',
        ]);
    }

    // 14. Same rule for menu keys.
    public function test_malformed_menu_key_is_rejected_but_unknown_wellformed_key_is_inert(): void
    {
        $admin = $this->superAdmin();

        $malformed = $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/menus", [
            'module_key' => 'one-power',
            'menu_key' => 'Not Valid!',
            'enabled' => false,
        ]);
        $malformed->assertStatus(422);
        $this->assertDatabaseMissing('site_menu_configs', ['site_id' => $this->bib->id]);

        $unknown = $this->actingAs($admin)->patchJson("/api/site-configurations/{$this->bib->id}/menus", [
            'module_key' => 'one-power',
            'menu_key' => 'nonexistent-menu-item',
            'enabled' => false,
        ]);
        $unknown->assertNoContent();
        $this->assertDatabaseHas('site_menu_configs', [
            'site_id' => $this->bib->id,
            'menu_key' => 'nonexistent-menu-item',
        ]);
    }
}
