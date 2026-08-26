<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Site;
use App\Models\User;
use App\Support\AccessScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STEP 3Q EFFECTIVE ACCESS VISIBILITY CORRECTION.
 *
 * Covers the reusable backend authorization primitive (AccessScope) and
 * the /api/user contract the frontend access-context composable relies
 * on — not the mock-data UI itself (that has no backend to assert
 * against yet; see the correction report's "remaining limitations").
 */
class AccessScopeTest extends TestCase
{
    use RefreshDatabase;

    private Site $siteA;
    private Site $siteB;
    private Site $siteC;
    private Module $moduleManpower;
    private Module $moduleFleet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteA = Site::create(['code' => 'BIB', 'name' => 'PT Borneo Indobara']);
        $this->siteB = Site::create(['code' => 'MHU', 'name' => 'PT Mitra Hutama Utama']);
        $this->siteC = Site::create(['code' => 'SKS', 'name' => 'PT Sarana Karya Sejahtera']);

        $this->moduleManpower = Module::create(['key' => 'one-manpower', 'label' => 'One-Manpower']);
        $this->moduleFleet = Module::create(['key' => 'one-fleet', 'label' => 'One-Fleet']);
    }

    public function test_super_admin_can_access_every_site_and_module_regardless_of_stored_scope(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'site_access_scope' => 'selected', // deliberately wrong/stale value
            'module_access_scope' => 'selected',
        ]);

        $this->assertTrue(AccessScope::userCanAccessSite($admin, $this->siteA));
        $this->assertTrue(AccessScope::userCanAccessSite($admin, $this->siteC));
        $this->assertTrue(AccessScope::userCanAccessSite($admin, 'ANY-UNKNOWN-CODE'));
        $this->assertTrue(AccessScope::userCanAccessModule($admin, $this->moduleFleet));
    }

    public function test_management_user_can_only_access_explicitly_selected_sites_and_modules(): void
    {
        $manager = User::factory()->create([
            'role' => 'management',
            'site_access_scope' => 'selected',
            'module_access_scope' => 'selected',
        ]);
        $manager->sites()->sync([$this->siteA->id, $this->siteB->id]);
        $manager->modules()->sync([$this->moduleManpower->id]);

        $this->assertTrue(AccessScope::userCanAccessSite($manager, $this->siteA));
        $this->assertTrue(AccessScope::userCanAccessSite($manager, $this->siteB));
        $this->assertFalse(AccessScope::userCanAccessSite($manager, $this->siteC));
        $this->assertFalse(AccessScope::userCanAccessSite($manager, 'SKS'));

        $this->assertTrue(AccessScope::userCanAccessModule($manager, $this->moduleManpower));
        $this->assertFalse(AccessScope::userCanAccessModule($manager, $this->moduleFleet));
    }

    public function test_view_user_can_only_access_explicitly_selected_site(): void
    {
        $viewer = User::factory()->create([
            'role' => 'view',
            'site_access_scope' => 'selected',
            'module_access_scope' => 'selected',
        ]);
        $viewer->sites()->sync([$this->siteA->id]);

        $this->assertTrue(AccessScope::userCanAccessSite($viewer, $this->siteA));
        $this->assertFalse(AccessScope::userCanAccessSite($viewer, $this->siteB));
        $this->assertFalse(AccessScope::userCanAccessSite($viewer, $this->siteC));
    }

    public function test_a_non_super_admin_with_no_pivot_rows_can_access_nothing(): void
    {
        $viewer = User::factory()->create([
            'role' => 'view',
            'site_access_scope' => 'selected',
            'module_access_scope' => 'selected',
        ]);

        $this->assertFalse(AccessScope::userCanAccessSite($viewer, $this->siteA));
        $this->assertFalse(AccessScope::userCanAccessModule($viewer, $this->moduleManpower));
    }

    public function test_api_user_endpoint_exposes_site_codes_and_module_keys_for_a_management_user(): void
    {
        $manager = User::factory()->create([
            'role' => 'management',
            'site_access_scope' => 'selected',
            'module_access_scope' => 'selected',
        ]);
        $manager->sites()->sync([$this->siteA->id, $this->siteB->id]);
        $manager->modules()->sync([$this->moduleManpower->id]);

        $response = $this->actingAs($manager)->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonPath('data.siteAccess.mode', 'selected');
        $response->assertJsonPath('data.moduleAccess.mode', 'selected');

        $siteCodes = $response->json('data.siteAccess.siteCodes');
        $moduleKeys = $response->json('data.moduleAccess.moduleKeys');

        sort($siteCodes);
        $this->assertSame(['BIB', 'MHU'], $siteCodes);
        $this->assertSame(['one-manpower'], $moduleKeys);
    }

    public function test_api_user_endpoint_reports_all_mode_with_no_ids_for_super_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
            'site_access_scope' => 'all',
            'module_access_scope' => 'all',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonPath('data.siteAccess.mode', 'all');
        $response->assertJsonPath('data.siteAccess.siteCodes', []);
        $response->assertJsonPath('data.moduleAccess.mode', 'all');
        $response->assertJsonPath('data.moduleAccess.moduleKeys', []);
    }
}
