<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use App\Services\SiteConfigPayload;
use App\Services\SiteExtensions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteExtensionsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'super_admin',
            'site_access_scope' => 'all',
            'module_access_scope' => 'all',
        ]);
    }

    // WARA reports every kind of module: a baseline one under the site's own key (one-core),
    // and a module the baseline does not have (one-coal) with a small menu tree.
    private function wara(): Site
    {
        $site = Site::create(['code' => 'WARA', 'name' => 'PT Wara']);
        $site->forceFill([
            'modules' => ['one-power', 'one-core', 'one-coal'],
            'menus' => [
                'one-power' => ['version' => 'v2.5.0', 'menus' => [['id' => 'dashboard', 'label' => 'Dashboard', 'parentId' => null, 'order' => 1]]],
                'one-coal' => ['version' => 'v1.0.0', 'menus' => [
                    ['id' => 'coal-dashboard', 'label' => 'Dashboard Coal', 'parentId' => null, 'order' => 1],
                    ['id' => 'coal-stock', 'label' => 'Stock', 'parentId' => null, 'order' => 2],
                    ['id' => 'coal-stock-pile', 'label' => 'Stock Pile', 'parentId' => 'coal-stock', 'order' => 1],
                ]],
            ],
        ])->save();

        return $site;
    }

    public function test_only_modules_outside_the_baseline_are_extensions(): void
    {
        $extensions = SiteExtensions::forSite($this->wara());

        $this->assertCount(1, $extensions, 'one-power and one-core (the baseline one-core-value) are not extensions.');
        $this->assertSame('one-coal', $extensions[0]['key']);
        $this->assertSame('One-Coal', $extensions[0]['label']);
        $this->assertSame('v1.0.0', $extensions[0]['version']);
        $this->assertCount(3, $extensions[0]['menus']);
    }

    public function test_malformed_module_keys_reported_by_a_site_are_ignored(): void
    {
        $site = Site::create(['code' => 'ODD', 'name' => 'PT Odd']);
        $site->forceFill(['modules' => ['one-ok', 'Not Valid!', str_repeat('a', 101)]])->save();

        $this->assertSame(['one-ok'], array_column(SiteExtensions::forSite($site), 'key'));
    }

    public function test_an_extension_module_can_be_switched_off_only_for_the_site_that_reported_it(): void
    {
        $wara = $this->wara();
        $other = Site::create(['code' => 'BIB', 'name' => 'PT Bib']);
        $admin = $this->admin();

        $this->actingAs($admin)->patchJson("/api/site-configurations/{$wara->id}/modules", ['module_key' => 'one-coal', 'enabled' => false])
            ->assertNoContent();
        $this->assertDatabaseHas('site_module_configs', ['site_id' => $wara->id, 'module_key' => 'one-coal', 'enabled' => false]);

        // A site that never reported one-coal cannot be given an override for it.
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$other->id}/modules", ['module_key' => 'one-coal', 'enabled' => false])
            ->assertStatus(422)->assertJsonValidationErrors('module_key');
        $this->assertDatabaseMissing('site_module_configs', ['site_id' => $other->id]);

        // Nor one nobody reported.
        $this->actingAs($admin)->patchJson("/api/site-configurations/{$wara->id}/modules", ['module_key' => 'one-unknown', 'enabled' => false])
            ->assertStatus(422);
    }

    public function test_only_leaf_menus_of_an_extension_can_be_switched_off(): void
    {
        $wara = $this->wara();
        $admin = $this->admin();
        $url = "/api/site-configurations/{$wara->id}/menus";

        $this->actingAs($admin)->patchJson($url, ['module_key' => 'one-coal', 'menu_key' => 'coal-stock-pile', 'menu_label' => 'Stock Pile', 'enabled' => false])
            ->assertNoContent();
        $this->assertDatabaseHas('site_menu_configs', ['site_id' => $wara->id, 'module_key' => 'one-coal', 'menu_key' => 'coal-stock-pile', 'enabled' => false]);

        // 'coal-stock' has a child: its state is derived.
        $this->actingAs($admin)->patchJson($url, ['module_key' => 'one-coal', 'menu_key' => 'coal-stock', 'enabled' => false])
            ->assertStatus(422)->assertJsonValidationErrors('menu_key');
        // A menu the site never reported for that module.
        $this->actingAs($admin)->patchJson($url, ['module_key' => 'one-coal', 'menu_key' => 'coal-nothing', 'enabled' => false])
            ->assertStatus(422)->assertJsonValidationErrors('menu_key');
        // Extra menus inside a BASELINE module stay locked (monitor only).
        $this->actingAs($admin)->patchJson($url, ['module_key' => 'one-power', 'menu_key' => 'a-menu-only-wara-has', 'enabled' => false])
            ->assertStatus(422);
    }

    public function test_disabled_extensions_travel_to_the_site_in_its_own_keys(): void
    {
        $wara = $this->wara();
        $wara->moduleConfigs()->create(['module_key' => 'one-coal', 'enabled' => false]);
        $wara->moduleConfigs()->create(['module_key' => 'one-core-value', 'enabled' => false]);
        $wara->menuConfigs()->create(['module_key' => 'one-coal', 'menu_key' => 'coal-stock-pile', 'menu_label' => 'Stock Pile', 'enabled' => false]);

        $config = (new SiteConfigPayload)->forSite($wara);

        $this->assertEqualsCanonicalizing(['one-coal', 'one-core'], $config['disabled_modules']);
        $this->assertSame(['Stock Pile'], $config['disabled_menus']['one-coal']);
    }

    public function test_extensions_are_listed_with_the_site_connection_and_configuration(): void
    {
        $this->wara();
        $admin = $this->admin();

        $connection = $this->actingAs($admin)->getJson('/api/site-connections')->assertOk();
        $connection->assertJsonPath('data.0.extensions.0.key', 'one-coal');
        $connection->assertJsonPath('data.0.extensions.0.menu_count', 3);

        $configuration = $this->actingAs($admin)->getJson('/api/site-configurations')->assertOk();
        $configuration->assertJsonPath('data.0.extensions.0.key', 'one-coal');
        $configuration->assertJsonPath('data.0.extensions.0.menus.2.parentId', 'coal-stock');
    }

    public function test_the_observatory_serves_an_extension_module(): void
    {
        $this->wara();

        $response = $this->actingAs($this->admin())->getJson('/api/module-observatory/one-coal')->assertOk();

        $response->assertJsonCount(1, 'data.sites');
        $response->assertJsonPath('data.sites.0.code', 'WARA');
        $response->assertJsonPath('data.sites.0.version', 'v1.0.0');
    }
}
