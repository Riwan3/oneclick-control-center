<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleObservatoryTest extends TestCase
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

    private function siteWithMenus(array $menus): Site
    {
        $site = Site::create(['code' => 'SKS', 'name' => 'PT SKS']);
        $site->forceFill(['menus' => $menus])->save();

        return $site;
    }

    public function test_baseline_module_keys_are_translated_to_the_keys_a_site_reports(): void
    {
        $this->siteWithMenus([
            'one-core' => ['version' => 'v2.5.0', 'menus' => [['id' => 'user', 'label' => 'User Oneclick']]],
            'one-procedure' => ['version' => 'v2.5.0', 'menus' => [['id' => 'prosedur', 'label' => 'Prosedur']]],
            'one-roster' => ['version' => 'v2.5.0', 'menus' => [['id' => 'roster', 'label' => 'Roster']]],
        ]);

        foreach (['one-core-value' => 'user', 'one-procedur' => 'prosedur', 'one-roster' => 'roster'] as $baselineKey => $menuId) {
            $response = $this->actingAs($this->admin())->getJson("/api/module-observatory/{$baselineKey}");

            $response->assertOk();
            $response->assertJsonCount(1, 'data.sites');
            $response->assertJsonPath('data.sites.0.code', 'SKS');
            $response->assertJsonPath('data.sites.0.menus.0.id', $menuId);
        }
    }

    public function test_a_site_that_does_not_report_the_module_is_left_out(): void
    {
        $this->siteWithMenus(['one-gmp' => ['version' => 'v2.5.0', 'menus' => []]]);

        $this->actingAs($this->admin())->getJson('/api/module-observatory/one-power')
            ->assertOk()
            ->assertJsonCount(0, 'data.sites');
    }
}
