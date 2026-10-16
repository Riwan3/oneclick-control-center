<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Site;
use App\Models\User;
use App\Services\SiteExtensions;
use App\Support\BaselineKeys;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaselineRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'site_access_scope' => 'all',
            'module_access_scope' => 'all',
        ]);
    }

    // WARA reports one-coal, a module the baseline does not have, with a small menu tree.
    private function wara(): Site
    {
        $site = Site::create(['code' => 'WARA', 'name' => 'PT Wara']);
        $site->forceFill([
            'modules' => ['one-power', 'one-coal'],
            'menus' => [
                'one-coal' => ['version' => 'v1.0.0', 'menus' => [
                    ['id' => 'coal-dashboard', 'label' => 'Dashboard Coal', 'parentId' => null, 'order' => 1],
                    ['id' => 'coal-stock', 'label' => 'Stock', 'parentId' => null, 'order' => 2],
                    ['id' => 'coal-stock-pile', 'label' => 'Stock Pile', 'parentId' => 'coal-stock', 'order' => 1],
                ]],
            ],
        ])->save();

        return $site;
    }

    public function test_an_extension_reported_by_a_site_is_listed_as_a_candidate(): void
    {
        $this->wara();

        $this->actingAs($this->user('super_admin'))->getJson('/api/baseline')
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('candidates.0.moduleKey', 'one-coal')
            ->assertJsonPath('candidates.0.sites', ['WARA'])
            ->assertJsonPath('candidates.0.menuCount', 3);
    }

    public function test_registering_promotes_the_extension_into_the_baseline(): void
    {
        $wara = $this->wara();
        $admin = $this->user('super_admin');

        $this->actingAs($admin)->postJson('/api/baseline/modules', ['module_key' => 'one-coal', 'description' => 'Coal handling'])
            ->assertCreated()
            ->assertJsonPath('data.moduleKey', 'one-coal')
            ->assertJsonPath('data.label', 'One-Coal')
            ->assertJsonPath('data.sourceSiteCode', 'WARA')
            ->assertJsonCount(3, 'data.menus');

        // The module and its menu hierarchy are now part of the baseline ...
        $this->assertContains('one-coal', BaselineKeys::moduleKeys());
        $this->assertTrue(BaselineKeys::isLeaf('one-coal', 'coal-stock-pile'));
        $this->assertFalse(BaselineKeys::isLeaf('one-coal', 'coal-stock'));
        // ... so it is no longer an extension, nor a candidate.
        $this->assertSame([], SiteExtensions::forSite($wara->fresh()));
        $this->actingAs($admin)->getJson('/api/baseline')->assertJsonPath('candidates', [])->assertJsonPath('data.0.moduleKey', 'one-coal');

        // A site can still switch the (now baseline) module off.
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$wara->id}/modules", ['module_key' => 'one-coal', 'enabled' => false])->assertNoContent();

        $this->assertDatabaseHas('audit_logs', ['action' => 'baseline_module_registered', 'user_id' => $admin->id]);
    }

    public function test_a_registered_module_is_part_of_snapshots(): void
    {
        $wara = $this->wara();
        $admin = $this->user('super_admin');
        $this->actingAs($admin)->postJson('/api/baseline/modules', ['module_key' => 'one-coal'])->assertCreated();

        $this->actingAs($admin)->postJson("/api/configuration-snapshots/{$wara->id}", ['note' => 'after register'])->assertCreated();

        $this->assertDatabaseHas('configuration_snapshot_modules', ['module_key' => 'one-coal', 'enabled' => true]);
        $this->assertDatabaseHas('configuration_snapshot_menus', ['module_key' => 'one-coal', 'menu_key' => 'coal-stock-pile', 'enabled' => true]);
    }

    public function test_unregistering_turns_it_back_into_an_extension_and_keeps_site_overrides(): void
    {
        $wara = $this->wara();
        $admin = $this->user('super_admin');
        $this->actingAs($admin)->postJson('/api/baseline/modules', ['module_key' => 'one-coal'])->assertCreated();
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$wara->id}/modules", ['module_key' => 'one-coal', 'enabled' => false])->assertNoContent();

        $this->actingAs($admin)->deleteJson('/api/baseline/modules/one-coal')->assertNoContent();

        $this->assertNotContains('one-coal', BaselineKeys::moduleKeys());
        $this->assertSame(['one-coal'], array_column(SiteExtensions::forSite($wara->fresh()), 'key'));
        $this->assertDatabaseHas('site_module_configs', ['site_id' => $wara->id, 'module_key' => 'one-coal', 'enabled' => false]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'baseline_module_unregistered']);
    }

    public function test_a_core_module_is_locked(): void
    {
        $admin = $this->user('super_admin');

        $this->actingAs($admin)->postJson('/api/baseline/modules', ['module_key' => 'one-power'])
            ->assertStatus(422)->assertJsonValidationErrors('module_key');
        $this->actingAs($admin)->deleteJson('/api/baseline/modules/one-power')
            ->assertStatus(422)->assertJsonValidationErrors('module_key');
    }

    public function test_a_module_no_site_reports_cannot_be_registered(): void
    {
        $this->wara();

        $this->actingAs($this->user('super_admin'))->postJson('/api/baseline/modules', ['module_key' => 'one-ghost'])
            ->assertStatus(422)->assertJsonValidationErrors('module_key');
        $this->actingAs($this->user('super_admin'))->postJson('/api/baseline/modules', ['module_key' => 'Not Valid!'])
            ->assertStatus(422)->assertJsonValidationErrors('module_key');
    }

    public function test_registering_the_same_module_twice_is_rejected(): void
    {
        $this->wara();
        $admin = $this->user('super_admin');

        $this->actingAs($admin)->postJson('/api/baseline/modules', ['module_key' => 'one-coal'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/baseline/modules', ['module_key' => 'one-coal'])->assertStatus(422);
        $this->actingAs($admin)->deleteJson('/api/baseline/modules/one-unknown')->assertStatus(422);
    }

    public function test_only_super_admin_can_change_the_baseline_and_management_can_read_it(): void
    {
        $this->wara();

        $management = $this->user('management');
        $this->actingAs($management)->getJson('/api/baseline')->assertOk();
        $this->actingAs($management)->postJson('/api/baseline/modules', ['module_key' => 'one-coal'])->assertForbidden();
        $this->actingAs($management)->deleteJson('/api/baseline/modules/one-coal')->assertForbidden();

        $viewer = $this->user('view');
        $this->actingAs($viewer)->getJson('/api/baseline')->assertForbidden();
        $this->actingAs($viewer)->postJson('/api/baseline/modules', ['module_key' => 'one-coal'])->assertForbidden();

        $this->assertSame(0, AuditLog::where('action', 'baseline_module_registered')->count());
    }
}
